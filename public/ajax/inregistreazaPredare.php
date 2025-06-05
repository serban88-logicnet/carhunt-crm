<?php
require_once "../../app/bootstrap.php";

$controller = new Controller;
$controller->generalModel = $controller->model('General');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inchiriereId  = $_POST['inchiriereId'];
    $actionType    = $_POST['actionType'];

    // dd($_POST);
    
    $kilometraj    = $_POST['kilometraj'];
    $combustibil   = $_POST['combustibil'];
    $observatii    = $_POST['observatii'];
    $masinaId      = $_POST['masinaId'];
    $marcataPentru = $_POST['marcataPentru'];
    $extraInfo     = $_POST['extra-info'];

    // New payment fields.
    $incasatAmount   = isset($_POST['incasatAmount']) ? floatval($_POST['incasatAmount']) : 0;
    $incasatCurrency = "RON";

    // Determine new payment value in EURO.
    $newPayment = 0;
    if ($incasatAmount != 0) {
        if ($incasatCurrency === "RON") {
            $bnrUrl = 'https://www.bnr.ro/nbrfxrates.xml';
            $xmlString = false;
            // Try file_get_contents with a User-Agent header.
            $opts = [
                'http' => [
                    'method'  => 'GET',
                    'header'  => 'User-Agent: Mozilla/5.0 (compatible; MyApp/1.0)'
                ]
            ];
            $context = stream_context_create($opts);
            $xmlString = @file_get_contents($bnrUrl, false, $context);
            // Fallback to cURL if file_get_contents fails.
            if ($xmlString === false) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $bnrUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MyApp/1.0)');
                $xmlString = curl_exec($ch);
                curl_close($ch);
            }
            if ($xmlString !== false) {
                $xml = simplexml_load_string($xmlString);
                $eurRate = 0;
                foreach ($xml->Body->Cube->Rate as $rate) {
                    if ((string)$rate['currency'] === "EUR") {
                        $eurRate = floatval($rate);
                        break;
                    }
                }
                if ($eurRate > 0) {
                    $newPayment = floor($incasatAmount / $eurRate);
                } else {
                    $newPayment = floor($incasatAmount);
                }
            } else {
                $newPayment = floor($incasatAmount);
            }
        } else {
            $newPayment = floor($incasatAmount);
        }
    }

    // File upload handling.
    $files = $_FILES['fileUpload'];
    $uploadedFiles = [];
    foreach ($files['tmp_name'] as $index => $tmpName) {
        $fileName = basename($files['name'][$index]);
        $uploadDir = '/home/logicnet/domains/carhunt.logicnet.ro/public_html/public/uploads/';
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($tmpName, $targetPath)) {
            $uploadedFiles[] = $fileName;
        }
    }
    $imagini = implode(',', $uploadedFiles);

    // Build booking name.
    

    if ($actionType == 'predare') {
        $query = "UPDATE inchirieri SET 
                    status = 21, 
                    kilometri_predare = :kilometraj, 
                    combustibil_predare = :combustibil, 
                    observatii_predare = :observatii, 
                    imagini_predare = :imagini, 
                    platit = IFNULL(platit, 0) + :newPayment 
                  WHERE id = :inchiriereId";
        $params = [
            ':kilometraj'   => $kilometraj,
            ':combustibil'  => $combustibil,
            ':observatii'   => $observatii,
            ':imagini'      => $imagini,
            ':newPayment'   => $newPayment,
            ':inchiriereId' => $inchiriereId,
        ];
    } else if ($actionType == 'retur') {
        $query = "UPDATE inchirieri SET 
                    status = 22, 
                    kilometri_retur = :kilometraj, 
                    combustibil_retur = :combustibil, 
                    observatii_retur = :observatii, 
                    imagini_retur = :imagini,
                    platit = IFNULL(platit, 0) + :newPayment 
                  WHERE id = :inchiriereId";
        $params = [
            ':kilometraj'   => $kilometraj,
            ':combustibil'  => $combustibil,
            ':observatii'   => $observatii,
            ':imagini'      => $imagini,
            ':newPayment'   => $newPayment,
            ':inchiriereId' => $inchiriereId,
        ];
    }

    if ($controller->generalModel->executeQueryPdo($query, $params)) {
        $updateCarQuery = "UPDATE masini SET 
                             km_actuali = :kilometraj, 
                             marcata_pentru = :marcataPentru, 
                             observatii_marcaj = :extraInfo 
                           WHERE id = :masinaId";
        $carParams = [
            ':kilometraj'   => $kilometraj,
            ':masinaId'      => $masinaId,
            ':marcataPentru' => $marcataPentru,
            ':extraInfo'     => $extraInfo,
        ];
        if ($controller->generalModel->executeQueryPdo($updateCarQuery, $carParams)) {
            echo json_encode(['status' => 'success', 'message' => 'Datele au fost salvate cu succes']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Eroare la actualizarea datelor pentru mașină']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Eroare la actualizarea rezervării']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metoda de solicitare invalidă']);
}
?>
