<?php
// Include your database connection and functions
require_once "../../app/bootstrap.php";

$controller = new Controller;
$controller->generalModel = $controller->model('General');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clasaPretId = $_POST['clasaPretId'];
    $startDate = $_POST['startDate']; // e.g., '2024-11-01'
    $endDate = $_POST['endDate'];     // e.g., '2024-11-03'
    $startTime = $_POST['startTime']; // e.g., '12:00'
    $endTime = $_POST['endTime'];     // e.g., '16:00'
    $totalDays = isset($_POST['totalDays']) ? (int)$_POST['totalDays'] : 0;
    $discount = isset($_POST['discount']) ? (float)$_POST['discount'] : 0;
    $costExtra = isset($_POST['costExtra']) ? (float)$_POST['costExtra'] : 0;
    
    if ($clasaPretId != "") {
        // Fetch prices from clasePret based on clasaPretId
        $priceQuery = "SELECT nume, pret_1, pret_2, pret_3, pret_4, pret_5 FROM clasePret WHERE id = :clasaPretId";
        $prices = $controller->generalModel->executeQueryPdo($priceQuery, [':clasaPretId' => $clasaPretId]);

        // Fetch fields from clasePret_fields (used to determine which price tier applies)
        $fieldsQuery = "SELECT name, display_name FROM clasePret_fields WHERE name IN ('pret_1','pret_2','pret_3','pret_4','pret_5')";
        $fields = $controller->generalModel->executeQueryPdo($fieldsQuery);

        // Fetch overlapping offers from oferteClasePret
        $offersQuery = "SELECT data_inceput, data_sfarsit, pret_1, pret_2, pret_3, pret_4, pret_5 
            FROM oferteClasePret 
            WHERE clasePret = :clasaPretId 
            AND ((data_inceput <= :endDate AND data_sfarsit >= :startDate))";
        $offers = $controller->generalModel->executeQueryPdo($offersQuery, [
            ':clasaPretId' => $clasaPretId,
            ':startDate' => $startDate,
            ':endDate' => $endDate
        ]);

        if ($prices && $fields) {
            $intervals = [];
            $currentStart = $startDate;

            // Break down the overall booking period into intervals based on overlapping offers.
            foreach ($offers as $offer) {
                if ($offer->data_inceput > $currentStart) {
                    // Create a non-offer interval from currentStart to one day before the offer starts.
                    $intervals[] = [
                        'start' => $currentStart,
                        'end' => date('Y-m-d', strtotime($offer->data_inceput . ' -1 day')),
                        'type' => 'non-offer',
                        'prices' => $prices[0]
                    ];
                }
                // Add the offer interval. Make sure it does not exceed the overall booking dates.
                $intervals[] = [
                    'start' => max($offer->data_inceput, $currentStart),
                    'end' => min($offer->data_sfarsit, $endDate),
                    'type' => 'offer',
                    'prices' => $offer
                ];
                // Update currentStart to one day after the offer's end.
                $currentStart = date('Y-m-d', strtotime($offer->data_sfarsit . ' +1 day'));
            }

            // If there is any remaining period after the offers, add it as a non-offer interval.
            if ($currentStart <= $endDate) {
                $intervals[] = [
                    'start' => $currentStart,
                    'end' => $endDate,
                    'timeStart' => $startTime,
                    'timeEnd' => $endTime,
                    'type' => 'non-offer',
                    'prices' => $prices[0]
                ];
            }

            // Calculate the total price over all intervals.
            $totalPrice = 0;
            foreach ($intervals as $index => $interval) {
                // Calculate the number of days for this interval.
                $days = (strtotime($interval['end']) - strtotime($interval['start'])) / (60 * 60 * 24) + 1;
                
                // For the final interval, adjust the day count based on the time difference.
                if ($index === count($intervals) - 1) {
                    $startHour = strtotime("$startTime");
                    $endHour = strtotime("$endTime");
                    $hoursDifference = ($endHour - $startHour) / (60 * 60);
                    if ($hoursDifference < 4) {
                        $days -= 1; // Remove one day if the extra hours are less than 4.
                    }
                    $intervals[$index]['hoursDifference'] = $hoursDifference;
                }
                $intervals[$index]['days'] = $days;
                
                // Determine the applicable price tier based on totalDays.
                foreach ($fields as $fieldIndex => $field) {
                    // The display_name is assumed to be in a format like "1-2" or "5+"
                    $minDays = (int) explode('-', $field->display_name)[0];
                    $maxDays = strpos($field->display_name, '+') !== false ? PHP_INT_MAX : (int) explode('-', $field->display_name)[1];
                    
                    // If the totalDays (from POST) falls within this tier, choose the corresponding price.
                    if ($totalDays >= $minDays && $totalDays <= $maxDays) {
                        $dailyPrice = $interval['prices']->{'pret_' . ($fieldIndex + 1)};
                        $totalPrice += $days * $dailyPrice;
                        break;
                    }
                }
                $intervals[$index]['dailyPrice'] = $dailyPrice;
            }

            // ------------------------------
            // Apply Discount & Advance Payment Logic
            // ------------------------------
            // 1. Standard discount first
            if ($discount > 0) {
                $totalPrice = $totalPrice * (1 - $discount / 100);
            }

            // 2. PlataAvans 5% discount (on the discounted price)
            $plataAvans = isset($_POST['plataAvans']) ? (int)$_POST['plataAvans'] : 0;
            if ($plataAvans == 29) {
                $totalPrice = $totalPrice * 0.95;
            }

            // 3. Add extra costs
            if($costExtra > 0) {
                $totalPrice = $totalPrice + $costExtra;
            }

            // 4. Round UP
            $totalPrice = ceil($totalPrice);

            
            $intervals["total"] = $totalPrice;
            
            echo json_encode([
                'calculatedPrice' => $totalPrice,
                'intervals' => $intervals,
                'clasaPretId' => $clasaPretId,
                'clasaPretName' => $prices[0]->nume,
            ]);
        } else {
            echo json_encode(['error' => 'Failed to fetch data']);
        }
    }
}
