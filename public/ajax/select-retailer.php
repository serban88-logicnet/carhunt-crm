<?php 
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

require_once "../../app/bootstrap.php";

$controller = new Controller;
$controller->generalModel = $controller->model('General');

$echipamente = $controller->generalModel->getItemsByItem("echipamente","organizatie_id",$_POST[''])