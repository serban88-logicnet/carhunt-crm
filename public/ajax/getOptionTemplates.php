<?php
// Include your database connection and functions
require_once "../../app/bootstrap.php";

$controller = new Controller;
$controller->generalModel = $controller->model('General');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	$idTemplate = $_POST['idTemplate'];

    if($idTemplate != "" && $idTemplate != "NaN") {
      // Fetch prices from clasePret based on clasaPretId
        $query = "SELECT * FROM templatesClasePret WHERE id = :idTemplate";
        $template = $controller->generalModel->executeQueryPdo($query, [':idTemplate' => $idTemplate]);
        $clasePret = $template[0]->clasePret;
        $clasePret = explode(",", $clasePret);
        echo json_encode(['clasePret' => $clasePret]);
    }
}


?>