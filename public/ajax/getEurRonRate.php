<?php
// Example: Fetch BCR daily XML for EUR->RON
header('Content-Type: application/json');
$xmlUrl = 'https://www.bnr.ro/nbrfxrates.xml'; // If BCR has a direct XML! Else, use BNR: https://www.bnr.ro/nbrfxrates.xml
$xml = @simplexml_load_file('https://www.bnr.ro/nbrfxrates.xml');
$rate = null;
if ($xml) {
    foreach ($xml->Body->Cube->Rate as $item) {
        if ((string)$item['currency'] === 'EUR') {
            $rate = (float)$item;
            break;
        }
    }
}
echo json_encode(['rate' => $rate]);
