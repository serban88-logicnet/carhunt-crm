<?php 


class Comment {
	private $db;

	public function __construct() {
		$this->db = new Database;
	}

	public function addComment($what, $id, $continut) {
		$sql = "INSERT INTO comentarii (user_id,what,what_id,continut) VALUES (:user_id, :what, :what_id, :continut)";
		$this->db->query($sql);
		$this->db->bind(":user_id",$_SESSION['user_id']);
		$this->db->bind(":what",$what);
		$this->db->bind(":what_id",$id);
		$this->db->bind(":continut",$continut);
		if ($this->db->execute()) {
			return $this->db->lastInsertId();
		} else {
			return false;
		}
	}

	public function getComments($what,$id){
		$sql = "SELECT * FROM comentarii WHERE what = :what AND what_id = :what_id";
		$this->db->query($sql);
		$this->db->bind(":what",$what);
		$this->db->bind(":what_id",$id);
		$rows = $this->db->resultSet();
		return $rows;
	}

}