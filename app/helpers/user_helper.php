<?php
	function isLoggedIn() {

		if(isset($_SESSION['user_id'])) {
			// die("TRUE");
			return true;
		} else {

			// die("FALSE");
			return false;

		}
	}

	function isSuperAdmin() {
		
		if(isset($_SESSION['user_id'])) {
			$controller = new Controller;
			$controller->generalModel = $controller->model('General');
			$user = $controller->generalModel->getItemByItem("utilizatori","id",$_SESSION['user_id']);
			$userType = $user->tip;
			
			if($userType == 1) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function hasRight($tip, $list) {
		if($list == NULL || $list == "all")
			return true;
		$list = explode(",", $list);
		if(in_array($tip, $list)) {
			return true;
		} else {
			
			return false;

		}
	}

	function writeType($type) {
		if($type == 3)
			return "Retailer";
		if ($type == 4)
			return "Furnizor";
	}

	function getUserName() {
		if(isLoggedIn()) {
			$controller = new Controller;
			$controller->generalModel = $controller->model('General');
			$user = $controller->generalModel->getItemById("utilizatori",$_SESSION['user_id']);
			return $user->nume;
		} else 
			return false;
	}

	function getUserType() {
		// dd("HEY");
		if(isLoggedIn()) {
			// dd("HEY");
			$controller = new Controller;
			$controller->generalModel = $controller->model('General');
			$user = $controller->generalModel->getItemById("utilizatori",$_SESSION['user_id']);
			return $user->tip;
		} else 
			return false;
	}