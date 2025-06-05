<?php
class Organizatii extends Controller{


	public function __construct(){
		$this->userModel = $this->model('User');
		$this->fieldModel = $this->model('Field');
		$this->facturaModel = $this->model('Factura');
		$this->generalModel = $this->model('General');
		$this->commonLinks = array("single" => "/facturi/show/single/", "sort" => "/facturi/sort/" );
	}

	public function creare() {
		$data = [];
		$count_errors = 0;
		$fields = $this->fieldModel->getFields('organizatii');

	

		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$data = addFieldsInData($fields, $data);
			
			foreach ($data['values']['errors'] as $error) {
				if (!empty($error)) {
					$count_errors++;
				} 
			}
			if ($count_errors) {
				flash('notices','Aveti '.$count_errors.' erori in formular. Va rugam sa le rezolvati.', 'alert alert-danger');
			} else {
				$justInserted = $this->generalModel->addGeneral("organizatii", $fields, $data['values']);
				if($justInserted) {
					flash('notices', 'Organizatie Adaugata');
				//	redirect("facturi/show/single/".$justInserted);
				} else {
					flash('notices','Ceva nu a mers bine!', 'alert alert-danger');
				}
			}
		} else {
			foreach($fields as $key=>$field) {
				if($field->show_on_create_page == 0)  {
					unset($fields[$key]);
				}	
			}
			
			$data['values'] = array();
		}

		$data['fields'] = $fields;
		$data['title'] = "Adauga o nou organizatie";
		$this->view("organizatii/create", $data);
	}

}
