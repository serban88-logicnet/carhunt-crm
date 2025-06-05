<?php 


class Echipament {
	private $db;

	public function __construct() {
		$this->db = new Database;
	}

	public function getEchipamenteWithOpenCases() {
		$sql = "SELECT echipamente.* FROM echipamente LEFT JOIN cazuri ON echipamente.id = cazuri.echipament_id WHERE cazuri.status_caz != 80";
		$this->db->query($sql);
		$rows = $this->db->resultSet();
		return $rows;			
	}



}