<?php 

$bootstrapPath = __DIR__ . '/../../bootstrap.php';
require_once($bootstrapPath);

// dd($data);

$what = $data['what'];

// Calculate the total price for each clasePret
$durata_zile = $data['oferta'][0]->durata_zile;
$discount = $data['oferta'][0]->discount;
$plataAvans = $data['oferta'][0]->plata_avans ?? null;
$hasPlataAvans = ($plataAvans == 29);
$costExtra = $data['oferta'][0]->cost_extra ?? 0;
$explicatieCostExtra = $data['oferta'][0]->explicatie_cost_extra;

// Apply extra 10% if plata_avans is 29
if ($hasPlataAvans) {
    $discount += 5;
}

if($what == "trimisa") {
    $clasaPretIds = explode(',', $data['oferta'][0]->oferta_trimisa);    
} elseif($what == "acceptata") {
    $clasaPretIds = explode(',', $data['oferta'][0]->oferta_acceptata);    
}


$intervalIndex = null;

// Find the appropriate price field based on durata_zile
foreach ($data['fieldsCp'] as $index => $field) {
    $range = explode('-', $field->display_name);
    $minDays = (int)$range[0];
    $maxDays = isset($range[1]) ? (int)$range[1] : PHP_INT_MAX;
    if ($durata_zile >= $minDays && $durata_zile <= $maxDays) {
        $intervalIndex = $index;
        break;
    }
}

$mpdf = new \Mpdf\Mpdf();

ob_start(); // Start output buffering

// Generate cards for each clasePret
foreach ($data['clasePret'] as $clase) {

    if (in_array($clase->id, $clasaPretIds) && $intervalIndex !== null) {
        $priceField = $data['fieldsCp'][$intervalIndex]->name;
        $dailyRate = $clase->$priceField;
        $totalPrice = $dailyRate * $durata_zile;

        // Apply discount
        $finalPrice = round($totalPrice - ($totalPrice * ($discount / 100)));

        $totalPrice = $totalPrice + $costExtra;
        $finalPrice = $finalPrice + $costExtra;


        //price HTML
        if($discount != 0) {
            $priceHtml = "<h3 style='color: red; text-decoration: line-through;'>{$totalPrice} €</h3><h2 style='color: darkgreen;'>{$finalPrice} €</h2>";    
        } else {
            $priceHtml = "<h2 style='color: darkgreen;'>{$finalPrice} €</h2>";
        }
        

        // Retrieve the image from the oferta
        $imagePath = '/uploads/' . $clase->imagine;

        // Prepare variables from oferta
        $transmisie = $clase->transmisie_name;
        $numar_pasageri = $clase->numar_pasageri;
        $combustibil = $clase->combustibil_name;
        $garantie = $clase->garantie;

        // Garantie checkmark text
        $garantieText = ($garantie == 0 || $garantie === null) ? "fara garantie" : "Garantie: {$garantie} €";

        // Card content, formatted similar to the provided image
         echo "
        <table border='1' cellpadding='10' cellspacing='0' style='width: 100%; margin-bottom: 20px;'>
            <tr>
                <td rowspan='2' style='width: 200px;'>
                    <img src='{$imagePath}' alt='car' style='width: 200px; height: auto;'>
                </td>
                <td colspan='2'>
                    <h2 style='font-size: 24px; margin-bottom: 30px;'>{$clase->nume}</h2>
                    <p style='color: red; font-size: 18px; font-weight: bold;'>Full Casco inclus in pret!</p>" . ($hasPlataAvans ? "<p style='color: darkblue; font-size: 16px; font-weight: bold;'>Plata Avans</p>" : "") . "

                </td>
            </tr>
            <tr>
                <td>
                    <strong>Cutie:</strong> {$transmisie}<br>
                    <strong>Numar pasageri:</strong> {$numar_pasageri}<br>
                    <strong>Combustibil:</strong> {$combustibil}
                </td>
                <td style='text-align: center;'>
                    <strong>{$durata_zile} zile x {$dailyRate} €</strong><br>
                    " . (
                        $costExtra != 0 
                        ? "<p style='margin: 5px 0; color: #333;'><strong>Supliemntar:</strong> {$costExtra} € – {$explicatieCostExtra}</p>" 
                        : ""
                    ) . "

                    <table style='background-color: #f0f0f0; padding: 10px; width: 100%; margin-top: 10px;'>
                        <tr>
                            <td>
                                <h3><strong>Total de achitat:</strong></h3>{$priceHtml}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan='3' style='background-color: #f0f0f0;'>
                    <table style='width: 100%; background-color: #f0f0f0; padding: 10px;'>
                        <tr>
                            <td style='width: 50%; color: red;'><strong>Garantie:</strong> {$garantieText}</td>
                            <td style='width: 25%;'>
                                <ul style='list-style: none; padding-left: 0;'>
                                    <li>{$garantieText}</li>
                                    <li>Livrare: Aeroport</li>
                                </ul>
                            </td>
                            <td style='width: 25%;'>
                                <ul style='list-style: none; padding-left: 0;'>
                                    <li>Plata la livrare</li>
                                    <li>Km nelimitati</li>
                                </ul>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>";

    }
}

$html = ob_get_clean();
// echo $html;

$mpdf->WriteHTML($html);
// Output the PDF
$clientName = $data['oferta'][0]->client_nume . '_' . $data['oferta'][0]->client_prenume;
$filename = strtolower(preg_replace('/\s+/', '_', $clientName)) . '_oferta_' . $data['oferta'][0]->id . '.pdf';

$mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);

