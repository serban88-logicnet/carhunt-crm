<?php
class Field {

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

	public function getFields($table, $display = 0, $withOptions = 1) {


		if($display == 1) {  //if display = 1, that means we're only getting the fields for the display page
			$modifier = ' AND show_on_details_page = 1';
		} elseif($display == 0) { // if display = 0, we're getting ALL the fields, we'll later use the 'show_on_create_page' to display or hide them;
			$modifier = '';	
		} elseif($display == 2) { // if display = 2, we're getting the fields on the index page
			$modifier = ' AND show_on_index_page = 1';	
		} elseif($display == 3) { // if display = 3, we're getting the fields on the edit page
			$modifier = ' AND show_on_edit_page = 1';
		} elseif($display == 4) { // if display = 4 we're getting the fields that are searchable
			$modifier = ' AND searchable = 1';
		} elseif($display == 5) { // if display = 5 we're getting the fields that are shown on the history index page
			$modifier = ' AND show_on_history_list_page = 1';
		}

		$sql = 'SELECT * FROM '.$table.'_fields WHERE disabled = 0'.$modifier." ORDER BY sort ASC";
		// dd($sql);

		$this->db->query($sql);
		$result = $this->db->resultSet();
		foreach ($result as $field) {
			if (($field->type == "select") || ($field->type == "multi_select")) {
				switch($field->source) {
					case 'other_lists':
						$controller = new Controller();
						$model = $controller->model('General');
						$items = $model->getItemsByItemWithSort("other_lists","list_name",$field->source_list,"sort","ASC");
						$field->options = $items;
						break;
					case 'tabel':
						$controller = new Controller();
						$model = $controller->model('General');
						$tabel = $field->source_list;
						if($tabel == "utilizatori-vanzari"):
							$options = $model->getSalesUsers();
						elseif($tabel == "utilizatori-service"):
							$options = $model->getServiceUsers();
						else:
							if($tabel == "utilizatori"):
								$whatToGet = "id, nume";
							else:
								$whatToGet = "*";
							endif;
							$options = $model->getItemsFromTabel($tabel, $whatToGet);
						endif;



						// show the series in the eq dropdowns for ease of use
						if($tabel == "clienti") {
						    foreach($options as $option) {
						        // build an array of extra bits to append
						        $extras = [];
						        if (!empty($option->cnp)) {
						            $extras[] = "CNP: " . $option->cnp;
						        }
						        if (!empty($option->telefon)) {
						            $extras[] = "Tel: " . $option->telefon;
						        }
						        // always show “First Last” plus any extras joined by “ - ”
						        $option->nume = $option->nume
						                       . " "
						                       . $option->prenume
						                       . (count($extras)>0 ? " - ".implode(" - ", $extras) : "");
						    }
						}


						

						$field->options = $options;
						break;
				}	
			}		
		}
		return $result;
	}
}