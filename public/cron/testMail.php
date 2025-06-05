<?php 
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);


require_once dirname(__DIR__,2)."/app/bootstrap.php";

$controller = new Controller;
$controller->generalModel = $controller->model('General');

//EXPIRARE CONTRACTE
$contracteNoticeDays = array(7,30,60,120);
$body = "";


foreach($contracteNoticeDays as $day) {
	$assoc = array();

	$date = date('y-m-d',strtotime("+".$day." days"));
	// echo $date."<br/>";

	$contracte = $controller->generalModel->getItemsByItem("contracte","data_terminare",$date);
	// print_r($contracte);

	if(!empty($contracte)) {
		$differentUsers = array();

		//we get all the unique users with contracts in said period
		foreach($contracte as $contract) {
			if(!in_array($contract->assigned_to, $differentUsers)) {
				array_push($differentUsers, $contract->assigned_to);
			}
		}

		// echo "<pre>"; print_r($differentUsers);	
		//we create an array that has each user id and all their contracts as a sub array
		foreach($differentUsers as $user) {
			if(!isset($assoc[$user])) {
				$assoc[$user] = array();	
			}
		}

		foreach($differentUsers as $user) {
			foreach($contracte as $contract) {
				if($contract->assigned_to == $user) {
					array_push($assoc[$user],$contract);
				}
			}
		}	
		
		// echo "<pre>"; print_r($assoc);
		foreach($assoc as $userId => $contracte) {
			$theUser = $controller->generalModel->getItemById("utilizatori", $userId);
			// dd($theUser);
			$userBody = "";
			// $userBody .= "<h4>SEND TO: ".$theUser->nume.": ".$theUser->email." </h4>";
			$userBody .= "<p>Urmatoarele Contracte sunt la ".$day." de zile de data de expirare:</p>";
			$userBody .= "<ul>";
			foreach($contracte as $contract) {
				$userBody .= "<li>";
				$userBody .= $contract->nume." - <a target='_blank' href='".URLROOT."/index/detalii/contracte/".$contract->id."'>Vezi in CRM</a>";
				$userBody .= "</li>";
			}
			$userBody .= "</ul>";	
			sendMail($theUser->email,"Contracte in curs de Expirare - CRM Synttergy",$userBody,"form@synttergy.ro");	
			// echo $userBody;
		}

		

		$body .= "<p>Urmatoarele Contracte sunt la ".$day." de zile de data de expirare:</p>";
		$body .= "<ul>";
		foreach($contracte as $contract) {
			$body .= "<li>";
			$body .= $contract->nume." - <a target='_blank' href='".URLROOT."/index/detalii/contracte/".$contract->id."'>Vezi in CRM</a>";
			$body .= "</li>";
		}
		$body .= "</ul>";		
	}
}

// echo $body;

if($body != "") {
	sendMail("mariana@synttergy.ro","Contracte in curs de Expirare - CRM Synttergy",$body,"form@synttergy.ro");
	sendMail("adrian.simion@synttergy.ro","Contracte in curs de Expirare - CRM Synttergy",$body,"form@synttergy.ro");
	sendMail("cristina.gindes@synttergy.ro", "Contracte in curs de Expirare - CRM Synttergy",$body,"form@synttergy.ro");
	// sendMail("serban@logicnet.ro","Contracte in curs de Expirare - CRM Synttergy",$body,"form@synttergy.ro");
}




// NOTIFICARE ZILE DE NASTERE
$birthdayNoticeDays = array(1,7);
$body = "";
foreach($birthdayNoticeDays as $day) {
	$date = date('y-m-d',strtotime("+".$day." days"));
	$contacte = $controller->generalModel->getItemsByItem("contacte","data_nastere",$date);
	if(!empty($contacte)) {
		$body .= "<p>Urmatoarele persoane de contact au ziua de nastere la ".$day." zile distanta:";
		$body .= "<ul>";
		foreach($contacte as $contact) {
			$body .= "<li>";
			$body .= $contact->nume." - <a target='_blank' href='".URLROOT."/index/detalii/contacte/".$contact->id."'>Vezi in CRM</a>";
			$body .= "</li>";
		}
		$body .= "</ul>";
	}
	// dd($contacte);
}

if($body != "") {
	sendMail("mariana@synttergy.ro","Zile de nastere - CRM Synttergy",$body,"form@synttergy.ro");
	sendMail("adrian.simion@synttergy.ro","Zile de nastere - CRM Synttergy",$body,"form@synttergy.ro");
	sendMail("cristina.gindes@synttergy.ro", "Zile de nastere - CRM Synttergy",$body,"form@synttergy.ro");
	// sendMail("serban@logicnet.ro","Zile de nastere - CRM Synttergy",$body,"form@synttergy.ro");
}


//CONTRACTE NESEMNATE
$contracte = $controller->generalModel->getItemsByItem("contracte","status_contract","379");
$body = "";
foreach($contracte as $contract) {
	if($contract->deleted == 0) {
		$dataIncepere = date_create($contract->data_incepere);
		$now = new DateTime();
		$interval = $now->diff($dataIncepere);
		$daysSinceAdded = $interval->days;
		// print_r($daysSinceAdded."<br/>");
		if ($daysSinceAdded % 7 == 0) {
			$weeksSinceAdded = floor($daysSinceAdded / 7);
			$body .= "<p>Contractul  ".$contract->nume." a ramas cu statusul 'Expediat' de ".$weeksSinceAdded." saptamani. Verificati daca este cazul de actualizarea statusului. <a target='_blank' href='".URLROOT."/index/detalii/contracte/".$contract->id."'>Vezi Contract in CRM<a/></p>";
		}	
	}
}

if($body != "") {
    sendMail("mariana@synttergy.ro","Contracte in status Expediat - CRM Synttergy",$body,"form@synttergy.ro");
	sendMail("adrian.simion@synttergy.ro","Contracte in status Expediat - CRM Synttergy",$body,"form@synttergy.ro");
	// sendMail("serban@logicnet.ro","Contracte in status Expediat - CRM Synttergy",$body,"form@synttergy.ro");
	//dd($body);
}
// dd($contracte);


$leaduri = $controller->generalModel->getItems("leaduri");
$usersArray = array();

foreach ($leaduri as $key => $lead) {
    $created_at_timestamp = strtotime($lead->created_at);
    $current_timestamp = time();
    $time_difference = $current_timestamp - $created_at_timestamp;
    
    if ($time_difference <= 24 * 60 * 60) {
        $theUser = $controller->generalModel->getItemById("utilizatori", $lead->assigned_to);
        $userId = $theUser->id;

        if(!isset($usersArray[$userId])) {
        	$usersArray[$userId] = array();
        	$usersArray[$userId]['email'] = $theUser->email;
        	$usersArray[$userId]['body'] = "<p>Aveti unul sau mai multe lead-uri atribuite:</p>";
        }
        
      $usersArray[$userId]['body'] .= $lead->nume . " - <a target='_blank' href='" . URLROOT . "/index/detalii/leaduri/" . $lead->id . "'>Vezi in CRM</a> - " . $lead->created_at . " - " . $theUser->id . "<br/>";

    }
}

foreach ($usersArray as $user) {
	sendMail($user['email'], "Lead-uri noi atribuite - CRM Synttergy", $user['body'],"form@synttergy.ro");
}