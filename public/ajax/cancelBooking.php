<?php
require_once "../../app/bootstrap.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $bookingId = (int)$_POST['booking_id'];
    if ($bookingId > 0) {
        $controller = new Controller;
        $controller->generalModel = $controller->model('General');
        $query = "UPDATE inchirieri SET status = 41 WHERE id = :id";
        $params = [':id' => $bookingId];
        $result = $controller->generalModel->executeQueryPdo($query, $params);
        if ($result !== false) {
            echo json_encode(['status' => 'success']);
            exit;
        }
    }
}
echo json_encode(['status' => 'error']);
