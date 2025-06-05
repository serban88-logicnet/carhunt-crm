<?php
require_once "../../app/bootstrap.php";

// Ensure the global overlap constant is defined.
if (!defined('OVERLAPTIME')) {
    define('OVERLAPTIME', 4);
}

$controller = new Controller;
$controller->generalModel = $controller->model('General');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read parameters from POST.
    $priceClasses = $_POST['priceClasses'] ?? "";
    $startDate    = $_POST['startDate'] ?? "";
    $endDate      = $_POST['endDate'] ?? "";
    $startTime    = $_POST['startTime'] ?? "";
    $endTime      = $_POST['endTime'] ?? "";

    if (empty($priceClasses) || empty($startDate) || empty($endDate) || empty($startTime) || empty($endTime)) {
        echo json_encode(['status' => 'error', 'message' => 'Parametri insuficienti']);
        exit;
    }
    
    // Convert comma-separated price classes into an array.
    $priceClassesArr = array_map('trim', explode(',', $priceClasses));
    // Re-index the array (we need it to be 1-indexed later).
    $priceClassesArr = array_values($priceClassesArr);
    
    // Combine date and time to get datetime strings.
    $newStart = $startDate . ' ' . $startTime;
    $newEnd   = $endDate . ' ' . $endTime;
    
    // Compute the leeway window.
    $leewayStart = date("Y-m-d H:i:s", strtotime("$newStart -" . OVERLAPTIME . " hours"));
    $leewayEnd   = date("Y-m-d H:i:s", strtotime("$newEnd +" . OVERLAPTIME . " hours"));

    // Build the query using positional placeholders.
    $placeholders = implode(',', array_fill(0, count($priceClassesArr), '?'));
    $query = "SELECT m.*
              FROM masini m
              WHERE m.clasaPret IN ($placeholders)
                AND m.id NOT IN (
                    SELECT DISTINCT masina_id
                    FROM inchirieri
                    WHERE CONCAT(data_inceput, ' ', ora_inceput) < ?
                      AND CONCAT(data_sfarsit, ' ', ora_sfarsit) > ?
                )
              LIMIT 1";
              
    // Create a new parameter array that is 1-indexed.
    $params = [];
    $index = 1;
    foreach ($priceClassesArr as $value) {
        $params[$index] = $value;
        $index++;
    }
    $params[$index++] = $leewayEnd;
    $params[$index++] = $leewayStart;

    // Execute the query.
    $carResult = $controller->generalModel->executeQueryPdo($query, $params);
    
    if ($carResult && count($carResult) > 0) {
        $car = $carResult[0];
        $response = [
            'status' => 'success',
            'car' => [
                'id' => $car->id,
                'nume' => $car->nume
                // Add additional fields if needed.
            ]
        ];
        echo json_encode($response);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No available car found in this category.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
