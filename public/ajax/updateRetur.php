<?php
require_once "../../app/bootstrap.php";

$controller = new Controller;
$controller->generalModel = $controller->model('General');
// dd($_POST);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Read POST variables (for retur updates only, no car change)
    $inchiriereId    = $_POST['inchiriereId'];
    $newDataSfarsit  = $_POST['dataSfarsitNoua']; // new return date (YYYY-MM-DD)
    $newOraSfarsit   = $_POST['oraSfarsitNoua'];  // new return time (HH:MM:SS or HH:MM)

    // Retrieve the current (original) booking record.
    $query = "SELECT * FROM inchirieri WHERE id = :inchiriereId";
    $params = [':inchiriereId' => $inchiriereId];
    $results = $controller->generalModel->executeQueryPdo($query, $params);
    if (empty($results)) {
        echo json_encode(['error' => ['Închirierea cu ID-ul '.$inchiriereId.' nu a fost găsită.']]);
        exit;
    }
    $inchiriere = $results[0];

    // Get the original scheduled return date and time
    $originalDataSfarsit = $inchiriere->data_sfarsit;
    $originalOraSfarsit  = $inchiriere->ora_sfarsit;

    // Build DateTime objects for the original and new return times.
    try {
        $originalReturn = new DateTime($originalDataSfarsit . ' ' . $originalOraSfarsit);
        $newReturn      = new DateTime($newDataSfarsit . ' ' . $newOraSfarsit);
    } catch (Exception $e) {
        echo json_encode(['error' => ['Formatul datei sau orei este invalid.']]);
        exit;
    }

    // If no change in return time, report error.
    if ($newReturn == $originalReturn) {
        echo json_encode(['error' => ['Nu s-au detectat modificări.']]);
        exit;
    }

    // Calculate the difference in hours between the new return and the original return.
    $diffHours = ($newReturn->getTimestamp() - $originalReturn->getTimestamp()) / 3600;

    // ****************************************
    //  NEW— OVERLAP DETECTION & OVERWRITE
    // ****************************************
    // Any other booking on this car whose period intersects [originalReturn, newReturn]
    // a) Query any other bookings on this car whose date‐range overlaps [originalReturn ↔ newReturn]
    $carId = $inchiriere->masina_id;
    $origDate = $originalReturn->format('Y-m-d');
    $newDate  = $newReturn->format('Y-m-d');

    $ovQuery = "
        SELECT * FROM inchirieri 
        WHERE masina_id = :m AND id <> :id
          AND data_inceput <= :newEnd 
          AND data_sfarsit  >= :origEnd
          AND status <> 32
    ";
    $ovParams = [
        ':m'=>$carId,
        ':id'=>$inchiriereId,
        ':newEnd'=>$newDate,
        ':origEnd'=>$origDate
    ];
    $overlaps = $controller->generalModel->executeQueryPdo($ovQuery,$ovParams);

    // dd($overlaps);

    // b) Compute max overlap in hours
    $maxOverlap = 0;
    $newStartTs = strtotime($inchiriere->data_sfarsit.' '.$inchiriere->ora_sfarsit); // original return start
    $newEndTs   = $newReturn->getTimestamp();

    foreach ($overlaps as $o) {
        $existStart = strtotime($o->data_inceput . ' ' . $o->ora_inceput);
        $existEnd   = strtotime($o->data_sfarsit . ' ' . $o->ora_sfarsit);
        $ovStart    = max($newStartTs, $existStart);
        $ovEnd      = min($newEndTs,   $existEnd);
        $ovSec      = max(0, $ovEnd - $ovStart);
        $ovHrs      = $ovSec / 3600;
        if ($ovHrs > $maxOverlap) {
            $maxOverlap = $ovHrs;
        }
    }

    // dd($maxOverlap);

    // c) Immediately mark heavy overlaps as overwritten; light overlaps remain active
    if ($maxOverlap > OVERLAPTIME) {
        foreach ($overlaps as $o) {
            // Overwrite booking
            $controller->generalModel->executeQueryPdo(
                "UPDATE inchirieri SET status = 32 WHERE id = :bid",
                [':bid'=>$o->id]
            );
            // Update its source offer if present
            if (!empty($o->oferta_id)) {
                $controller->generalModel->executeQueryPdo(
                    "UPDATE oferte SET status_oferta = 16 WHERE id = :ofid",
                    [':ofid'=>$o->oferta_id]
                );
            }
        }
    }
    // (If abs($maxOverlap - OVERLAPTIME) < 0.01, we do nothing—both remain active)

    // **********************************************
    // CASE 1: New return time is either earlier OR within 4 hours later.
    // **********************************************
    if ($diffHours <= OVERLAPTIME) {
        // Build a confirmation message (applies both if the new time is earlier or just slightly later).
        $confirmationMessage = "Se va actualiza data și ora de retur la " . $newReturn->format('Y-m-d H:i') . ". Confirmati?";
        if (!isset($_POST['confirm'])) {
            echo json_encode(['confirmation' => $confirmationMessage]);
            exit;
        }
        // Update the current booking with the new return date/time.
        $updateQuery = "UPDATE inchirieri 
                        SET data_sfarsit = :newDataSfarsit, ora_sfarsit = :newOraSfarsit 
                        WHERE id = :inchiriereId";
        $updateParams = [
            ':newDataSfarsit' => $newReturn->format('Y-m-d'),
            ':newOraSfarsit'  => $newReturn->format('H:i:s'),
            ':inchiriereId'   => $inchiriereId,
        ];
        if ($controller->generalModel->executeQueryPdo($updateQuery, $updateParams)) {
            echo json_encode(['success' => "Închirierea a fost actualizată cu succes."]);
        } else {
            echo json_encode(['error' => ['Eroare la actualizarea închirierii.']]);
        }
        exit;
    }
    // **********************************************
    // CASE 2: New return time is more than OVERLAPTIME later.
    // **********************************************
    else {
        // For the new booking, we need to calculate pricing.
        try {
            $originalStart = new DateTime($inchiriere->data_inceput . ' ' . $inchiriere->ora_inceput);
        } catch(Exception $e) {
            echo json_encode(['error' => ['Formatul datei/orii de start este invalid.']]);
            exit;
        }
        $originalDiffSeconds = $originalReturn->getTimestamp() - $originalStart->getTimestamp();
        $fullDays = floor($originalDiffSeconds / (24 * 3600));
        $remainingSeconds = $originalDiffSeconds - ($fullDays * 24 * 3600);
        $remainingHours = $remainingSeconds / 3600;
        $originalDays = $fullDays;
        if ($remainingHours > 4) {
            $originalDays += 1;
        }
        if ($originalDays < 1) { 
            $originalDays = 1; // at least one day.
        }
        $originalPrice = $inchiriere->pret; // total price of original booking
        $pricePerDay = $originalPrice / $originalDays;

        // 2. Calculate the duration of the extra period (from the original scheduled return to the new announced return)
        $extraSeconds = $newReturn->getTimestamp() - $originalReturn->getTimestamp();
        $extraFullDays = floor($extraSeconds / (24 * 3600));
        $extraRemainingSeconds = $extraSeconds - ($extraFullDays * 24 * 3600);
        $extraRemainingHours = $extraRemainingSeconds / 3600;
        $extraDays = $extraFullDays;
        if ($extraRemainingHours > 4) {
            $extraDays += 1;
        }
        if ($extraDays < 1) { 
            $extraDays = 1;
        }
        $newBookingPrice = $extraDays * $pricePerDay;

        $confirmationMessage = "Se va crea o nouă închiriere pentru perioada suplimentară (de la " . $originalReturn->format('Y-m-d H:i') . " până la " . $newReturn->format('Y-m-d H:i') . "). Clientul va fi taxat. Confirmati?";
        if (!isset($_POST['confirm'])) {
            echo json_encode(['confirmation' => $confirmationMessage]);
            exit;
        }

        // Create the new booking as extension
        $insertQuery = "INSERT INTO inchirieri (nume, masina_id, client_id, data_inceput, ora_inceput, data_sfarsit, ora_sfarsit, pret, status, kilometri_predare, combustibil_predare, imagini_predare, inchiriere_extinsa_de_la_id, oferta_id) 
                        VALUES (:nume, :masina_id, :client_id, :data_inceput, :ora_inceput, :data_sfarsit, :ora_sfarsit, :pret, :status, :kilometri_predare, :combustibil_predare, :imagini_predare, :inchiriere_extinsa_de_la_id, :oferta_id)";
        
        // Build new name for extension
        if (stripos($inchiriere->nume, "Extindere") === 0) {
            if (preg_match('/^Extindere(?:-(\d+))?$/i', $inchiriere->nume, $matches)) {
                $counter = isset($matches[1]) ? intval($matches[1]) + 1 : 2;
                $newNume = "Extindere-" . $counter;
            } else {
                $newNume = "Extindere-2" . $inchiriere->nume;
            }
        } else {
            $newNume = "Extindere-" . $inchiriere->nume;
        }

        $newMasinaId = $inchiriere->masina_id;
        $newClientId = $inchiriere->client_id;
        $kilometri_predare = $inchiriere->kilometri_predare;
        $combustibil_predare = $inchiriere->combustibil_predare;
        $imagini_predare = $inchiriere->imagini_predare;
        $inchiriere_extinsa_de_la_id = $inchiriere->id;
        $oferta_id = $inchiriere->oferta_id;
        $newDataInceput = $originalReturn->format('Y-m-d');
        $newOraInceput  = $originalReturn->format('H:i:s');
        $newDataSfarsit = $newReturn->format('Y-m-d');
        $newOraSfarsit  = $newReturn->format('H:i:s');
        $newStatus = $inchiriere->status;
        $insertParams = [
            ':nume'        => $newNume,
            ':masina_id'   => $newMasinaId,
            ':client_id'   => $newClientId,
            ':data_inceput'=> $newDataInceput,
            ':ora_inceput' => $newOraInceput,
            ':data_sfarsit'=> $newDataSfarsit,
            ':ora_sfarsit' => $newOraSfarsit,
            ':pret'        => $newBookingPrice,
            ':status'      => $newStatus,
            ':kilometri_predare' => $kilometri_predare,
            ':combustibil_predare' => $combustibil_predare,
            ':imagini_predare' => $imagini_predare,
            ':inchiriere_extinsa_de_la_id' => $inchiriere_extinsa_de_la_id,
            ':oferta_id' => $oferta_id
        ];
        $newBookingId = $controller->generalModel->executeQueryPdo($insertQuery, $insertParams);
        if (!$newBookingId) {
            echo json_encode(['error' => ['Eroare la crearea noii închirieri.']]);
            exit;
        }

        // Update the original booking: mark it as "Extinsa" (status 31)
        $updateOrigQuery = "UPDATE inchirieri SET status = 31, inchiriere_extinsa_id = :newBookingId WHERE id = :inchiriereId";
        $updateOrigParams = [
            ':newBookingId' => $newBookingId,
            ':inchiriereId' => $inchiriereId,
        ];
        $controller->generalModel->executeQueryPdo($updateOrigQuery, $updateOrigParams);

        // ---- RECALCULATE & UPDATE THE ENTIRE CHAIN'S PRICE ----
        // Step 1: Find the original booking (head of the chain)
        $rootBooking = $inchiriere;
        while (!empty($rootBooking->inchiriere_extinsa_de_la_id)) {
            $parentQ = "SELECT * FROM inchirieri WHERE id = :id";
            $parentR = $controller->generalModel->executeQueryPdo($parentQ, [':id' => $rootBooking->inchiriere_extinsa_de_la_id]);
            if (!empty($parentR)) {
                $rootBooking = $parentR[0];
            } else {
                break;
            }
        }
        // Step 2: Recursively collect all bookings in the chain (original + all extensions)
        $chainBookings = getChainBookings($rootBooking->id);

        // Step 3: Find the earliest start and latest end in the chain
        $chainStart = null;
        $chainEnd = null;
        foreach ($chainBookings as $b) {
            $bStart = strtotime($b->data_inceput . ' ' . $b->ora_inceput);
            $bEnd   = strtotime($b->data_sfarsit . ' ' . $b->ora_sfarsit);
            if ($chainStart === null || $bStart < $chainStart) $chainStart = $bStart;
            if ($chainEnd   === null || $bEnd   > $chainEnd)   $chainEnd   = $bEnd;
        }

        // Step 4: Calculate chain duration in days (round up if extra > 4 hours)
        $totalSeconds = $chainEnd - $chainStart;
        $fullDays = floor($totalSeconds / (24 * 3600));
        $remainingSeconds = $totalSeconds - ($fullDays * 24 * 3600);
        $remainingHours = $remainingSeconds / 3600;
        $totalDays = $fullDays;
        if ($remainingHours > 4) $totalDays += 1;
        if ($totalDays < 1) $totalDays = 1;

        // Step 5: Calculate price per day based on original booking
        $origSeconds = strtotime($rootBooking->data_sfarsit . ' ' . $rootBooking->ora_sfarsit)
                     - strtotime($rootBooking->data_inceput . ' ' . $rootBooking->ora_inceput);
        $origFullDays = floor($origSeconds / (24 * 3600));
        $origRemSec = $origSeconds - ($origFullDays * 24 * 3600);
        $origRemHrs = $origRemSec / 3600;
        $origDays = $origFullDays + ($origRemHrs > 4 ? 1 : 0);
        if ($origDays < 1) $origDays = 1;
        $pricePerDay = $rootBooking->pret / $origDays;
        $newTotalPrice = round($totalDays * $pricePerDay);

        // Step 6: Update every booking in the chain with the new total price
        foreach ($chainBookings as $b) {
            $controller->generalModel->executeQueryPdo(
                "UPDATE inchirieri SET pret = :pret WHERE id = :id",
                [':pret' => $newTotalPrice, ':id' => $b->id]
            );
        }
        // ---- END CHAIN PRICE UPDATE ----

        echo json_encode(['success' => "Noua închiriere a fost creată și închirierea originală a fost marcată ca extinsă."]);
        exit;
    }
} else {
    echo json_encode(['error' => ['Metoda de solicitare este invalidă.']]);
}