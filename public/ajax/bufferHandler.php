<?php
require_once "../../app/bootstrap.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mode'], $_POST['inchiriere_id'])) {
    $mode = $_POST['mode'];
    $id = (int)$_POST['inchiriere_id'];
    $controller = new Controller;
    $controller->generalModel = $controller->model('General');

    if ($mode === 'toBuffer') {
        // Move to Buffer (status 43)
        $controller->generalModel->executeQueryPdo(
            "UPDATE inchirieri SET status = 43 WHERE id = :id",
            [':id' => $id]
        );
        echo json_encode(['success' => true, 'action' => 'toBuffer']);
        exit;
    }
    if ($mode === 'fromBuffer' && isset($_POST['destinatie'])) {
        $dest = (int)$_POST['destinatie'];
        if (!in_array($dest, [20, 21])) {
            echo json_encode(['error' => true, 'msg' => 'Destinație invalidă']);
            exit;
        }
        $controller->generalModel->executeQueryPdo(
            "UPDATE inchirieri SET status = :dest WHERE id = :id",
            [':id' => $id, ':dest' => $dest]
        );
        echo json_encode(['success' => true, 'action' => 'fromBuffer']);
        exit;
    }
    echo json_encode(['error' => true, 'msg' => 'Operațiune necunoscută']);
    exit;
}
echo json_encode(['error' => true, 'msg' => 'Cerere invalidă']);
