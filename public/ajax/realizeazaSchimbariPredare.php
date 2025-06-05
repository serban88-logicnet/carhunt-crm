<?php
require_once "../../app/bootstrap.php";

$controller = new Controller;
$controller->generalModel = $controller->model('General');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve POST data
    $numarMasinaNoua = isset($_POST['numarMasinaNoua']) ? trim($_POST['numarMasinaNoua']) : "";
    $dataSosireNoua  = isset($_POST['dataSosireNoua'])  ? trim($_POST['dataSosireNoua'])  : "";
    $oraSosireNoua   = isset($_POST['oraSosireNoua'])   ? trim($_POST['oraSosireNoua'])   : "";
    $inchiriereId   = isset($_POST['inchiriereId'])     ? trim($_POST['inchiriereId'])     : "";

    // Get current rental record
    $query = "SELECT * FROM inchirieri WHERE id = :inchiriereId";
    $params = [':inchiriereId' => $inchiriereId];
    $inchirieriRes = $controller->generalModel->executeQueryPdo($query, $params);
    if (empty($inchirieriRes)) {
        echo json_encode(['error' => ['Închirierea specificată nu a fost găsită.']]);
        exit;
    }
    $inchiriere = $inchirieriRes[0];

    // Determine new start date and time:
    // Use new values if provided, otherwise fall back on the current rental values.
    $dataSosire = ($dataSosireNoua === "") ? $inchiriere->data_inceput : $dataSosireNoua;
    $oraSosire  = ($oraSosireNoua  === "") ? $inchiriere->ora_inceput  : $oraSosireNoua;

    // Create DateTime objects for start and end (end is not changed)
    try {
        $requestedStart = new DateTime($dataSosire . ' ' . $oraSosire);
        $requestedEnd   = new DateTime($inchiriere->data_sfarsit . ' ' . $inchiriere->ora_sfarsit);
    } catch (Exception $e) {
        echo json_encode(['error' => ['Formatul datei sau orei nu este valid.']]);
        exit;
    }

    // Set the leeway (in hours) before start and after end
    $leewayHours = 4;
    $availabilityStart = clone $requestedStart;
    $availabilityStart->modify("-{$leewayHours} hours");
    $availabilityEnd = clone $requestedEnd;
    $availabilityEnd->modify("+{$leewayHours} hours");

    // Prepare an errors array and initialize the selected car variable.
    $errors = [];
    $selectedCar = null;

    // -----------------------------------------------
    // CASE 1: A new car number is provided -> search for that car.
    if ($numarMasinaNoua !== "") {
        $query = "SELECT id, km_actuali, nume FROM masini WHERE nume LIKE :numarMasinaNoua";
        $params = [':numarMasinaNoua' => $numarMasinaNoua];
        $masinaNouaRes = $controller->generalModel->executeQueryPdo($query, $params);

        if (isset($masinaNouaRes[0])) {
            // Car exists; candidate car found.
            $masinaNoua = $masinaNouaRes[0];

            // Check if this car is available in the extended period.
            $queryAvail = "SELECT id FROM inchirieri
                WHERE masina_id = :car_id 
                  AND id != :currentRentalId
                  AND (:availStart < STR_TO_DATE(CONCAT(data_sfarsit, ' ', ora_sfarsit), '%Y-%m-%d %H:%i:%s'))
                  AND (:availEnd > STR_TO_DATE(CONCAT(data_inceput, ' ', ora_inceput), '%Y-%m-%d %H:%i:%s'))";
            $paramsAvail = [
                ':car_id' => $masinaNoua->id,
                ':currentRentalId' => $inchiriereId,
                ':availStart' => $availabilityStart->format('Y-m-d H:i:s'),
                ':availEnd'   => $availabilityEnd->format('Y-m-d H:i:s')
            ];
            $conflict = $controller->generalModel->executeQueryPdo($queryAvail, $paramsAvail);
            if (!empty($conflict)) {
                $errors[] = "Mașina cu numărul '{$numarMasinaNoua}' nu este disponibilă în intervalul solicitat.";
            } else {
                $selectedCar = $masinaNoua;
            }
        } else {
            // -----------------------------------------------
            // CASE 2: Car not found -> search for a temporary car (masina_temp = 29)
            $queryTemp = "SELECT id, nume FROM masini WHERE masina_temp = 29";
            $tempCars = $controller->generalModel->executeQueryPdo($queryTemp, []);
            $foundTemp = false;
            foreach ($tempCars as $tempCar) {
                $queryAvail = "SELECT id FROM inchirieri
                    WHERE masina_id = :car_id 
                      AND id != :currentRentalId
                      AND (:availStart < STR_TO_DATE(CONCAT(data_sfarsit, ' ', ora_sfarsit), '%Y-%m-%d %H:%i:%s'))
                      AND (:availEnd > STR_TO_DATE(CONCAT(data_inceput, ' ', ora_inceput), '%Y-%m-%d %H:%i:%s'))";
                $paramsAvail = [
                    ':car_id' => $tempCar->id,
                    ':currentRentalId' => $inchiriereId,
                    ':availStart' => $availabilityStart->format('Y-m-d H:i:s'),
                    ':availEnd'   => $availabilityEnd->format('Y-m-d H:i:s')
                ];
                $conflict = $controller->generalModel->executeQueryPdo($queryAvail, $paramsAvail);
                if (empty($conflict)) {
                    $selectedCar = $tempCar;
                    $foundTemp = true;
                    break;
                }
            }
            if (!$foundTemp) {
                $errors[] = "Nu a fost găsită nicio mașină temporară disponibilă în intervalul solicitat.";
            }
        }
    }
    // -----------------------------------------------
    // CASE 3: No new car provided -> use the current rental's car.
    else {
        $currentCarId = $inchiriere->masina_id;
        $query = "SELECT id, nume FROM masini WHERE id = :car_id";
        $params = [':car_id' => $currentCarId];
        $currentCarRes = $controller->generalModel->executeQueryPdo($query, $params);
        if (isset($currentCarRes[0])) {
            $currentCar = $currentCarRes[0];
            $selectedCar = $currentCar;
            // Even if we keep the same car, ensure that the new start time does not cause a conflict.
            $queryAvail = "SELECT id FROM inchirieri
                WHERE masina_id = :car_id 
                  AND id != :currentRentalId
                  AND (:availStart < STR_TO_DATE(CONCAT(data_sfarsit, ' ', ora_sfarsit), '%Y-%m-%d %H:%i:%s'))
                  AND (:availEnd > STR_TO_DATE(CONCAT(data_inceput, ' ', ora_inceput), '%Y-%m-%d %H:%i:%s'))";
            $paramsAvail = [
                ':car_id' => $currentCar->id,
                ':currentRentalId' => $inchiriereId,
                ':availStart' => $availabilityStart->format('Y-m-d H:i:s'),
                ':availEnd'   => $availabilityEnd->format('Y-m-d H:i:s')
            ];
            $conflict = $controller->generalModel->executeQueryPdo($queryAvail, $paramsAvail);
            if (!empty($conflict)) {
                $errors[] = "Mașina curentă nu este disponibilă pentru noul interval de timp solicitat.";
            }
        } else {
            $errors[] = "Mașina curentă nu a fost găsită.";
        }
    }

    // If any errors have been detected, return them in a JSON response.
    if (!empty($errors)) {
        echo json_encode(['error' => $errors]);
        exit;
    }

    // -----------------------------------------------
    // Build the confirmation message based on what is being changed.
    // Check if the car is being changed:
    $carChanged = false;
    if ($numarMasinaNoua !== "" && $selectedCar->id != $inchiriere->masina_id) {
        $carChanged = true;
    }
    // Check if the date/time is changed by comparing requested start to the current start.
    $originalStart = new DateTime($inchiriere->data_inceput . ' ' . $inchiriere->ora_inceput);
    $dateTimeChanged = ($requestedStart != $originalStart);

    // If no changes are detected, return an error message.
    if (!$carChanged && !$dateTimeChanged) {
        echo json_encode(['error' => ['Nu s-au detectat modificări față de valorile existente.']]);
        exit;
    }

    // Build a tailored confirmation message.
    $confirmationMessage = "";
    if ($carChanged && $dateTimeChanged) {
        $confirmationMessage = "Se propune înlocuirea mașinii cu '{$selectedCar->nume}' și actualizarea datei/orei de start la " .
                               $requestedStart->format('Y-m-d H:i:s') . ". Confirmati?";
    } else if ($carChanged) {
        $confirmationMessage = "Se propune înlocuirea mașinii cu '{$selectedCar->nume}'. Confirmati?";
    } else if ($dateTimeChanged) {
        $confirmationMessage = "Se propune actualizarea datei/orei de start la " .
                               $requestedStart->format('Y-m-d H:i:s') . ". Confirmati?";
    }
    
    // -----------------------------------------------
    // If confirmation has not yet been provided, send the tailored message.
    if (!isset($_POST['confirm'])) {
        echo json_encode(['confirmation' => $confirmationMessage]);
        exit;
    }

    // -----------------------------------------------
    // Finally: the user has confirmed, so perform the update.
    // Build the update query – update data_inceput and ora_inceput; update masina_id if a new car is chosen.
    $updateQuery = "UPDATE inchirieri SET data_inceput = :data_inceput, ora_inceput = :ora_inceput";
    $updateParams = [
        ':data_inceput' => $requestedStart->format('Y-m-d'),
        ':ora_inceput'  => $requestedStart->format('H:i:s'),
        ':id'           => $inchiriereId
    ];
    // Update masina_id if the selected car is different from the current one.
    if ($selectedCar->id != $inchiriere->masina_id) {
        $updateQuery .= ", masina_id = :masina_id";
        $updateParams[':masina_id'] = $selectedCar->id;
    }
    $updateQuery .= " WHERE id = :id";

    // Execute the update query.
    $result = $controller->generalModel->executeQueryPdo($updateQuery, $updateParams);
    
    // Return a success message.
    echo json_encode(['success' => "Închirierea a fost actualizată cu succes."]);
    exit;
}
?>
