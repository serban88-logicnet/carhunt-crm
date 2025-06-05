<?php
require_once "../../app/bootstrap.php";
$controller = new Controller;
$controller->generalModel = $controller->model('General');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_id'])) {
    $clientId = intval($_POST['client_id']);
    if ($clientId > 0) {
        $result = $controller->generalModel->executeQueryPdo(
            "SELECT istoric_client FROM clienti WHERE id = :id LIMIT 1",
            [':id' => $clientId]
        );
        if ($result && isset($result[0]->istoric_client)) {
            echo json_encode(['istoric_client' => $result[0]->istoric_client]);
            exit;
        }
    }
}
echo json_encode(['istoric_client' => null]);
