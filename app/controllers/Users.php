<?php
class Users extends Controller{
	public function __construct(){
		$this->userModel = $this->model('User');
		$this->fieldModel = $this->model('Field');
		$this->generalModel = $this->model('General');
		$this->jsFiles = array('users');
		$this->commonLinks = array("single" => "/users/show/");
	}

	public function index(){
		$users = $this->userModel->getAllUsers();
		// dd($users);
		$data['users'] = $users;
		$data['title'] = "Conturile existente in aplicatie";
		$this->view("users/index", $data);
	}

	public function list($type = "") {
		if($type == "furnizori") {
			$typeId = 4;
		} elseif($type == "retaileri") {
			$typeId = 3;
		}
		$users = $this->userModel->getAllUsers($typeId);
		// dd($users);

		$data['users'] = $users;
		$data['title'] = "Conturile de <strong>".$type."</strong> existente in aplicatie";
		$this->view("users/index", $data);
	}

	public function show($id, $sortWhat = "", $sortBy = "", $order = "") { //type 3 = retailer, 4 = furnizor
		$user = $this->userModel->findUserById($id);
		$connections = $this->userModel->getConnections($id, $user->type);
		$retaileri = $this->userModel->getUsersByType(3);
		
		foreach($retaileri as $key=>$retailer) {
			if($this->userModel->checkIfConnectionExists($retailer->id, $id)) {
				unset($retaileri[$key]);
			}
		}

 		
		foreach($connections as $connection) {
			if($user->type == 3) {
				$temp = $this->userModel->findUserById($connection->furnizor_id);
			}
			if($user->type == 4) {
				$temp = $this->userModel->findUserById($connection->retailer_id);
			}
			$connection->originalId = $temp->id;
			$connection->name = $temp->name;
			$connection->email = $temp->email;
			$connection->cui = $temp->cui;
			$connection->limita_retailer = $temp->limita_retailer;
			$connection->comision_furnizor = $temp->comision_furnizor;
		}
		$user->connections = $connections;


		$sumaFacturi = 0;
		if($user->type == 3) {
			if($sortWhat != "facturi"){
				$facturi = $this->generalModel->getItemByItem("facturi", "retailer_id", $id);	
			} else {
				$facturi = $this->generalModel->getItemsByItemWithSort("facturi","retailer_id",$id, $sortBy, $order);	
			}
		} elseif($user->type == 4) {
			if($sortWhat != "facturi"){
				$facturi = $this->generalModel->getItemByItem("facturi", "furnizor_id", $id);		
			} else {
				$facturi = $this->generalModel->getItemsByItemWithSort("facturi","furnizor_id",$id, $sortBy, $order);	
			}
			
		}
		foreach($facturi as $factura) {
			$sumaFacturi += $factura->valoare;
			$factura->furnizorInfo = $this->userModel->findUserById($factura->furnizor_id);
			$factura->retailerInfo = $this->userModel->findUserById($factura->retailer_id);
		}
		$user->sumaFacturi = new StdClass();
		$user->sumaFacturi = $sumaFacturi;

		$user->facturi = $facturi;
		
		$data['facturiFields'] = $this->fieldModel->getFields("facturi");

		$this->commonLinks['sort'] = "/users/show/".$id."/facturi/";
		$data['commonLinks'] = $this->commonLinks;


		$data['user'] = $user;
		$data['sortData'] = array("sortBy" => $sortBy, "order" => $order);
		$data['retaileri'] = $retaileri;
		$data['js'] = $this->jsFiles;
		$this->view("users/view", $data);
	}

	public function creare($type){
		$data = [];
		$count_errors = 0;
		if($type == "retailer") {
			$fields = $this->fieldModel->getFields("retaileri");
			$typeLink = "retaileri";
		}
		if($type == "furnizor") {
			$fields = $this->fieldModel->getFields("furnizori");
			$typeLink = "furnizori";
		}

		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$data = addFieldsInData($fields, $data);
			// dd($data);
			if($data['values']['password'] != $data['values']['password_confirm']) {
				$data['values']['errors']['password_confirm_error'] = "Parolele nu se potrivesc";
			}
			$data['values']['password'] = password_hash($data['values']['password'], PASSWORD_DEFAULT);
			//check if we have errors
			foreach ($data['values']['errors'] as $error) {
				if (!empty($error)) {
					$count_errors++;
				} 
			}

			if ($count_errors) {
				flash('user_notices','Aveti '.$count_errors.' erori in formular. Va rugam sa le rezolvati.', 'alert alert-danger');
			} else {
				if($this->userModel->addUser($fields, $data['values'], $type)) {
					flash('user_notices', 'Utilizator Adaugat');
					redirect("users/list/".$typeLink);
				} else {
					flash('user_notices','Ceva nu a mers bine!', 'alert alert-danger');
				}
			}
		} else {
			$data['values'] = array();
		}

		$data['fields'] = $fields;
		$data['type'] = $type;
		$this->view('users/create', $data);
	}

	public function editare($id) {
		$count_errors = 0;
		$user = $this->userModel->findUserById($id);
		$data['values'] = json_decode(json_encode($user), True);
		
		if($user->type == 3) {
			$fields = $this->fieldModel->getFields("retaileri");
			$data['type'] = "retailer";
		}
		if($user->type == 4) {
			$fields = $this->fieldModel->getFields("furnizori");
			$data['type'] = "furnizor";
		}

		foreach ($fields as $key=>$field) {
			if($field->type == "password" || $field->name == "type") {
				unset($fields[$key]);
			}
		}

		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$data = addFieldsInData($fields, $data);
			foreach ($data['values']['errors'] as $error) {
				if (!empty($error)) {
					$count_errors++;
				} 
			}
			if ($count_errors) {
				flash('user_notices','Aveti '.$count_errors.' erori in formular. Va rugam sa le rezolvati.', 'alert alert-danger');
			} else {
				if($this->generalModel->editGeneral("users",$id,$fields,$data['values'])) {
					flash('user_notices', 'Inregistrare modificata!');
					redirect("users/show/".$id);
				} else {
					flash('user_notices','Something went wrong!', 'alert alert-danger');
				}
			}
		} 
		
		$data['fields'] = $fields; 
		
		$this->view('users/edit', $data);

	}

	public function connect($furnizor_id) {
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$this->userModel->createConnection($_POST['retailer'],$furnizor_id);
			redirect("users/show/".$furnizor_id);
		} else {
			redirect("users/show/".$furnizor_id);
		}
	}

	public function unlink($retailer_id, $furnizor_id, $source = "") {
		$this->userModel->deleteConnection($retailer_id, $furnizor_id);
		if($source == "retailer") {
			redirect("users/show/".$retailer_id);
		} elseif($source == "furnizor") {
			redirect("users/show/".$furnizor_id);	
		}
	}

	public function login(){
      // Check if logged in
		if($this->isLoggedIn()){
			redirect('');
		}

      // Check if POST
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
        // Sanitize POST
			$_POST  = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

			$data = [       
				'email' => trim($_POST['email']),
				'password' => trim($_POST['password']),        
				'email_err' => '',
				'password_err' => '',       
			];

        // Check for email
			if(empty($data['email'])){
				$data['email_err'] = 'Please enter email.';
			}

        // Check for name
			if(empty($data['name'])){
				$data['name_err'] = 'Please enter name.';
			}

        // Check for user
			
			if($this->generalModel->getItemByItem("utilizatori","email",$data['email'])){
          // User Found
			} else {
          // No User
				$data['email_err'] = 'This email is not registered.';
			}

        // Make sure errors are empty
			if(empty($data['email_err']) && empty($data['password_err'])){

          // Check and set logged in user
				$loggedInUser = $this->userModel->login($data['email'], $data['password']);

				if($loggedInUser){
            // User Authenticated!
					$this->createUserSession($loggedInUser);

				} else {
					$data['password_err'] = 'Password incorrect.';
            // Load View
					$this->view('users/login', $data);
				}

			} else {
          // Load View
				$this->view('users/login', $data);
			}

		} else {
        // If NOT a POST

        // Init data
			$data = [
				'email' => '',
				'password' => '',
				'email_err' => '',
				'password_err' => '',
			];

        // Load View
			$this->view('users/login', $data);
		}
	}

    // Create Session With User Info
	public function createUserSession($user){
		$_SESSION['user_id'] = $user->id;
		$_SESSION['user_email'] = $user->email; 
		$_SESSION['user_name'] = $user->name;
		redirect('');
	}

    // Logout & Destroy Session
	public function logout(){
		unset($_SESSION['user_id']);
		unset($_SESSION['user_email']);
		unset($_SESSION['user_name']);
		session_destroy();
		redirect('users/login');
	}

    // Check Logged In
	public function isLoggedIn(){
		if(isset($_SESSION['user_id'])){
			return true;
		} else {
			return false;
		}
	}
}