<?php
class Index extends Controller{

	public function __construct(){
		$this->userModel = $this->model('User');
		$this->fieldModel = $this->model('Field');
		$this->generalModel = $this->model('General');
		$this->commentModel = $this->model('Comment');
		$this->echipamentModel = $this->model('Echipament');
		$this->perPage = 25;
		$this->fileName = "download-".$_SESSION['user_id'].".csv";
	}

	public function lista($what, $modifier = "active") {

		$sortBy = "id";
		$sortOrder = "DESC";
		if(isset($_GET['sort']['sortBy'])) {
			$sortBy = $_GET['sort']['sortBy'];
			if(isset($_GET['sort']['sortOrder'])){
				$sortOrder = $_GET['sort']['sortOrder'];
			}
		}

		if(isset($_GET['pagination'])) {
			$page = $_GET['pagination']['goToPage'];
		} else {
			$page = 1;
		}

		
		$data['function'] = "lista";

		$object = $this->generalModel->getItemByItem("object_types", 'table_name', $what);
		
		$userType = getUserType();


		if(!hasRight($userType, $object->can_view)) {

			flash('notices','Nu aveti drepturi de vizualizare pentru '.$object->plural, 'alert alert-warning');
			redirect("");
		}

		
		$fields = $this->fieldModel->getFields($what, 2);

		$searchableFields = $this->fieldModel->getFields($what,4);
		if($modifier == "sterse") {
			$deleted = 1;
		} else {
			$deleted = 0;
		}

		$itemCount = count($this->generalModel->getItemsByItemWithSort($what,"deleted",$deleted,"id","desc","id"));
		
		$items = $this->generalModel->getItemsByItemWithSort($what,"deleted",$deleted,$sortBy,$sortOrder,"*", $this->perPage, $page);


		$items = createItemDetails($items, $fields);

		$tempArray = personalizeResults($items, $fields, $what);
		// dd($tempArray);
		$items = $tempArray[0];
		$fields = $tempArray[1];
		$fields = removeOptionsFromFields($fields);
		// dd($fields);


		$allItems = $this->generalModel->getItemsByItemWithSort($what,"deleted",$deleted,$sortBy,$sortOrder,"*", "all");
		$allItems = createItemDetails($allItems, $fields);

		$data['content']['buttons'] = array(
			array("name"=>"Descarca Rezultate", "link"=>$this->fileName, "class"=>"btn-secondary")
		);

		$data['title'] = "Lista de ".$object->plural;
		if($modifier == "sterse") {
			$data['title'] = "Lista de ".$object->plural." <span class='text-danger'>STERSE</span>";
		}
		$data['items'] = $items;
		$data['allItems'] = $allItems;
		$data['fields'] = $fields;
		// dd($fields);
		$data['searchableFields'] = $searchableFields;
		$data['usefulLinks']['single'] = "index/detalii/".$what;
		$data['usefulLinks']['edit'] = "index/editare/".$what;
		$data['sortData'] = array("sort" => array("sortOrder"=>$sortOrder,"sortBy"=>$sortBy));
		$data['what'] = $what;

		//pagination variables
		$data['count'] = $itemCount;
		$data['perPage'] = $this->perPage;
		$data['currentPage'] = $page;
		// $data['paginationLink'] = '/index/lista/'.$what."/".$modifier;


		$this->view("general/list", $data);
	}

	public function creare($what, $var = "") {
		$data['function'] = "creare";
		//these are fields that we have in the fields table but not in the object database
		$fieldsToIgnore = array("confirmare_parola","edited_by","created_by","edited_at");
		$object = $this->generalModel->getItemByItem("object_types", 'table_name', $what);

		//User Rights check
		$userType = getUserType();
		if(!hasRight($userType, $object->can_add)) {
			flash('notices','Nu aveti drepturi de creare pentru '.$object->plural, 'alert alert-warning');
			redirect("index/lista/".$what);
		}

		$data = [];
		$count_errors = 0;
		$fields = $this->fieldModel->getFields($what);

		$userType = getUserType();
		//make it so the sales and services normal user doesn't get to choose attributed 
		if($userType == 2 || $userType == 3) {
			$fields = removeFields($fields,array("assigned_to","service_assigned_to"));
		}


		if(isset($_GET['from'])) {
			//get the details data for the item we're coming from
			$itemSource = $this->generalModel->getItemById($_GET['from'],$_GET['id']);
			//get the object data for the item we're coming from
			$objectSource = $this->generalModel->getItemByItem("object_types", 'table_name', $_GET['from']);

			$_GET['from'] = strtolower($objectSource->singular)."_id";
			// dd($_GET);
			$data['from'] = json_encode($_GET);
		}

		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$fields = removeFields($fields,array("observatii_editare"));
			// dd($fields);
			$data = addFieldsInData($fields, $data);


			
			$data['values'] = processForm($what, $data['values'],"creare");
			// dd($data);

			
			foreach ($data['values']['errors'] as $error) {
				if (!empty($error)) {
					$count_errors++;
				} 
			}
			if ($count_errors) {
				foreach($fields as $key=>$field) {
					if($field->show_on_create_page == 0)  {
						unset($fields[$key]);
					}	
				}
				flash('notices','Aveti '.$count_errors.' erori in formular. Va rugam sa le rezolvati.', 'alert alert-danger');
			} else {
				$fields = removeFields($fields,$fieldsToIgnore);
				// dd($_POST);
				$justInserted = $this->generalModel->addGeneral($what, $fields, $data['values']);
				if($justInserted) {

					//edit client variaable istoric_client depending on offer
					if ($what == "oferte" && !empty($data['values']['client_id']) && $data['values']['client_id'] != 0) {
					    $clientId = $data['values']['client_id'];
					    $newIscVal = null;

					    // Get current istoric_client value
					    $currentQuery = "SELECT istoric_client FROM clienti WHERE id = :id";
					    $currentRow = $this->generalModel->executeQueryPdo($currentQuery, [':id' => $clientId]);
					    $currentVal = isset($currentRow[0]->istoric_client) ? $currentRow[0]->istoric_client : null;

					    // Normalize
					    $currentVal = ($currentVal === "" || $currentVal === null) ? null : (int)$currentVal;

					    // Now, apply your rules:
					    if ($currentVal === null || $currentVal === 36) {
					        if (!empty($data['values']['status_oferta'])) {
					            if ($data['values']['status_oferta'] == 18) {
					                $newIscVal = 37;
					            } elseif ($data['values']['status_oferta'] == 16) {
					                $newIscVal = 38;
					            }
					        }
					    } elseif ($currentVal === 37) {
					        if (!empty($data['values']['status_oferta']) && $data['values']['status_oferta'] == 16) {
					            $newIscVal = 38;
					        }
					    }
					    // If already 38, do nothing.

					    if ($newIscVal !== null) {
					        $updateQuery = "UPDATE clienti SET istoric_client = :istoric_client WHERE id = :id";
					        $updateParams = [
					            ':istoric_client' => $newIscVal,
					            ':id' => $clientId
					        ];
					        $this->generalModel->executeQueryPdo($updateQuery, $updateParams);
					    }
					}


					if(isset($_POST['comingFromOffer'])) {
						$comingFromOfferId = $_POST['comingFromOffer']; 
						$query = "UPDATE oferte SET client_id = :client_id WHERE id = :id";
						$params = [
        					':client_id' => $justInserted,
        					':id' => $comingFromOfferId
        				];
        				$this->generalModel->executeQueryPdo($query, $params);
        				flash('notices', $object->singular.' adaugat');
        				if(isset($_POST['comingFromCalendar'])) {
        					$comingFromCalendar = $_POST['comingFromCalendar']; 
        					if($comingFromCalendar == 1) {
        						redirect("pages/calendar");			
        					}
        				} else {
        					redirect("index/detalii/oferte/".$comingFromOfferId);		
        				}
					} elseif(isset($_POST['comingFromCalendar'])) {
						flash('notices', $object->singular.' adaugat');
						$comingFromCalendar = $_POST['comingFromCalendar']; 
    					if($comingFromCalendar == 1) {
    						redirect("pages/calendar");			
    					}
					} else {
						flash('notices', $object->singular.' adaugat');
						redirect("index/detalii/".$what."/".$justInserted);	
					}
					
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

			//this helps us preload fields by using $_GET. EX: index/creare/what?nume=343243242
			foreach($fields as $field) {
				if(isset($_GET[$field->name])) {
					$temp = $field->name;
					if($field->type == "select") {
						if(isset($field->options) && !empty($field->options)) {
							foreach($field->options as $option) {

								if($option->nume == $_GET[$temp]) {
									$_GET[$temp] = $option->id;
									break;
								}
							}
						}
					} 

					$data['values'][$temp] = $_GET[$temp];
				}
			}
		}
		// dd($fields);
		$data['fields'] = $fields;
		$data['title'] = "Adauga ".(($object->pronume == "F")?"o":"un")." <strong>".$object->singular."</strong>";
		if(isset($_GET['from'])) {
			// $data['title'] .= " pentru ".$objectSource->sg_articulat." <strong>".$itemSource->nume."</strong>";
		}
		$data['what'] = $what;

		$this->view("general/create", $data);
	}

	public function detalii($what, $id) {
		$data['function'] = "detalii";
		$fieldsToIgnore = array("observatii_editare","fisier_editare");
		$object = $this->generalModel->getItemByItem("object_types", 'table_name', $what);

		$userType = getUserType();


		if(!hasRight($userType, $object->can_view)) {
			flash('notices','Nu aveti drepturi pentru acest tip de actiune.', 'alert alert-warning');
			redirect("index/lista/".$what);
		}

		$item = $this->generalModel->getItemById($what,$id);


	
		$connections = $this->generalModel->getItemsByItemWithSort("connections","parent",$what,"sort","ASC");


		
		$connectionsArray = array();
		foreach($connections as $connection) {
			$tempArray = array();
			$tempFields = $this->fieldModel->getFields($connection->child, 2);
			
			$searchedColumn = $object->singular."_id";
			if($object->table_name == "utilizatori") {
				$searchedColumn = "assigned_to";
			}
			if($connection->parent == "leaduri" && $connection->child == "contacte") {
				$searchedColumn = "original_lead_id";
			}
			$tempItems = $this->generalModel->getItemsByItemWithSort($connection->child,$searchedColumn,$id,"id","desc","*");
			foreach($tempItems as $key=>$tempItem) {
				if($tempItem->deleted == 1) {
					unset($tempItems[$key]);
				}
			}
			$tempItems = createItemDetails($tempItems, $tempFields);
			// dd($tempItems);

			$tempArray['what'] = $connection->child;
			$tempArray['fields'] = $tempFields;
			$tempArray['items'] = $tempItems;
			

			// dd($tempArray);
			array_push($connectionsArray, $tempArray);
		}
		// dd($connectionsArray);

		$fields = $this->fieldModel->getFields($what,1);
		$fields = addValuesToFields($fields, $item);
		$fields = removeFields($fields,$fieldsToIgnore);

		// dd($fields);
		
		if($object->has_comments == 1) {
			$comments =  $this->commentModel->getComments($what,$id);
			
			foreach($comments as $comment) {
				$comment->userInfo = $this->generalModel->getItemById("utilizatori",$comment->user_id);
			}
			$data['comments']  = $comments;
		}

		$data['fields'] = $fields;
		$data['item'] = $item;
		$data['what'] = $what;
		$data['deleteRights'] = $object->can_delete;
		$data['hasHistory'] = $object->has_history;
		$data['hasComments'] = $object->has_comments;
		$data['title'] = "Detalii ".$object->singular;
		if($item->deleted == 1) {
			$data['title'] = "Detalii ".$object->singular." <span class='text-danger'><strong>OBIECT STERS</strong></span> ";
		}
		$data['connections'] = $connectionsArray;

		// dd($item);

		$istoricButton = array("name"=>"Istoric Schimbari", "link"=>"index/istoric/".$what."/lista/".$id, "class"=>"btn-primary");
		$deleteButton = array("name"=>"Sterge", "link"=>"index/sterge/".$what."/".$id, "class"=>"btn-danger js-confirm");
		$editButton = array("name"=>"Editare", "link"=>"index/editare/".$what."/".$id, "class"=>"btn-primary");
		$backButton = array("name"=>"Inapoi", "link"=>"index/lista/".$what, "class"=>"btn-secondary");

		 if($item->deleted == 1) {
		 	$deleteButton = array("name"=>"Sterge", "link"=>"index/sterge/".$what."/".$id, "class"=>"btn-danger d-none");
		 	$editButton = array("name"=>"Editare", "link"=>"index/editare/".$what."/".$id, "class"=>"btn-primary d-none");
		 	$istoricButton = array("name"=>"Istoric Schimbari", "link"=>"index/istoric/".$what."/lista/".$id, "class"=>"btn-primary d-none");
		 	$backButton = array("name"=>"Inapoi", "link"=>"index/lista/".$what."/sterse", "class"=>"btn-secondary");
		}

		$userType = getUserType();
		if(!hasRight($userType, $object->can_delete)) {
			$deleteButton = array("name"=>"Sterge", "link"=>"index/sterge/".$what."/".$id, "class"=>"btn-danger d-none");
		}
		
		$data['content']['buttons'] = array();

		// Add Istoric only if hasHistory is true/1
		if (!empty($data['hasHistory'])) {
		    // $data['content']['buttons'][] = $istoricButton;
		}

		// Always add Edit
		$data['content']['buttons'][] = $editButton;

		// You can do the same for back/delete, etc, as needed
		// if ($showBack) $data['content']['buttons'][] = $backButton;
		// if ($showDelete) $data['content']['buttons'][] = $deleteButton;


		if($what == "oferte") {
			//add button for offer PDF, show different dependnf on if accepted or just sent:

			if ($data['item']->status_oferta == 18) {
			    array_unshift($data['content']['buttons'], array(
			        "name" => "PDF Oferta Trimisa",
			        "link" => "index/pdf-oferta/" . $id,
			        "class" => "btn-success",
			        "target" => "_blank",
			        "download" => "{$data['item']->client_nume}_{$data['item']->client_prenume}_oferta_{$id}.pdf"
			    ));
			} else {
			    array_unshift($data['content']['buttons'], array(
			        "name" => "PDF Oferta Acceptata",
			        "link" => "index/pdf-oferta/" . $id . "/acceptata/",
			        "class" => "btn-success",
			        "target" => "_blank",
			        "download" => "{$data['item']->client_nume}_{$data['item']->client_prenume}_oferta_{$id}.pdf"
			    ));
			}

			//check if it has an undefined as of yet client, to add the button for that as well
			if($data['item']->client_id == 0) {
				$numeClient = $data['item']->client_nume;
				$prenumeClient = $data['item']->client_prenume;
				$telefonClient = $data['item']->client_telefon;
				$emailClient = $data['item']->client_email;
				$observatiiClient = $data['item']->client_alte_info;

				array_unshift($data['content']['buttons'], array("name"=>"Genereaza Client", "link"=>"index/creare/clienti?nume=$numeClient&prenume=$prenumeClient&telefon=$telefonClient&email=$emailClient&comingFromOffer=$id", "class"=>"btn-primary", "target"=>"_self"));
			}

			//if offer is Acceptata or Alocata, it has a contract
			if($data['item']->status_oferta == 16 || $data['item']->status_oferta == 19) {
				array_unshift($data['content']['buttons'], array("name"=>"PDF Contract", "link"=>"index/pdf-contract/".$id, "class"=>"btn-secondary", "target"=>"_blank"));
			}

			//if oferta is Trimisa, show button to potentially accept or refuse
			if($data['item']->status_oferta == 18) {
				array_unshift($data['content']['buttons'], array("name"=>"Accepta", "link"=>"index/editare/oferte/".$id."?action=acceptaOferta", "class"=>"btn-success", "target"=>"_self"));
				array_unshift($data['content']['buttons'], array("name"=>"Refuza", "link"=>"oferte/refuza-oferta/".$id, "class"=>"btn-danger", "target"=>"_self"));
			}
		}

		if($what == "inchirieri") {
			$totalPaid = getChainTotalPaid($id);
			if($data['item']->status != 31) {
				array_unshift($data['content']['buttons'], array("name"=>"Incaseaza Plata", "link"=>"#", "class"=>"btn-success js-my-incaseaza-plata", "attrs" => ["data-total-platit"=>$totalPaid, "data-total-de-plata"=>$data['item']->pret]));
				array_unshift($data['content']['buttons'], array("name"=>"Extinde", "link"=>"#", "class"=>"btn-primary js-my-extinde-inchiriere"));
			} else {
				$lastInChain = getLastBookingInChain($id);
				$url = "index/detalii/inchirieri/".$lastInChain;
				array_unshift($data['content']['buttons'], array("name"=>"Finala", "link"=>$url, "class"=>"btn-success"));
			}
		}

		$lastEdit = null;
		if (!empty($object->has_history)) {
		    // Fetch the most recent history entry for this object
		    $historyTable = $what . "_history";
		    $latestHistory = $this->generalModel->getItemsByItemWithSort($historyTable, "original_id", $id, "edited_at", "desc", "*", 1); // get last 1

		    if (!empty($latestHistory)) {
		        $lastEdit = $latestHistory[0];

		        // Also fetch the user info for edited_by
		        if (isset($lastEdit->edited_by) && is_numeric($lastEdit->edited_by)) {
		            $user = $this->generalModel->getItemById('utilizatori', $lastEdit->edited_by);
		            $lastEdit->edited_by_user = $user ? $user->nume : "Utilizator necunoscut";
		        } else {
		            $lastEdit->edited_by_user = "Utilizator necunoscut";
		        }
		    }
		}

		$data['lastEdit'] = $lastEdit;

		// Check for recent edits (last 5 minutes)
		if (!empty($lastEdit)) {
		    $now = new DateTime();
		    $editedAt = new DateTime($lastEdit->edited_at);
		    $interval = $now->getTimestamp() - $editedAt->getTimestamp();

		    if ($interval <= 5 * 60) { // 5 minutes
		        // Get editor name
		        $userName = 'Utilizator necunoscut';
		        if (!empty($lastEdit->edited_by)) {
		            $user = $this->generalModel->getItemById('utilizatori', $lastEdit->edited_by);
		            if ($user && !empty($user->nume)) {
		                $userName = $user->nume;
		                if (!empty($user->prenume)) {
		                    $userName .= ' ' . $user->prenume;
		                }
		            }
		        }
		        flash(
		            'notices',
		            '⚠️ Acest obiect a fost modificat în ultimele 5 minute de <strong>' . htmlspecialchars($userName) . '</strong>!',
		            'alert alert-warning'
		        );
		    }
		}



		// dd($data['fields']);
		// dd($data);

		$this->view("general/single", $data);
	}

	public function pdf_contract($id,$source = "oferta") {
		if($source == "oferta") {
			$query = "SELECT o.id, o.data_inceput, o.data_sfarsit, o.ora_inceput, o.ora_sfarsit,o.pret_final, c.nume as nume_client, c.prenume as prenume_client, c.cnp, c.telefon, c.email, c.adresa, c.nr_permis
			        FROM oferte o
			        JOIN clienti c ON o.client_id = c.id
			        WHERE o.id = $id";

		} elseif($source == "inchiriere") {
			$query = "SELECT i.id, i.data_inceput, i.data_sfarsit, i.ora_inceput, i.ora_sfarsit, i.pret as pret_final, c.nume as nume_client, c.prenume as prenume_client, c.cnp, c.telefon, c.email, c.adresa, c.nr_permis
			        FROM inchirieri i
			        JOIN clienti c ON i.client_id = c.id
			        WHERE i.id = $id";

		}

		$contract = $this->generalModel->executeQuery($query);
		$data['contract'] = $contract;

		// dd($contract); 

		$this->view("oferte/contracte-pdf", $data);
	}

	public function pdf_oferta($id, $what = "trimisa") {

		
		if($what == "trimisa") {
			$temp = "oferta_trimisa";
		} elseif ($what == "acceptata") {
			$temp = "oferta_acceptata";
		}

		//get clase pret from oferte 
		$query = "SELECT cp.*, 
            ol1.nume as combustibil_name, 
            ol2.nume as transmisie_name
	        FROM oferte o
	        JOIN clasePret cp ON FIND_IN_SET(cp.id, o.".$temp.")
	        LEFT JOIN other_lists ol1 ON ol1.id = cp.combustibil
	        LEFT JOIN other_lists ol2 ON ol2.id = cp.transmisie
	        WHERE o.id = $id";	
		

		$clasePret = $this->generalModel->executeQuery($query);

		// dd($clasePret);

		//get oferta details
		$query = "SELECT * FROM oferte WHERE id LIKE $id";
		$oferta = $this->generalModel->executeQuery($query);

		//get price limits
		$query = "SELECT id, name, display_name FROM clasePret_fields WHERE name IN ('pret_1','pret_2','pret_3','pret_4','pret_5')";
		$fieldsCp = $this->generalModel->executeQuery($query);
		
		$data['oferta'] = $oferta;
		$data['clasePret'] = $clasePret;
		$data['fieldsCp'] = $fieldsCp;
		$data['what'] = $what;

		// dd($data);

		$this->view("oferte/oferta-pdf", $data);
	}

	public function istoric($what, $view, $id) {
		$data['function'] = "istoric";
		$historyTabel = $what."_history";
		$object = $this->generalModel->getItemByItem("object_types", 'table_name', $what);

		$userType = getUserType();
		if(!hasRight($userType, $object->can_edit)) {
			flash('notices','Nu aveti drepturi de vizualizare a istoricului de modificari pentru '.$object->plural, 'alert alert-warning');
			redirect("index/detalii/".$what."/".$id);
		}

		if($view == "lista") {
			$fields = $this->fieldModel->getFields($what, 5);
			foreach($fields as $key=>$field) {
				if($field->name == "created_by") {
					$field->name = "edited_by";
					$field->display_name = "Modificat De";
				}
			}
			$item = $this->generalModel->getItemById($what,$id);

			$historyItems = $this->generalModel->getItemsByItemWithSort($historyTabel,"original_id",$id,"id","desc");
			// dd($historyIndex);

			foreach($historyItems as $key=>$historyItem) {
				foreach($fields as $key2=>$field) {
					if($field->type == "select" || $field->type == "multi_select") {
						$fieldName = $field->name;
						foreach($field->options as $option) {
							if($option->id == $historyItem->$fieldName) {
								$temp = $fieldName."Info";
								$historyItem->$temp = $option;
							}
						}
					}
					if($field->name == "created_at") {
						$field->name = "edited_at";
						$field->display_name = "Modificat La";
					}
				}

				// --- NEW: For edited_by field, set up the Info property with user object
			    if (isset($historyItem->edited_by) && is_numeric($historyItem->edited_by)) {
			        $user = $this->generalModel->getItemById('utilizatori', $historyItem->edited_by);
			        if ($user) {
			            $historyItem->edited_byInfo = [$user]; // Make it an array of objects (even if just one)
			        } else {
			            $historyItem->edited_byInfo = [];
			        }
			    }
			}
			
			// dd($fields);
			$data['title'] = "Istoric modificari ".$item->nume;
			$data['items'] = $historyItems;
			$data['count'] = count($data['items']);
			$data['fields'] = $fields;
			$data['sortData'] = array();
			$data['what'] = $what;
			$data['function'] = "istoricLista";
			$data['usefulLinks']['single'] = "index/istoric/".$what."/detalii";
			$data['content']['buttons'] = array(
				array("name"=>"Inapoi", "link"=>"index/detalii/".$what."/".$id, "class"=>"btn-secondary")
			);

			$this->view("general/list", $data);
			
		} elseif($view == "detalii") {
			$object = $this->generalModel->getItemByItem("object_types", 'table_name', $what);
			$item = $this->generalModel->getItemById($historyTabel,$id);

			//we check if the item we got is the most recent iteration, so we know whom to compare it to
			$allItems = $this->generalModel->getItemsByItemWithSort($historyTabel,"original_id",$item->original_id,"edited_at","desc");
			if($allItems[0] == $item) {
				$nextItem = $this->generalModel->getItemById($what,$item->original_id);
			} else {
				foreach($allItems as $key=>$historyItem) {
					if($historyItem->id == $item->id) {
						$nextItem = $this->generalModel->getItemById($historyTabel,$allItems[$key-1]->id);
					}
				}
			}
			$fields = $this->fieldModel->getFields($what,1);
			// dd($nextItem);

			foreach($fields as $key=>$field) {
				$temp = $field->name;
				if($temp == "observatii_editare") {
					continue;
				}
				if(isset($item->$temp) && isset($nextItem->$temp)) {
					if($item->$temp != $nextItem->$temp) {
						$field->changed = 1;
						$field->oldValue = $nextItem->$temp;
						if($field->type=="select" || $field->type == "multi_select") {
							$field->oldValue = "";
							$options = getOptionsByItem("id",$nextItem->$temp,$field->options);
							foreach($options as $option) {
								$field->oldValue .= $option->nume.",";
							}
							$field->oldValue = rtrim($field->oldValue,",");
						}
					} else {
						$field->changed = 0;
					}
				}
				if($field->name == "created_by") {
					$field->name = "edited_by";
					$field->display_name = "Modificat De";
					$field->changed = 0;
				}
				if($field->name == "created_at") {
					$field->name = "edited_at";
					$field->display_name = "Modificat La";
					$field->changed = 0;
				}
			}

			$fields = addValuesToFields($fields, $item);

			// dd($fields);
			$data['fields'] = $fields;
			$data['item'] = $item;
			$data['what'] = $what;
			$data['function'] = "istoricDetalii";

			$data['content']['buttons'] = array(
				array("name"=>"Inapoi", "link"=>"index/istoric/".$what."/lista/".$item->original_id, "class"=>"btn-secondary")
			);

			// dd($data['fields']);

			$data['title'] = "Istoric ".$object->singular." - ".$item->edited_at;
			$this->view("general/single", $data);
		}
	}

	public function editare($what, $id) {
		$fieldsToIgnore = array("observatii_editare","fisier_editare","confirmare_parola");

		$userType = getUserType();

		$data['function'] = "editare";
		$object = $this->generalModel->getItemByItem("object_types", 'table_name', $what);
		$item = $this->generalModel->getItemById($what,$id);
		$fields = $this->fieldModel->getFields($what,3);
		$hasHistory = $object->has_history;

		//make it so users with the service trait can't modify the attributed sign
		if($userType == 3) {
			array_push($fieldsToIgnore, "service_assigned_to");
			$fields = removeFields($fields,array("service_assigned_to"));
		}

		//make it so users with the sales trait can't modify the attributed sign
		if($userType == 2) {
			array_push($fieldsToIgnore, "assigned_to");
			$fields = removeFields($fields,array("assigned_to"));
		}

		
		if(!hasRight($userType, $object->can_edit)) {
			flash('notices','Nu aveti drepturi de editare pentru '.$object->plural, 'alert alert-warning');
			redirect("index/detalii/".$what."/".$id);
		}

		if($userType == 3 && $what == "cazuri" && $item->service_assigned_to != $_SESSION['user_id']) {
			flash('notices','Nu aveti drepturi de editare pentru cazul unui coleg.', 'alert alert-warning');
			redirect("index/detalii/".$what."/".$id);
		}

		$data['values'] = json_decode(json_encode($item), True);
		$data['fields'] = $fields;
		$data['what'] = $what;

		

		$count_errors = 0;

		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$data = addFieldsInData($fields, $data);


			$data['values'] = processForm($what, $data['values'], "editare");
			// dd($data);
			// dd($_POST);
			foreach ($data['values']['errors'] as $error) {
				if (!empty($error)) {
					$count_errors++;
				} 
			}
			if ($count_errors) {
				flash('notices','Aveti '.$count_errors.' erori in formular. Va rugam sa le rezolvati.', 'alert alert-danger');
			} else {
				$fields = removeFields($fields,$fieldsToIgnore);
				if($this->generalModel->editGeneral($what, $id, $fields, $data['values'], $hasHistory)) {

					//edit client variaable istoric_client depending on offer
					if ($what == "oferte" && !empty($data['values']['client_id']) && $data['values']['client_id'] != 0) {
					    $clientId = $data['values']['client_id'];
					    $newIscVal = null;

					    // Get current istoric_client value
					    $currentQuery = "SELECT istoric_client FROM clienti WHERE id = :id";
					    $currentRow = $this->generalModel->executeQueryPdo($currentQuery, [':id' => $clientId]);
					    $currentVal = isset($currentRow[0]->istoric_client) ? $currentRow[0]->istoric_client : null;

					    // Normalize
					    $currentVal = ($currentVal === "" || $currentVal === null) ? null : (int)$currentVal;

					    // Now, apply your rules:
					    if ($currentVal === null || $currentVal === 36) {
					        if (!empty($data['values']['status_oferta'])) {
					            if ($data['values']['status_oferta'] == 18) {
					                $newIscVal = 37;
					            } elseif ($data['values']['status_oferta'] == 16) {
					                $newIscVal = 38;
					            }
					        }
					    } elseif ($currentVal === 37) {
					        if (!empty($data['values']['status_oferta']) && $data['values']['status_oferta'] == 16) {
					            $newIscVal = 38;
					        }
					    }
					    // If already 38, do nothing.

					    if ($newIscVal !== null) {
					        $updateQuery = "UPDATE clienti SET istoric_client = :istoric_client WHERE id = :id";
					        $updateParams = [
					            ':istoric_client' => $newIscVal,
					            ':id' => $clientId
					        ];
					        $this->generalModel->executeQueryPdo($updateQuery, $updateParams);
					    }
					}
					
					if(isset($_POST['comingFromCalendar'])) {
						$comingFromCalendar = $_POST['comingFromCalendar']; 
    					if($comingFromCalendar == 1) {
    						redirect("pages/calendar");		
    					}	
    				} else {
    					flash('notices', $object->singular.' modificat');
						redirect("index/detalii/".$what."/".$id);	
    				}
					
				} else {
					flash('notices','Ceva nu a mers bine!', 'alert alert-danger');
				}
			}
		}
		// dd($data);
		$data['title'] = "Editare ".$object->singular." ".$item->nume;

		$lastEdit = null;
		if (!empty($object->has_history)) {
		    // Fetch the most recent history entry for this object
		    $historyTable = $what . "_history";
		    $latestHistory = $this->generalModel->getItemsByItemWithSort($historyTable, "original_id", $id, "edited_at", "desc", "*", 1); // get last 1

		    if (!empty($latestHistory)) {
		        $lastEdit = $latestHistory[0];

		        // Also fetch the user info for edited_by
		        if (isset($lastEdit->edited_by) && is_numeric($lastEdit->edited_by)) {
		            $user = $this->generalModel->getItemById('utilizatori', $lastEdit->edited_by);
		            $lastEdit->edited_by_user = $user ? $user->nume : "Utilizator necunoscut";
		        } else {
		            $lastEdit->edited_by_user = "Utilizator necunoscut";
		        }
		    }
		}

		$data['lastEdit'] = $lastEdit;

		// Check for recent edits (last 5 minutes)
		if (!empty($lastEdit)) {
		    $now = new DateTime();
		    $editedAt = new DateTime($lastEdit->edited_at);
		    $interval = $now->getTimestamp() - $editedAt->getTimestamp();

		    if ($interval <= 5 * 60) { // 5 minutes
		        // Get editor name
		        $userName = 'Utilizator necunoscut';
		        if (!empty($lastEdit->edited_by)) {
		            $user = $this->generalModel->getItemById('utilizatori', $lastEdit->edited_by);
		            if ($user && !empty($user->nume)) {
		                $userName = $user->nume;
		                if (!empty($user->prenume)) {
		                    $userName .= ' ' . $user->prenume;
		                }
		            }
		        }
		        flash(
		            'notices',
		            '⚠️ Acest obiect a fost modificat în ultimele 5 minute de <strong>' . htmlspecialchars($userName) . '</strong>!',
		            'alert alert-warning'
		        );
		    }
		}


		$this->view("general/edit", $data);
	}

	public function cautare($what, $action ="") {

		$sortBy = "id";
		$sortOrder = "ASC";
		if(isset($_GET['sort']['sortBy'])) {
			$sortBy = $_GET['sort']['sortBy'];
			if(isset($_GET['sort']['sortOrder'])){
				$sortOrder = $_GET['sort']['sortOrder'];
			}
		}

		if(isset($_GET['pagination'])) {
			$page = $_GET['pagination']['goToPage'];
		} else {
			$page = 1;
		}

		$data['function'] = "editare";

		if($action == "resetare") {

			redirect("index/lista/".$what);
		} else {
			$fields = $this->fieldModel->getFields($what, 2);
			
			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				
				foreach($_POST as $key=>$field) {
					if(empty($field)) {
						unset($_POST[$key]);
					}
				}
				// dd($_POST);
				$searchedFields = array("searchTerms"=>$_POST);
				redirect("index/cautare/".$what."?".http_build_query($searchedFields));
			} 

			if($_SERVER['REQUEST_METHOD'] ==  "GET") {
				// dd($_GET);
				if(isset($_GET['url'])) {
					unset($_GET['url']);
				}
				
				$searchedFields = $_GET['searchTerms'];



				if(count($searchedFields) == 1 && (isset($searchedFields['submit']))) {
					unset($searchedFields['submit']);
				} else {
					$searchType = $searchedFields['submit'];
					unset($searchedFields['submit']);
				}


			}
			
			// dd($_SERVER['REQUEST_URI']);
			if(empty($searchedFields)) {
				flash("search_notices","Alegeti termeni de cautare","alert alert-warning");
				redirect("index/lista/".$what);
			} else {


				//we create an array containing all needed info for the searched fields (actual value, display value, actual name, display name)
				$tempArray = array();
				$searchableFields = $this->fieldModel->getFields($what, 4);
				// dd($searchableFields);
				// dd($searchedFields);

				foreach($searchedFields as $name=>$value) {

					$temp = getFieldByItem("name", $name, $searchableFields);

					if($temp == NULL) {
						continue;
					}
					
					$displayValue = $value;

					if($temp->type == "select" || $temp->type == "multi_select") {
						foreach($temp->options as $option) {
							if($option->id == $value) {
								$displayValue = $option->nume;
								break;
							}
						}
					} 

					
					$temp = array("nume" => $name, "displayName" => $temp->display_name, "value"=> $value, "displayValue" => $displayValue, "type"=>$temp->type);	

					array_push($tempArray, $temp);
					
				}
				// dd($tempArray);
				$searchedFields = $tempArray;
				// dd($searchedFields);
				$results = $this->generalModel->searchTable($what,$searchedFields, $sortBy, $sortOrder, "*", $this->perPage, $page);
				$resultsCount = $this->generalModel->searchTable($what,$searchedFields, $sortBy, $sortOrder, "id", "all", $page);

				$allResults = $this->generalModel->searchTable($what,$searchedFields, $sortBy, $sortOrder, "*", "all", $page);

				$data['title'] = "Rezultatele Cautarii";
				if($searchType == "Cautare cu Exceptie") {
					$results = $this->generalModel->searchTableException($what,$searchedFields, $sortBy, $sortOrder, "*", $this->perPage, $page);
					$resultsCount = $this->generalModel->searchTableException($what,$searchedFields, $sortBy, $sortOrder, "id", "all", $page);
					$allResults = $this->generalModel->searchTableException($what,$searchedFields, $sortBy, $sortOrder, "*", "all", $page);
					$data['title'] = "Rezultatele Cautarii cu Exceptie";
				}
				$resultsCount = count($resultsCount);
				$results = createItemDetails($results, $fields);

				$allResults = createItemDetails($allResults, $fields);

				$data['content']['buttons'] = array(
					array("name"=>"Descarca Rezultate", "link"=>$this->fileName, "class"=>"btn-secondary")
				);

				// dd($results);
				$data['fields'] = $fields;
				$data['results'] = $results;
				$data['allItems'] = $allResults;
				$data['sortData'] = array("sort" => array("sortOrder"=>$sortOrder,"sortBy"=>$sortBy));
				
				$data['what'] = $what;
				$data['searchableFields'] = $searchableFields;
				$data['usefulLinks']['single'] = "index/detalii/".$what;
				$data['usefulLinks']['edit'] = "index/editare/".$what;
				$data['searchedFields'] = $searchedFields;
				

				//pagination variables
				$data['count'] = $resultsCount;
				$data['perPage'] = $this->perPage;
				$data['currentPage'] = $page;
				// dd($searchedFields);
				
				$this->view("general/search", $data);	
			}
		}
	}

	public function convert_lead($leadId, $toWhat) {
		$data = array();
		$lead = $this->generalModel->getItemById("leaduri",$leadId);
		$_POST = (array)$lead;
		$_POST['original_lead_id'] = $leadId;
		$fields = $this->fieldModel->getFields($toWhat);
		foreach($fields as $key=>$field) {
			if($field->show_on_create_page == 0)  {
				unset($fields[$key]);
			}	
		}

		

		// dd($_POST);
		$data['values'] = $_POST;
		$data['fields'] = $fields;
		$data['what'] = $toWhat;
		$data['title'] = "Convertire Lead in Contact";
		$data['function'] = "convertLead";
		$this->view("general/create", $data);

		// dd($data);	
	}

	public function sterge($what, $id) {
		$object = $this->generalModel->getItemByItem("object_types", 'table_name', $what);
		$userType = getUserType();
		if(!hasRight($userType, $object->can_delete)) {
			flash('notices','Nu aveti drepturi de stergere pentru '.$object->plural, 'alert alert-warning');
			redirect("index/lista/".$what);
		}

		if($this->generalModel->delete($what, $id)) {
			flash('notices', $object->singular.' sters cu succes', 'alert alert-warning');	
		} else {
			flash('notices', $object->singular.' nu a putut fi sters', 'alert alert-danger');
		}
		redirect("index/lista/".$what);
		
	}

	public function comentarii($action, $what, $id) {
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$fields = array();
			$content = $_POST['comment'];

			if($this->commentModel->addComment($what, $id, $content)) {
				flash('notices', 'Comentariul a fost adaugat');
			} else {
				flash('notices','Ceva nu a mers bine!', 'alert alert-danger');
			}
			redirect("index/detalii/".$what."/".$id."#comentarii");
		}
	}
}
