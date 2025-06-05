<?php 
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

require_once "../../app/bootstrap.php";

$controller = new Controller;
$controller->generalModel = $controller->model('General');

$contacte = $controller->generalModel->getItemsByItem("contacte","organizatie_id",$_POST['org']);
echo json_encode($contacte);