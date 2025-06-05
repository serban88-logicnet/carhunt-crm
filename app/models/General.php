<?php 

class General{

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

	public function __construct() {
		$this->db = new Database;
	}

	public function executeQuery(string $query) {
	    // Trim leading and trailing whitespace
	    $trimmedQuery = trim($query);
	    
	    // Run the provided query
	    $this->db->query($trimmedQuery);
	    
	    // Execute the query
	    if ($this->db->execute()) {	
	        // If the query is a SELECT statement, return the result set.
	        // Use the trimmed query to check if it starts with SELECT.
	        if (stripos($trimmedQuery, 'SELECT') === 0) {
	            return $this->db->resultSet();
	        }
	        return true;
	    } else {
	        return false;
	    }
	}


public function executeQueryPdo(string $query, array $params = []) {
    // Trim the query to remove any unwanted whitespace.
    $trimmedQuery = trim($query);
    
    // Run the provided query
    $this->db->query($trimmedQuery);

    // Bind parameters
    foreach ($params as $key => $value) {
        $this->db->bind($key, $value);
    }
    
    // Execute the query
    if ($this->db->execute()) {
        // If the query is a SELECT statement, return the result set.
        if (stripos($trimmedQuery, 'SELECT') === 0) {
            return $this->db->resultSet();
        }
        // If the query is an INSERT statement, return the last insert id.
        if (stripos($trimmedQuery, 'INSERT') === 0) {
            return $this->db->lastInsertId();
        }
        return true;
    } else {
        return false;
    }
}


	public function getItems($tabel, $whatToGet = "*") {
		$sql = "SELECT $whatToGet FROM $tabel";
		$this->db->query($sql);
		$rows = $this->db->resultSet();
		return $rows;
	}

	public function getItemsWithSort($tabel, $sortBy, $order, $whatToGet = "*", $perPage = "all", $page = 1) {
		$sortBy = str_replace("-","_",$sortBy);
		if($perPage == "all") {
			$sql = "SELECT $whatToGet FROM $tabel ORDER BY $sortBy $order";	
		} else {
			$offset = ($page - 1) * $perPage;
			$sql = "SELECT $whatToGet FROM $tabel ORDER BY $sortBy $order LIMIT $perPage OFFSET $offset";
		}
		
		// dd($sql);
		$this->db->query($sql);
		$rows = $this->db->resultSet();
		return $rows;
	}

	public function getCazuriHome() {
		$sql = "SELECT * FROM cazuri WHERE data_limita > CURDATE() ORDER BY data_limita asc LIMIT 0,10";	
		$this->db->query($sql);
		$rows = $this->db->resultSet();
		return $rows;
	}

	public function getContracteHome() {
    	$userType = getUserType();
    	if(hasRight($userType,"2,3")) {
        	$assigned = " AND assigned_to = ".$_SESSION['user_id']." ";
        } else {
        	$assigned = "";
        }
		$sql = "SELECT * FROM contracte WHERE data_terminare > CURDATE() ".$assigned." ORDER BY data_terminare asc LIMIT 0,10";	
		$this->db->query($sql);
		$rows = $this->db->resultSet();
		return $rows;
	}

	public function getCreatedAt($tabel,$date) {
		
	}

	public function getSalesUsers() {
		$sql = "SELECT id, nume FROM utilizatori WHERE tip IN (1,2,6)";	
		$this->db->query($sql);
		$rows = $this->db->resultSet();
		return $rows;
	}

	public function getServiceUsers() {
		$sql = "SELECT id, nume FROM utilizatori WHERE tip IN (3,7)";	
		$this->db->query($sql);
		$rows = $this->db->resultSet();
		return $rows;
	}

	//functie care extrage doar copiii relevanti (ex: doar echipamentele de la organizatiile assigned)
	public function createConexiuni($tabel) {
		$userType = getUserType();
		$tabeleCuConexiuni = tabeleCuConexiuni;
		$tabeleCuConexiuniService = tabeleCuConexiuniService;
		$tempSql = "";
		foreach($tabeleCuConexiuni as $conexiune) {
			if(hasRight($userType,"2") && ($tabel == $conexiune['child'])) {
				$sql = "SELECT id FROM ".$conexiune['parent']." WHERE assigned_to = ".$_SESSION['user_id'];
				$this->db->query($sql);
				$result = $this->db->resultSet();
				$list = "";
				foreach($result as $temp) {
					$list .= $temp->id.",";
				}
				$list = substr($list, 0, -1);
				//if we don't have a list cause there's not parent stuff assigned, we set the list to a random ID so it returns empty
				if($list == "") {
					$list = "574354327590234552355342";
				} 
				//this is to show everyone, no matter what organizations they're assigned the equipments called "BIROU BUCURESTI" and the Depozite
				if($tabel == "echipamente" && $conexiune['parent'] == "organizatii") {
					$tempSql = " AND ".$conexiune['parentSg']."_id IN (".$list.",'1410','1412','1415','1416','1418','1419')";	
				} else {
					$tempSql = " AND ".$conexiune['parentSg']."_id IN (".$list.")";	
				}
				
			}
		}

		foreach($tabeleCuConexiuniService as $conexiune) {
      //  dd($conexiune);
			if(hasRight($userType,"3") && ($tabel == $conexiune['child'])) {
				$sql = "SELECT id FROM ".$conexiune['parent']." WHERE service_assigned_to LIKE '".$_SESSION['user_id']."' OR service_assigned_to LIKE '".$_SESSION['user_id'].",%' OR service_assigned_to LIKE '%,".$_SESSION['user_id'].",%' OR service_assigned_to LIKE '%,".$_SESSION['user_id']."'";
					
            	$this->db->query($sql);
				$result = $this->db->resultSet();
				$list = "";
				foreach($result as $temp) {
					$list .= $temp->id.",";
				}
				$list = substr($list, 0, -1);
				//if we don't have a list cause there's not parent stuff assigned, we set the list to a random ID so it returns empty
				if($list == "") {
					$list = "574354327590234552355342";
				} 
				$tempSql = " AND ".$conexiune['parentSg']."_id IN (".$list.")";
			}
		}
		// dd($tempSql);
		return $tempSql;
	}

	//functie care se asigura ca le apar celor relevanti doar entitatile atribuite
	public function createAssigned($tabel) {
		$userType = getUserType();
		$assigned = "";
		$tabeleCuAssigned = tabeleCuAssigned;
		$tabeleCuAssignedService = tabeleCuAssignedService;
		if((hasRight($userType,"2") && in_array($tabel, $tabeleCuAssigned)) || (hasRight($userType,"3") && in_array($tabel, array("contracte","intalniri","sarcini")))) {

			//this is to show everyone, no matter what organizations they're assigned the organization called "BIROU BUCURESTI" and the Depozite
			if($tabel == "organizatii") {
				$tempSql = " OR id IN ('5749','1410','1412','1415','1416','1418','1419') ";
			} else {
				$tempSql = "";
			}

			$assigned = "AND (assigned_to =".$_SESSION['user_id'].$tempSql.") ";
		} elseif(hasRight($userType,"3") && in_array($tabel, $tabeleCuAssignedService)) {
			$assigned = " AND (service_assigned_to LIKE '".$_SESSION['user_id']."' OR service_assigned_to LIKE '".$_SESSION['user_id'].",%' OR service_assigned_to LIKE '%,".$_SESSION['user_id'].",%' OR service_assigned_to LIKE '%,".$_SESSION['user_id']."') ";
		}
    	

		if($tabel == "organizatii") {
			// dd($assigned);
		}

		return $assigned;
	}


	public function getItemsByItemWithSort($tabel, $search, $value, $sortBy, $order, $whatToGet = "*", $perPage = "all", $page = 1) {
		
		
		$assigned = $this->createAssigned($tabel);
		$conexiuniSql = $this->createConexiuni($tabel);
		

		$sortBy = str_replace("-","_",$sortBy);
		if($perPage == "all") {
			$offset = "";
		} else {
			$offset = ($page - 1) * $perPage;
			$offset = " LIMIT $perPage OFFSET $offset";
		}


		$sql = "SELECT $whatToGet FROM $tabel WHERE $search = :value ".$assigned.$conexiuniSql." ORDER BY $sortBy $order".$offset;	
		if($tabel == "organizatii") {
			// dd($sql);
		}
		// dd($sql);
		if($search == "deleted") {
			// dd($sql);
		}

		$this->db->query($sql);
		$this->db->bind(':value', $value);
		$rows = $this->db->resultSet();
		// dd($rows);
		return $rows;
	}

	public function getItemById($tabel, $id, $whatToGet = "*") {
		$sql = "SELECT $whatToGet FROM $tabel WHERE id = :id";
		// dd($sql);
		$this->db->query($sql);
		$this->db->bind(':id', $id);
		$row = $this->db->single();
		return $row;
	}

	function getItemsByItem ($tabel, $search, $value, $whatToGet = "*") {

		$assigned = $this->createAssigned($tabel);
		$conexiuniSql = $this->createConexiuni($tabel);

		$sql = "SELECT $whatToGet FROM $tabel WHERE $search = :value".$assigned.$conexiuniSql;
		// dd($sql);
		$this->db->query($sql);
		$this->db->bind(':value', $value);
		$rows = $this->db->resultSet();
		return $rows;
	}

	function getItemByItem ($tabel, $search, $value, $whatToGet = "*") {
		$sql = "SELECT $whatToGet FROM $tabel WHERE $search = :value";
		// dd($sql);
		$this->db->query($sql);
		$this->db->bind(':value', $value);
		$rows = $this->db->single();
		return $rows;
	}

	function getItemsFromTabel ($tabel,$whatToGet = "*", $perPage = "all", $page = 1) {

		$assigned = $this->createAssigned($tabel);
		$conexiuniSql = $this->createConexiuni($tabel);

		if($perPage == "all") {
			$offset = "";
		} else {
			$offset = ($page - 1) * $perPage;
			$offset = " LIMIT $perPage OFFSET $offset";
		}

		$sql = "SELECT $whatToGet FROM $tabel WHERE 1 ".$assigned.$conexiuniSql.$offset;
		$this->db->query($sql);
		// dd($sql);
		$rows = $this->db->resultSet();
		// dd($rows);
		return $rows;
	}

	function searchTable ($tabel, $searchedFields, $sortBy, $order, $whatToGet = "*", $perPage = "all", $page = 1) {
		$searchQuery = "";
		$i = 1;
		
		$assigned = $this->createAssigned($tabel);
		$conexiuniSql = $this->createConexiuni($tabel);
    
   	 	// dd($assigned);
		
		// dd($searchedFields);

		foreach($searchedFields as $key=>$field) {
			if($i == 1) {
				$searchQuery .= "WHERE ";
			} else {
				$searchQuery .= " AND ";
			}
			if($field["type"] == "date") {
				$field["value"] = explode("|", $field["value"]);
				$searchQuery .= $field["nume"]." BETWEEN '".$field["value"][0]."' AND '".$field["value"][1]."'";
			} elseif(str_ends_with($field['nume'], "_id")) {
				$searchQuery .= "LOWER(".$field["nume"].") LIKE LOWER('".$field["value"]."')"; 	
			} else {
				$searchQuery .= "LOWER(".$field["nume"].") LIKE LOWER('%".$field["value"]."%')"; 	
			}
			
			$i++;
		}

		// dd($searchQuery);
		if($perPage != "all") {
			$offset = ($page - 1) * $perPage;
			$offset = "LIMIT $perPage OFFSET $offset";
		} else {
			$offset = "";
		}

		$sql = "SELECT ".$whatToGet." FROM $tabel ".$searchQuery. "AND deleted = 0 ".$assigned.$conexiuniSql." ORDER BY $sortBy $order ".$offset;

		// dd($sql);
		
		$this->db->query($sql);
		$rows = $this->db->resultSet();
		return $rows;
	}

	function searchTableException ($tabel, $searchedFields, $sortBy, $order, $whatToGet = "*", $perPage = "all", $page = 1) {
		$searchQuery = "";
		$i = 1;
		
		$assigned = $this->createAssigned($tabel);
		$conexiuniSql = $this->createConexiuni($tabel);
    
    	

		foreach($searchedFields as $key=>$field) {
			if($i == 1) {
				$searchQuery .= "WHERE ";
			} else {
				$searchQuery .= " AND ";
			}
			
			if($field["type"] == "date") {
				$field["value"] = explode("|", $field["value"]);
				$searchQuery .= $field["nume"]." BETWEEN '".$field["value"][0]."' AND '".$field["value"][1]."'";
			} else {
				$searchQuery .= "LOWER(".$field["nume"].") NOT LIKE LOWER('%".$field["value"]."%')"; 	
			}
			
			$i++;
		}
		if($perPage != "all") {
			$offset = ($page - 1) * $perPage;
			$offset = "LIMIT $perPage OFFSET $offset";
		} else {
			$offset = "";
		}
		$sql = "SELECT ".$whatToGet." FROM $tabel ".$searchQuery. "AND deleted = 0 ".$assigned.$conexiuniSql." ORDER BY $sortBy $order ".$offset;
		
		$this->db->query($sql);
		$rows = $this->db->resultSet();
		return $rows;
	}

	public function addGeneral($tabel, $fields, $values) {
		$field_string = $param_string = "";
		foreach ($fields as $key=>$field) {
			if($field->type == "password") {
				$values[$field->name] = password_hash($values[$field->name], PASSWORD_DEFAULT);
			}
			$field_string .= $field->name.", ";
			$param_string .= ":".$field->name.", ";
		}

		// dd($values);
		$user = $_SESSION['user_id'];
		$userType = getUserType();

		if($userType == 3 && $tabel == "cazuri") {
			$sql = "INSERT INTO $tabel (".substr($field_string, 0, -2).",created_by, service_assigned_to) VALUES(".substr($param_string, 0, -2).", $user, $user)";
		} elseif ($userType == 2 && $tabel == "intalniri") {
			$sql = "INSERT INTO $tabel (".substr($field_string, 0, -2).",created_by, assigned_to) VALUES(".substr($param_string, 0, -2).", $user, $user)";
		} else {
			$sql = "INSERT INTO $tabel (".substr($field_string, 0, -2).",created_by) VALUES(".substr($param_string, 0, -2).", $user)";
		}
		// dd($sql);
		
		$this->db->query($sql);
		foreach ($fields as $field) {
			if($values[$field->name] == "") {
				$values[$field->name] = NULL;
			}
			$this->db->bind(':'.$field->name, $values[$field->name]);
		}
		// dd($values);
		if ($this->db->execute()) {
			return $this->db->lastInsertId();
		} else {
			return false;
		}
	}

	

	public function editGeneral($tabel, $id, $fields, $values, $hasHistory = 0) {
		if(!isset($values['fisier_editare'])) {
			$values['fisier_editare'] = "";
		}
		
		if($hasHistory) {
			$this->createHistory($tabel, $id, $fields, $values['observatii_editare'], $values['fisier_editare']);	
		}
		
		$field_string = "";
		foreach ($fields as $key=>$field) {
			if($field->type == "password") {
				$values[$field->name] = password_hash($values[$field->name], PASSWORD_DEFAULT);
			}
			$field_string .= $field->name." = :".$field->name.", ";	
		}
		// dd("STOP");
		$sql = "UPDATE $tabel SET ".substr($field_string, 0, -2)." WHERE id = ".$id;
		// dd($fields);
		$this->db->query($sql);
		foreach ($fields as $field) {
			if($values[$field->name] == "") {
				$values[$field->name] = NULL;
			}
			$this->db->bind(':'.$field->name, $values[$field->name]);
		}

		if ($this->db->execute()) {
			return true;
		} else {
			return false;
		}
	}

	public function createHistory($tabel,$id, $fields, $observatii, $fisier) {
		// dd($fields);
		$field_string = "";
		foreach ($fields as $key=>$field) {
			$field_string .= $field->name.", ";	
		}
		$field_string = substr($field_string, 0, -2);

		$historyTabel = $tabel."_history";
		$user = $_SESSION['user_id'];
		if($tabel != "echipamente") {
			$sql = "INSERT INTO $historyTabel (original_id, $field_string, observatii_editare, edited_by) SELECT id, $field_string, :observatii_editare, $user FROM $tabel WHERE id = :id";
		} else {
			$sql = "INSERT INTO $historyTabel (original_id, $field_string, observatii_editare, fisier_editare, edited_by) SELECT id, $field_string, :observatii_editare, :fisier_editare, $user FROM $tabel WHERE id = :id";
		}
		
		// dd($sql);
		$this->db->query($sql);
		$this->db->bind(':id',$id);
		$this->db->bind(':observatii_editare',$observatii);
		if($tabel == "echipamente") {
			$this->db->bind(':fisier_editare',$fisier);	
		}
		

		if ($this->db->execute()) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($tabel, $id) {
		$user = $_SESSION['user_id'];
		$sql = "UPDATE ".$tabel." SET deleted = 1, deleted_by = ".$user." WHERE id = ".$id;

		// dd($sql);
		$this->db->query($sql);
		if ($this->db->execute()) {
			return true;
		} else {
			return false;
		}
	}

}