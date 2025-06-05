<?php 
// File: pages/calendar.php
// This view displays a full-year calendar with a bookings overview on the left and a list of available offers on the right.
// The calendar is built for the year provided in $data['year']. Navigation buttons allow switching between years.
require APPROOT . '/views/inc/header.php'; 
?>
<div class="container my-3">
    <!-- Year Navigation Buttons -->
    <?php 
    $currentYear = date("Y");
    $displayYear = $data['year'];
    ?>
    <div class="d-flex justify-content-between align-items-left mb-3">
        <div>
            <a class="btn btn-secondary" href="<?= URLROOT; ?>/pages/calendar/<?= $displayYear - 1; ?>"><< <?= $displayYear - 1; ?></a>
            <a class="btn btn-secondary" href="<?= URLROOT; ?>/pages/calendar/<?= $currentYear; ?>">Current (<?= $currentYear; ?>)</a>
            <a class="btn btn-secondary" href="<?= URLROOT; ?>/pages/calendar/<?= $displayYear + 1; ?>"><?= $displayYear + 1; ?> >></a>
        </div>
        <h3 class="mb-0">Calendar for <?= $displayYear; ?></h3>
    </div>
</div>
<div class="row">
    <?php flash('notices'); ?>
    <?php
    // Retrieve the structured bookings and offers passed from the controller.
    $bookingsByCar   = $data['bookings']; 
    $availableOffers = $data['available_offers'];
    ?>
    <!-- Left Column: Calendar Table -->
    <div class="col-md-10">
        <div class="card card-body bg-white shadow mt-0 calendar-container">
            <table class="table table-bordered table-striped table-responsive my-bookings-table">
                <thead>
                    <tr>
                        <!-- The first header cell contains a search input -->
                        <th style="min-width: 150px;">
                            <input class="form-control" type="text" id="carSearch" placeholder="Search">
                        </th>
                        <?php 
                        // Use January 1 of the displayed year as the start date.
                        $startDate = new DateTime("$displayYear-01-01");
                        for ($i = 0; $i < 365; $i++):
                            $currentDate  = clone $startDate;
                            $currentDate->modify("+{$i} days");
                            $cellDateAttr = $currentDate->format("Y-m-d");
                            $dateFormatted = $currentDate->format("d/m");
                        ?>
                            <th data-cell-date="<?= $cellDateAttr; ?>"><?= $dateFormatted; ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookingsByCar as $carName => $carData): ?>
                    <tr data-clasa-pret="<?= $carData['clasaPret']; ?>"
                        data-license="<?= $carData['license']; ?>"
                        data-id-masina="<?= $carData['masina_id']; ?>">
                        <!-- Car name column -->
                        <td class="js-calendar-car-column"><?= htmlspecialchars($carName); ?></td>
                        
                        <?php 
                        // Build a map day-of-year → booking info
                        $daysBookings = [];
                        if (!empty($carData['bookings'])) {
                            foreach ($carData['bookings'] as $clientName => $clientData) {
                                foreach ($clientData['bookings'] as $startTs => $b) {
                                    $d = (new DateTime)->setTimestamp($startTs);
                                    $z = (int)$d->format('z');
                                    $daysBookings[$z] = [
                                        'perioada'      => $b['perioada'] ?? $b['perioada'],
                                        'inchiriere_id' => $b['inchiriere_id'],
                                        'client_id'     => $b['client_id'],
                                        'masina_id'     => $carData['masina_id'],
                                        'client'        => $clientName,
                                        'rating_client' => $clientData['rating_client'],
                                        'data_inceput'  => $b['data_inceput'],
                                        'data_sfarsit'  => $b['data_sfarsit'],
                                        'ora_inceput'  => $b['ora_inceput'],
                                        'ora_sfarsit'  => $b['ora_sfarsit'],
                                    ];
                                }
                            }
                        }
                        // Adjust for end-to-start overlaps
                        $adjusted = [];
                        $keys = array_keys($daysBookings);
                        rsort($keys);
                        foreach ($keys as $k1) {
                            $b1 = $daysBookings[$k1];
                            $ov = false;
                            foreach ($daysBookings as $k2=>$b2) {
                                if ($k1==$k2) continue;
                                if ($b1['data_inceput'] === $b2['data_sfarsit']) {
                                    $b1['perioada']--; $b1['overlap']=$b2['inchiriere_id'];
                                    $adjusted[$k1+1] = $b1; $ov=true; break;
                                }
                            }
                            if (!$ov) { $b1['overlap']=false; $adjusted[$k1] = $b1; }
                        }
                        ksort($adjusted);
                        $daysBookings = $adjusted;

                        // Render each day-cell
                        for ($day=0; $day<365; $day++):
                            $cellClass   = "text-center empty-cell";
                            $aClass      = "text-primary";
                            $btnClass    = "btn-outline-light";
                            $colspan     = 1;
                            $cellContent = "";
                            $curDate     = clone $startDate;
                            $curDate->modify("+{$day} days");
                            $cellDateAttr= $curDate->format("Y-m-d");
                            $dataInceput = $cellDateAttr;
                            $href        = "#";
                            
                            if (isset($daysBookings[$day])):
                                $bk = $daysBookings[$day];
                                switch($bk['rating_client']){
                                    case 11: $cellClass="bg-warning"; $aClass="text-dark"; $btnClass = "btn-outline-dark"; break;
                                    case 12: $cellClass="bg-info";    $aClass="text-white"; $btnClass = "btn-outline-light"; break;
                                    case 13: $cellClass="bg-primary"; $aClass="text-white"; $btnClass = "btn-outline-light"; break;
                                    case 14: $cellClass="bg-success"; $aClass="text-white"; $btnClass = "btn-outline-light"; break;
                                    case 15: $cellClass="bg-danger";  $aClass="text-white"; $btnClass = "btn-outline-light"; break;
                                    default: $cellClass="bg-info";    $aClass="text-white"; $btnClass = "btn-outline-light"; break;
                                }
                                $colspan     = $bk['perioada'];
                                $cellContent = $bk['client'];
                                if ($bk['overlap']) {
                                    $cellContent = '<i class="bi bi-arrow-left-right"></i> '.$cellContent;
                                }
                                $inchiriere_id = $bk['inchiriere_id'];
                                $href          = URLROOT."/index/detalii/inchirieri/".$inchiriere_id;
                        ?>
                            <td data-cell-date="<?= $dataInceput; ?>"
                                class="<?= $cellClass; ?>"
                                colspan="<?= $colspan; ?>">
                                <a href="<?= $href; ?>"
                                   class="js-booking-details-link <?= $aClass; ?>"
                                   data-booking-id="<?= $inchiriere_id; ?>">
                                    <?= $cellContent; ?>
                                </a>
                                <!-- only in booking cells: move button -->
                                <button class="btn btn-sm <?= $btnClass; ?> js-muta-rezervare"
                                        data-booking-id="<?= $inchiriere_id;?>"
                                        data-start-date="<?= $bk['data_inceput'];?>"
                                        data-end-date="<?= $bk['data_sfarsit'];?>"
                                        data-start-time="<?= $bk['ora_inceput'];?>"
                                        data-end-time="<?= $bk['ora_sfarsit'];?>">
                                    Muta
                                </button>
                            </td>
                        <?php 
                                $day += $colspan - 1;
                            else:
                                // empty cell: preserve current-date highlight
                                $todayZ = (int)date('z');
                                if ($day === $todayZ) {
                                    $cellClass .= " bg-warning my-current-date";
                                } else {
                                    $cellClass .= " bg-white";
                                }
                        ?>
                            <td data-cell-date="<?= $dataInceput; ?>"
                                class="<?= $cellClass; ?>"
                                colspan="<?= $colspan; ?>">
                                <a class="<?= $aClass; ?>" href="#"><?= $cellContent; ?></a>
                            </td>
                        <?php endif;
                        endfor;
                        ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Column: Available Offers -->
    <div class="col-md-2">
        <div class="offers-container">
            <?php foreach ($availableOffers as $offer): ?>
                <?php 
                if ($offer->status_oferta == 16 && $offer->client_id != 0) {
                    $borderColor = 'border-success';
                } elseif ($offer->status_oferta == 18 || $offer->client_id == 0) {
                    $borderColor = 'border-warning';
                }
                ?>
                <div class="card card-body bg-white shadow mt-0 mb-3 js-calendar-offer-card border border-3 <?= $borderColor; ?>" 
                     data-clase-pret="<?= $offer->oferta_acceptata; ?>" 
                     data-oferta-id="<?= $offer->id; ?>"
                     data-status-oferta="<?= $offer->status_oferta; ?>">
                    <h6 class="js-calendar-card-client" data-client-id="<?= $offer->client_id; ?>">
                        <span class="js-calendar-card-nume-client"><?= $offer->nume . " " . $offer->prenume; ?></span>
                    </h6>
                    <p class="mb-0">
                        <span class="js-calendar-card-start"><?= $offer->data_inceput; ?></span> (<span class="js-calendar-ora-start"><?= $offer->ora_inceput; ?></span>) - <span class="js-calendar-card-end"><?= $offer->data_sfarsit; ?></span> (<span class="js-calendar-ora-end"><?= $offer->ora_sfarsit; ?></span>)
                    </p>
                    <div class="extra-info">
                        <p class="mb-0"><strong>Clasa Pret:</strong> <?= $offer->clasePretNames; ?></p>
                        <?php if ($offer->client_id == 0): ?>
                            <a class="btn btn-primary btn-sm mt-1" 
                               href="<?= URLROOT; ?>/index/creare/clienti?nume=<?= $offer->client_nume; ?>&prenume=<?= $offer->client_prenume; ?>&telefon=<?= $offer->client_telefon; ?>&email=<?= $offer->client_email; ?>&comingFromOffer=<?= $offer->id; ?>&comingFromCalendar=1">
                               Creeaza Client
                            </a>
                        <?php else: ?>
                            <?php if ($offer->status_oferta == 18): ?>
                                <a class="btn btn-primary btn-sm mt-1" 
                                   href="<?= URLROOT; ?>/index/editare/oferte/<?= $offer->id; ?>?action=acceptaOferta&comingFromCalendar=1">
                                   Accepta Oferta
                                </a>
                            <?php elseif ($offer->status_oferta == 16): ?>
                                <p class="mb-0"><strong>Pret Final:</strong> <span class="js-calendar-card-pret"><?= $offer->pret_final; ?></span> RON</p>
                                <button class="btn btn-sm btn-outline-light js-arata-masini-libere mb-2" style="display:none;">Arată Doar Disponibile</button>
                                <button class="btn btn-sm btn-outline-light js-cauta-masina mb-2" style="display:none;">Alocare Automată</button>
                                <button class="btn btn-sm btn-outline-light js-permite-orice-masina" style="display:none;">Permite Orice Mașină</button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/pages/modals/bookingDetailsModal.php'; ?>
<?php require APPROOT . '/views/inc/footer.php'; ?>
