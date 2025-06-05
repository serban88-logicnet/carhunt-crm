<?php
class Oferte extends Controller{

	public function __construct(){
		$this->fieldModel = $this->model('Field');
		$this->generalModel = $this->model('General');
	}

	public function refuza_oferta($id) {
		$query = "UPDATE oferte SET status_oferta = 17 WHERE id LIKE :id";
		$params = [
        	':id' => $id
    	];
    	$this->generalModel->executeQueryPdo($query, $params);
    	flash('notices',"Oferta a fost marcata ca 'refuzata'",'alert alert-warning');
    	redirect("index/lista/oferte");
	}

}