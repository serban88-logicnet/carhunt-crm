<?php
$bootstrapPath = __DIR__ . '/../../bootstrap.php';
require_once($bootstrapPath);

$contract = $data['contract'][0];

$mpdf = new \Mpdf\Mpdf();
ob_start(); // Start output buffering


// Set up the content for the PDF
echo '<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12pt; }
        h1 { text-align: center; font-size: 16pt; }
        .contract-details { margin: 20px 0; }
        .signature { margin-top: 50px; }
    </style>
</head>
<body>
    <div class="contract-header">
        <img src="/img/Logo.png" alt="Carhunt Logo" style="width: 150px;">
        <h1>Contract de Închiriere Auto</h1>
        <p>Nr. contract: ' . rand(1000, 9999) . '</p>
    </div>

    <div class="contract-details">
        <p>Între:</p>
        <p><strong>Carhunt SRL</strong>, cu sediul în București, România, reprezentată legal prin Administrator, denumit în continuare <strong>Locatar</strong>, și</p>
        <p><strong>' . $contract->nume_client . ' ' . $contract->prenume_client . '</strong>, cu următoarele date:</p>
        <ul>
            <li><strong>CNP:</strong> ' . ($contract->cnp ?: '__________') . '</li>
            <li><strong>Telefon:</strong> ' . $contract->telefon . '</li>
            <li><strong>Email:</strong> ' . ($contract->email ?: '__________') . '</li>
            <li><strong>Adresă:</strong> ' . ($contract->adresa ?: '__________') . '</li>
            <li><strong>Nr. permis de conducere:</strong> ' . ($contract->nr_permis ?: '__________') . '</li>
        </ul>
        <p>denumit în continuare <strong>Chiriaș</strong>, s-a încheiat prezentul contract de închiriere auto.</p>
    </div>

    <div class="contract-body">
        <h2>1. Obiectul contractului</h2>
        <p>Locatarul se obligă să pună la dispoziția Chiriașului un autovehicul în vederea utilizării temporare, în condițiile prezentului contract.</p>

        <h2>2. Perioada de închiriere</h2>
        <p>Perioada de închiriere este de la data <strong>' . $contract->data_inceput . '</strong>, ora <strong>' . $contract->ora_inceput . '</strong> până la data <strong>' . $contract->data_sfarsit . '</strong>, ora <strong>' . $contract->ora_sfarsit . '</strong>.</p>

        <h2>3. Prețul și condițiile de plată</h2>
        <p>Prețul total pentru perioada de închiriere este de <strong>' . $contract->pret_final . ' RON</strong>. Plata se va face conform termenilor agreați la momentul semnării contractului.</p>

        <h2>4. Obligațiile părților</h2>
        <p><strong>Locatarul</strong> se obligă să livreze vehiculul în stare corespunzătoare de funcționare.</p>
        <p><strong>Chiriașul</strong> se obligă să returneze vehiculul în aceeași stare în care l-a primit, la data și ora menționate în prezentul contract.</p>

        <h2>5. Datele personale</h2>
        <p>Chiriașul consimte la prelucrarea datelor sale personale în scopul executării prezentului contract.</p>
    </div>

    <div class="signature">
        <p><strong>Locatar</strong></p>
        <p>Carhunt SRL</p>
        <br><br><br>
        <p><strong>Chiriaș</strong></p>
        <p>' . $contract->nume_client . ' ' . $contract->prenume_client . '</p>
    </div>
</body>
</html>
';

$html = ob_get_clean();
//echo $html;

$mpdf->WriteHTML($html);
// Output the PDF
$filename = strtolower(preg_replace('/\s+/', '_', $contract->nume_client . '_' . $contract->prenume_client)) . '_contract_' . $contract->id . '.pdf';
$mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);


?>