<?php
// File: createInchiriere.php
// This AJAX script creates a new booking (inchiriere) from an offer. It first validates 
// the car's documents and mileage, then checks for overlapping bookings.
// Depending on the amount of overlap (measured in hours), it returns a confirmation warning:
// - For a "light" 4‑hour overlap (exactly OVERLAPTIME hours), it warns the user but if confirmed,
//   both bookings remain active.
// - For a "heavy" overlap (more than OVERLAPTIME hours), if confirmed the existing overlapping
//   bookings are marked as overwritten (status = 32) and their corresponding offer is updated to status 16.
// The new booking is then inserted and the associated offer updated.

require_once "../../app/bootstrap.php";

$controller = new Controller;
$controller->generalModel = $controller->model('General');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve POST variables
    $masina_id    = $_POST['masina_id'];
    $client_id    = $_POST['client_id'];
    $data_inceput = $_POST['data_inceput'];
    $data_sfarsit = $_POST['data_sfarsit'];
    $ora_inceput  = $_POST['ora_inceput'];
    $ora_sfarsit  = $_POST['ora_sfarsit'];
    $pret         = $_POST['pret'];
    $license      = $_POST['license'];
    $numeClient   = $_POST['numeClient'];
    $ofertaId     = $_POST['ofertaId'];
    $skipCheck    = $_POST['skipCheck'];    // For minor overlap checks (start/end edge cases)
    $skipCarCheck = $_POST['skipCarCheck'];   // For car document/warning override
    // New parameter for overlapping confirmation:
    $skipOverlap  = isset($_POST['skipOverlap']) ? $_POST['skipOverlap'] : 'false';

    $today = date('Y-m-d');

    // Fetch offer details for the given offer_id
    $query = "SELECT *
              FROM oferte
              WHERE id LIKE :oferta_id";
    $params = [':oferta_id' => $ofertaId];
    $oferta = $controller->generalModel->executeQueryPdo($query, $params);

    // Fetch car details (including marking) for the given masina_id.
    $query = "SELECT m.*, ol.nume as marcata
              FROM masini m
              LEFT JOIN other_lists ol ON ol.id = m.marcata_pentru
              WHERE m.id LIKE :masina_id";
    $params = [':masina_id' => $masina_id];
    $masina = $controller->generalModel->executeQueryPdo($query, $params);

    // Initialize the warning message.
    $message = '';

    if ($skipCarCheck == 'false') {
        // Helper function to compare a given date to today and the booking's end date.
        function checkDateStatus($date, $today, $data_sfarsit, $label) {
            if ($date < $today) {
                return "$label este deja expirată.\n";
            } elseif ($date > $today && $date < $data_sfarsit) {
                return "$label expiră în timpul rezervării.\n";
            }
            return '';
        }
        
        $message .= checkDateStatus($masina[0]->asigurare, $today, $data_sfarsit, 'Asigurarea');
        $message .= checkDateStatus($masina[0]->vigneta, $today, $data_sfarsit, 'Vigneta');
        $message .= checkDateStatus($masina[0]->itp, $today, $data_sfarsit, 'ITP-ul');
        $message .= checkDateStatus($masina[0]->data_ultima_revizie, $today, $data_sfarsit, 'Ultima revizie');

        if (($masina[0]->km_actuali - $masina[0]->revizie_facuta) > REVIZIEOVER) {
            $message .= "Mașina a depășit cu peste " . REVIZIEOVER . " km de la ultima revizie.\n";
        }


        if (!empty($masina[0]->marcata)) {
            $message .= "Mașina este marcată pentru {$masina[0]->marcata}. {$masina[0]->observatii_marcaj}\n";
        }

        if (!empty($message)) {
            echo json_encode(['status' => 'error', 'errorCode' => '03', 'message' => $message]);
            return;
        }    
    }

    $numeInchiere = $numeClient."-".$license."-".$data_inceput.":".$data_sfarsit;

    // ---------------------------------------------------------------
    // OVERLAP CHECKING (INCLUDING TIME)
    // ---------------------------------------------------------------
    // Query to find any overlapping bookings (by date).
    $query = "SELECT * FROM inchirieri
              WHERE (data_inceput <= :data_sfarsit)
              AND (data_sfarsit >= :data_inceput)
              AND (masina_id = :masina_id)
              AND status <> 32";
    $params = [
        ':masina_id'    => $masina_id,
        ':data_inceput' => $data_inceput,
        ':data_sfarsit' => $data_sfarsit,
    ];
    $results = $controller->generalModel->executeQueryPdo($query, $params);

    // Compute maximum overlap (in hours) between new booking and any existing booking.
    $maxOverlap = 0;
    // Combine new booking's date and time into timestamps.
    $newStart = strtotime($data_inceput . ' ' . $ora_inceput);
    $newEnd   = strtotime($data_sfarsit . ' ' . $ora_sfarsit);

    if (!empty($results)) {
        foreach ($results as $result) {
            // Combine the existing booking's date and time.
            $existStart = strtotime($result->data_inceput . ' ' . $result->ora_inceput);
            $existEnd   = strtotime($result->data_sfarsit . ' ' . $result->ora_sfarsit);
            // The overlap period is the intersection of the new booking and the existing booking.
            $overlapStart = max($newStart, $existStart);
            $overlapEnd   = min($newEnd, $existEnd);
            $overlapSeconds = max(0, $overlapEnd - $overlapStart);
            $overlapHours = $overlapSeconds / 3600;
            if ($overlapHours > $maxOverlap) {
                $maxOverlap = $overlapHours;
            }
        }
    }

    // Decide what to do based on the maximum overlap hours.
    // Use the global constant OVERLAPTIME defined in your config (e.g., define('OVERLAPTIME', 4);).
    if (!empty($results) && ($skipCheck === 'false') && ($skipOverlap === 'false')) {
        if ($maxOverlap > OVERLAPTIME) {
            // Heavy overlap case: more than 4 hours overlap.
            $message = "Există suprapuneri semnificative (".round($maxOverlap,1)." ore) în rezervare. Dorești să continui și să suprascrii rezervările existente?";
            echo json_encode(['status' => 'error', 'errorCode' => '04', 'message' => $message]);
            return;
        } elseif (abs($maxOverlap - OVERLAPTIME) < 0.01) {
            // Light overlap: exactly 4 hours.
            $message = "Există o suprapunere de ".OVERLAPTIME." ore în rezervare. Dorești să continui (ambele rezervări vor rămâne active)?";
            echo json_encode(['status' => 'error', 'errorCode' => '05', 'message' => $message]);
            return;
        }
    }

    // If overlapping bookings exist and the user confirmed (skipOverlap == true)
    // then handle heavy overlaps by marking overlapped bookings as overwritten.
    if (!empty($results) && $skipOverlap === 'true') {
        if ($maxOverlap > OVERLAPTIME) {
            foreach ($results as $overlapBooking) {
                // Mark overlapped booking as overwritten (status = 32)
                $updateOverlapQuery = "UPDATE inchirieri SET status = 32 WHERE id = :bookingId";
                $controller->generalModel->executeQueryPdo($updateOverlapQuery, [':bookingId' => $overlapBooking->id]);
                // Update its related offer (if any) to status 16.
                if (isset($overlapBooking->oferta_id) && !empty($overlapBooking->oferta_id)) {
                    $updateOfferQuery = "UPDATE oferte SET status_oferta = 16 WHERE id = :ofertaId";
                    $controller->generalModel->executeQueryPdo($updateOfferQuery, [':ofertaId' => $overlapBooking->oferta_id]);
                }
            }
        }
        // For a light overlap (exactly 4 hours), we do nothing to existing bookings.
    }

    // ---------------------------------------------------------------
    // NEW BOOKING INSERTION
    // ---------------------------------------------------------------
    // Insert the new booking record.
    $query = "INSERT INTO inchirieri (nume, masina_id, client_id, data_inceput, data_sfarsit, ora_inceput, ora_sfarsit, pret, status, oferta_id) 
              VALUES (:numeInchiere, :masina_id, :client_id, :data_inceput, :data_sfarsit, :ora_inceput, :ora_sfarsit, :pret, '20', :oferta_id)";
    $params = [
        ':masina_id'    => $masina_id,
        ':client_id'    => $client_id,
        ':data_inceput' => $data_inceput,
        ':data_sfarsit' => $data_sfarsit,
        ':ora_inceput'  => $ora_inceput,
        ':ora_sfarsit'  => $ora_sfarsit,
        ':pret'         => $pret,
        ':numeInchiere' => $numeInchiere,
        ':oferta_id'    => $ofertaId
    ];

    if ($inchiriereId = $controller->generalModel->executeQueryPdo($query, $params)) {
        // Update the corresponding offer to mark it as converted (status_oferta = 19)
        // and associate it with the new booking.
        $updateQuery = "UPDATE oferte 
                        SET status_oferta = 19, inchiriere_id = :inchiriere_id 
                        WHERE id = :oferta_id";
        $updateParams = [
            ':inchiriere_id' => $inchiriereId,
            ':oferta_id'     => $ofertaId,
        ];
        if ($controller->generalModel->executeQueryPdo($updateQuery, $updateParams)) {
            echo json_encode(['status' => 'success', 'message' => 'Record created and oferta updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update oferta']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create record']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
