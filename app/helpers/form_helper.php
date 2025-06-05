<?php

function createForm($fields, $values, $function = "") {
	$labelClasses = "mb-1";
	// dd($function);
?>

	<div class="row g-3 mb-2">
	<?php foreach($fields as $field): ?>
		<?php 
			// if ($field->mandatory) {
			if (1) {
				if (!empty($values['errors'][$field->name.'_error'])) {
					$field->class .= ' is-invalid';
				} else {
					$field->class .= '';
				}
			}

			if ((isset($field->readonly)) && ($field->readonly == true)) {
				$disabled_text = 'readonly="readonly"';
			} else {
				$disabled_text = '';
			}

			//only set the fields as read_only when in edit mode;
			if (isset($function) && $function == 'edit') {
				if(isset($field->editable_by_user_type) && ($field->editable_by_user_type != NULL)) {
					$userType = getUserType();
		            if(!hasRight($userType, $field->editable_by_user_type)) {
		                    $disabled_text = 'readonly="readonly"';
		            } else {
		            		$disabled_text = '';
		            }
				}
			}

			if (isset($function) && $function == 'create') {
				if(isset($field->creatable_by_user_type) && ($field->creatable_by_user_type != NULL)) {
					$userType = getUserType();
		            if(!hasRight($userType, $field->creatable_by_user_type)) {
		                    $disabled_text = 'readonly="readonly"';
		            } else {
		            		$disabled_text = '';
		            }
				}
			}
				
				

			// if(isset($field->admin_only_edit)) {
			// 	if( isAgent() && ($field->admin_only_edit == 1)) {
			// 		$disabled_text = 'readonly="readonly"';
			// 	} else {
			// 		$disabled_text = '';
			// 	}
			// }
				

			if (isset($values['action']) && $values['action'] == 'edit') {
				if ((isset($field->visitable)) && ($field->visitable == true)) {
					// dd($values);
					// dd($field);
					
					$visit_text = ' - <a href="'.URLROOT.'/'.$field->source.'/show/'.$values[$field->name].'">Vizualizare</a>';
				} else {
					$visit_text = '';
				}
			} else {
				$visit_text = '';
			}

			if($field->separator_start != NULL) {
		?>
				<div class="w-100 mb--3">
					<div class="row mt-3">
						<div class="col-md-auto">
							<h5><?= $field->separator_start; ?></h5>	
						</div>
						<div class="col">
							<hr>
						</div>
					</div>
				</div>

		<?php 	
			}
		?>	
			<div class="col-md-<?=$field->col_size?>">
				<?php 
					if ($field->mandatory) {
						$mandatory = '<sup class="text-danger">*</sup>';
					} else {
						$mandatory = '';
					}
				?>
				
				
				<?php
					if ($field->type == "multi_select") {
						$multiple = "multiple";
						$field->name = $field->name."[]";
					} else {
						$multiple = '';
					}
					switch($field->type) {
					    case 'text':
					    case 'date':
					    case 'number':
					    case 'time':
					    case 'datetime-local':
					    case 'email':
					    case 'password':
					    case 'hidden':
					        // If the field type is "time", we want to render an input of type "text"
					        // and add an extra class "js-custom-timepicker" so that our JS can initialize the custom timepicker.
					        // For all other types, we simply use $field->type.
					        $inputType = ($field->type == "time") ? "text" : $field->type;
					        
					        // Append "js-custom-timepicker" to the field's class if the field type is "time"
					        $extraClass = ($field->type == "time") ? "js-custom-timepicker " : "";
					?>
					        <?= ($field->has_label) ? '<label class="'.$labelClasses.'" for="'.$field->name.'">'.$field->display_name.$mandatory.'</label> '.$visit_text : '' ?>
					        <input type="<?= $inputType ?>" 
					               name="<?= $field->name ?>" 
					               class="form-control <?= $extraClass . $field->class; ?>" 
					               <?= ($field->type == "number") ? "step='0.01'" : ""; ?> 
					               value="<?= (isset($values[$field->name])) ? $values[$field->name] : ''; ?>" 
					               <?= $disabled_text; ?> >
					<?php
					        break;
					

						case 'select':
						case 'multi_select':
				?>
							<?=($field->has_label)?'<label class="'.$labelClasses.'" for="'.$field->name.'">'.$field->display_name.$mandatory.'</label> '.$visit_text:''?>
				<?php 
							if($disabled_text == ""):
							// if(1):
				?>
						    <select data-style="btn-white" data-live-search="true" class="form-control selectpicker <?=$field->class;?>" id="<?=$field->name?>" name="<?=$field->name?>" <?= $multiple; ?> <?= $disabled_text; ?> >
						    	<option value="">----</option>
						    	<?php 
									if($field->type == "multi_select") {
										$field->name = substr($field->name, 0, -2);
									}
						    	?>
								<?php foreach($field->options as $option): ?>		    	
									<?php 
										$selected = '';
										if (isset($values[$field->name])) {
											if ($field->type == "select") {
												if ($option->id == $values[$field->name]) {
													$selected = 'selected';
												}
											} else if ($field->type == "multi_select") {
												$values_array = array();
												$values_array = explode(",", $values[$field->name]);
												if (in_array($option->id, $values_array)) {
													$selected = 'selected';
												}
											}
											
										}
										if(isset($option->name)) {
											$option->nume = $option->name;
										}
									?>
									<option <?= (isset($option->valoare))?'data-valoare="'.$option->valoare.'"':'' ?> value="<?= $option->id; ?>" <?= $selected; ?>><?= (isset($option->pretty_name))?$option->pretty_name:$option->nume ?><?= (isset($option->valoare))?' - '.$option->valoare.' %':'' ?></option>
								<?php endforeach; ?>
						    </select>
				<?php 
							else:
								// dd($field);
								foreach($field->options as $option):
									if (isset($values[$field->name])) {
										if ($option->id == $values[$field->name]) {
				?>
											<input disabled type="text" name="" class="form-control <?=$field->class;?>" value="<?= $option->nume; ?>" >
											<input type="hidden" name="<?=$field->name?>" class="form-control <?=$field->class;?>" value="<?= $option->id; ?>" >
				<?php
										}
									}
								endforeach;
							endif;
				?>
				<?php
							break;
						case 'textarea':
				?>			
							<?=($field->has_label)?'<label class="'.$labelClasses.'" for="'.$field->name.'">'.$field->display_name.$mandatory.'</label> '.$visit_text:''?>
							 <textarea class="form-control <?= $field->class; ?>" name="<?=$field->name?>" class="form-control" rows="3"><?= (isset($values[$field->name]))?$values[$field->name]:''; ?></textarea>
				<?php
							break;
						case 'checkbox':
							if(isset($values[$field->name]) && ($values[$field->name] == 1)) {
								$checked = "checked";
							} else {
								$checked = '';
							}
				?>
							<div class="form-check">
								<input class="js-hidden-checkbox-element" type='hidden' value='0' name="<?= $field->name?>">
								<input class="form-check-input js-checkbox-element" type="checkbox" value="1" name= "<?= $field->name?>" id="<?= $field->name; ?>" <?= $checked; ?> >
								<?=($field->has_label)?'<label class="form-check-label '.$labelClasses.'" for="'.$field->name.'">'.$field->display_name.$mandatory.'</label> '.$visit_text:''?>
							</div>
				<?php 		
							break;
							case 'file':
				?>
								<?=($field->has_label)?'<label class="'.$labelClasses.'" for="'.$field->name.'">'.$field->display_name.$mandatory.'</label> '.$visit_text:''?>
								<input type="<?=$field->type?>" name="<?=$field->name?>" class="form-control <?=$field->class;?>" value="<?= (isset($values[$field->name]))?$values[$field->name]:''; ?>" <?= $disabled_text; ?> >
				<?php
								break;
							case 'multi_file':
				?>
								<?=($field->has_label)?'<label class="'.$labelClasses.'" for="'.$field->name.'">'.$field->display_name.$mandatory.'</label> '.$visit_text:''?>
								<input type="file" name="<?=$field->name?>[]" class="form-control <?=$field->class;?>" multiple <?= $disabled_text; ?> >
				<?php
								break;
							case 'separator':
				?>
								<hr/>
								<?php if($field->display_name != ""): ?>
									<p class="h5 my-separator"><span><?= $field->display_name; ?></span></p>
								<?php endif; ?>
				<?php
							break;
					}
				// if ($field->mandatory):
				if(1):
				?>
					<div class="invalid-feedback"><?=(isset($values['errors'][$field->name.'_error']))?$values['errors'][$field->name.'_error']:''?></div>
				<?php
				endif;
				?>
			</div>
			<?php if($field->break_after): ?>
				<div class="w-100"></div>
			<?php endif; ?>
	<?php endforeach; ?>
	</div>
<?php
}

function addFieldsInData($fields, $data) {
	// dd($fields);
	// dd($data);
	// dd($_POST);
	// dd($_FILES);
	if(isset($_FILES)) {
	    foreach($_FILES as $key => $file) {
	        if(is_array($file['name'])) {
	            // Handle multi-file upload
	            $uploadedFiles = [];
	            foreach($file['name'] as $index => $fileName) {
	                if($fileName != "") {
	                    // Store the uploaded file name
	                    $uploadedFiles[] = $fileName;
	                }
	            }
	            // If there are uploaded files, join them with a separator or store as an array
	            if(!empty($uploadedFiles)) {
	                $_POST[$key] = implode(',', $uploadedFiles); // Store as a comma-separated string (or store as an array if needed)
	            } elseif(isset($data['values'][$key]) && !empty($data['values'][$key])) {
	                // If no new files were uploaded, keep the old values
	                $_POST[$key] = $data['values'][$key];
	            } else {
	                $_POST[$key] = "";
	            }
	        } else {
	            // Handle single file upload
	            if($file['name'] != "") {
	                $_POST[$key] = $file['name'];
	            } elseif(isset($data['values'][$key]) && !empty($data['values'][$key])) {
	                $_POST[$key] = $data['values'][$key];
	            } else {
	                $_POST[$key] = "";
	            }
	        }
	    }
	}

	// dd($_POST);

	foreach ($fields as $field) {
		if (($field->type == "multi_select") && (isset($_POST[$field->name]))) {
			
			$_POST[$field->name] = implode(",", $_POST[$field->name]);

		}
		if (isset($_POST[$field->name])) {
			// print_r($_POST[$field->name]);
			$data['values'][$field->name] = trim($_POST[$field->name]);
		} else {
			$data['values'][$field->name] = NULL;
		}

		if($field->type == "file") {
			$target_dir = "uploads/";
			$target_file = $target_dir . basename($_FILES[$field->name]["name"]);
			// dd($target_file);
			$upload = move_uploaded_file($_FILES[$field->name]["tmp_name"],$target_file);
			// dd($upload);
			// dd($_FILES[$field->name]);
		} elseif($field->type == "multi_file") {
		    // Multiple file upload case
		    $target_dir = "uploads/";
		    $fileNames = [];
		    
		    // Loop through each uploaded file
		    foreach ($_FILES[$field->name]['name'] as $index => $fileName) {
		        if (!empty($fileName)) {
		            // Generate the target path for each file
		            $target_file = $target_dir . basename($fileName);
		            $tmp_name = $_FILES[$field->name]["tmp_name"][$index];
		            
		            // Move the file to the uploads directory
		            if (move_uploaded_file($tmp_name, $target_file)) {
		                // Save the uploaded file name to the array
		                $fileNames[] = $fileName;
		            }
		        }
		    }
		    
		    // Join the uploaded file names into a CSV string
		    $_POST[$field->name] = implode(',', $fileNames);
		}
		
		if ($field->mandatory) {
			if (empty($data['values'][$field->name])) {
				$data['values']['errors'][$field->name."_error"] = 'Completati si campul <i>'.$field->display_name.'</i>';
			} else {
				$data['values']['errors'][$field->name."_error"] = '';
			}
		}

	}
	
	return $data;
}


function removeFields($fields, $ignore) {
	foreach($fields as $key=>$field) {
		if(in_array($field->name, $ignore)) {
			unset($fields[$key]);
		}
	}

	return $fields;
}

function processForm($what, $data, $action = "") {

	if($what == "utilizatori") {
		if(!passwordStrength($data['parola'])) {
			$data['errors']['parola_error'] = "Parola trebuie sa contina cel putin 8 caractere printre care litere mari, litere mici, si cifre";

		}
		if($data['parola'] != $data['confirmare_parola']) {
			$data['errors']['confirmare_parola_error'] = "Parolele nu se potrivesc";
		}

		
		if($action != "editare") {
			$controller = new Controller;
			$controller->generalModel = $controller->model('General');
			$user = $controller->generalModel->getItemByItem($what,"email",$data['email']);
			if($user) {
				$data['errors']['email_error'] = "Aceasta adresa de email este deja inregistrata";
			}
		}
			
	}

	// if($what == "inchirieri") {
	// 	if($data['data_sfarsit'] < $data['data_inceput']) {
	// 		$data['errors']['data_sfarsit_error'] = "Data de Sfarsit nu poate fi mai devreme ca data de inceput";
	// 	}
	// 	// dd($data);

	// 	$data_sfarsit = $data['data_sfarsit'];
	// 	$data_inceput = $data['data_inceput'];
	// 	$masina_id = $data['masina_id'];
	// 	$id = $data['id'];

	// 	$controller = new Controller;
	// 	$controller->generalModel = $controller->model('General');
	// 	$query = "SELECT * FROM inchirieri
	// 			WHERE (data_inceput <= '$data_sfarsit')
 //  				AND (data_sfarsit >= '$data_inceput')
 //  				AND (masina_id = $masina_id)
 //  				AND (id NOT LIKE $id)";
	// 	$results = $controller->generalModel->executeQuery($query);

	// 	if(!empty($results)) {
	// 		$data['errors']['data_inceput_error'] = "Masina nu este disponibila in aceasta perioada!";
	// 	} 
		
	// }

	if($what == "oferte") {
	    $today = date('Y-m-d'); // Get today's date in 'Y-m-d' format
	    
	    // Check if data_sfarsit is earlier than data_inceput
	    if($data['data_sfarsit'] < $data['data_inceput']) {
	        $data['errors']['data_sfarsit_error'] = "Data de Sfarsit nu poate fi mai devreme ca data de inceput";
	    }

	    // Check if data_inceput is earlier than today
	    if($data['data_inceput'] < $today) {
	        $data['errors']['data_inceput_error'] = "Data de Inceput nu poate fi inainte de astăzi";
	    }
	}

	
	if ($what == "masini") {
	    $controller = new Controller;
	    $controller->generalModel = $controller->model('General');

	    // Check for duplicate 'nume', excluding the current car ID if editing
	    $query = "SELECT * FROM masini WHERE nume = :nume AND activa_inactiva = 29 AND id <> :id";
	    $params = [
	        ':nume' => $data['nume'],
	        ':id'   => $data['id'] ?? 0 // use 0 if not set, avoids excluding everything on create
	    ];
	    $result = $controller->generalModel->executeQueryPdo($query, $params);
	    if ($result) {
	        $data['errors']['nume_error'] = "Acest număr este deja folosit pentru o mașină activă. <a href='/index/detalii/masini/".$result[0]->id."' target='_blank'>Vezi Mașina.</a>";
	    }

	    // Check for duplicate VIN, excluding the current car ID if editing
	    $query = "SELECT * FROM masini WHERE vin_number = :vin_number AND activa_inactiva = 29 AND id <> :id";
	    $params = [
	        ':vin_number' => $data['vin_number'],
	        ':id'         => $data['id'] ?? 0
	    ];
	    $result = $controller->generalModel->executeQueryPdo($query, $params);
	    if ($result) {
	        $data['errors']['vin_number_error'] = "Acest VIN este deja folosit pentru o mașină activă. <a href='/index/detalii/masini/".$result[0]->id."' target='_blank'>Vezi Mașina.</a>";
	    }
	}

	
		


	
	return($data);
}


// --- Helper function for chain bookings (declare only once) ---
if (!function_exists('getChainBookings')) {
    function getChainBookings($id) {
    	$controller = new Controller;
	    $controller->generalModel = $controller->model('General');

        $result = [];
        $q = "SELECT * FROM inchirieri WHERE id = :id";
        $r = $controller->generalModel->executeQueryPdo($q, [':id'=>$id]);
        if (!empty($r)) {
            $result[] = $r[0];
            // Find any extensions (children) of this booking
            $q2 = "SELECT * FROM inchirieri WHERE inchiriere_extinsa_de_la_id = :id";
            $r2 = $controller->generalModel->executeQueryPdo($q2, [':id'=>$id]);
            foreach ($r2 as $ext) {
                $result = array_merge($result, getChainBookings($ext->id));
            }
        }
        return $result;
    }
}

//get last in chain
function getLastBookingInChain($id) {
	$controller = new Controller;
	$controller->generalModel = $controller->model('General');

    $currentId = $id;
    while (true) {
        // Look for a booking that extends this one
        $q = "SELECT id FROM inchirieri WHERE inchiriere_extinsa_de_la_id = :id";
        $r = $controller->generalModel->executeQueryPdo($q, [':id' => $currentId]);
        if (!empty($r)) {
            // There's a child, continue with it
            $currentId = $r[0]->id;
        } else {
            // No further extension, this is the last in the chain
            break;
        }
    }
    return $currentId;
}

//get all in the chain from the last one
function getChainBackwards($id) {
	$controller = new Controller;
	$controller->generalModel = $controller->model('General');

    $chain = [];
    $currentId = $id;
    while ($currentId) {
        $chain[] = $currentId;
        // Get parent
        $q = "SELECT inchiriere_extinsa_de_la_id FROM inchirieri WHERE id = :id";
        $r = $controller->generalModel->executeQueryPdo($q, [':id'=>$currentId]);
        if (!empty($r) && $r[0]->inchiriere_extinsa_de_la_id) {
            $currentId = $r[0]->inchiriere_extinsa_de_la_id;
        } else {
            break;
        }
    }
    return $chain; // from last to root
}

/**
 * Get the total paid ('platit') for the entire extension chain, given any booking's ID.
 * This works forwards and backwards: collects all linked extensions both ways.
 * @param Controller $controller
 * @param int $id  The ID of any booking in the chain.
 * @return float   The total sum of 'platit' for the entire chain.
 */
function getChainTotalPaid($id) {
	$controller = new Controller;
	$controller->generalModel = $controller->model('General');
    // Helper to collect all IDs in the chain (bidirectional: up to root, then all extensions down)
    $chainIds = [];

    // Step 1: Go BACKWARDS to the root
    $currentId = $id;
    while ($currentId) {
        $chainIds[] = $currentId;
        $q = "SELECT inchiriere_extinsa_de_la_id FROM inchirieri WHERE id = :id";
        $r = $controller->generalModel->executeQueryPdo($q, [':id'=>$currentId]);
        if (!empty($r) && $r[0]->inchiriere_extinsa_de_la_id) {
            $currentId = $r[0]->inchiriere_extinsa_de_la_id;
        } else {
            break;
        }
    }
    // Now $chainIds has all ancestors up to root (including $id). We'll collect forwards next.

    // Step 2: Go FORWARDS (down the chain), breadth-first, from every node we haven't visited
    $visited = array_flip($chainIds); // for quick lookup
    $toCheck = $chainIds; // start with all the backward IDs found (usually just root)
    while (!empty($toCheck)) {
        $checkingId = array_pop($toCheck);
        $q = "SELECT id FROM inchirieri WHERE inchiriere_extinsa_de_la_id = :id";
        $r = $controller->generalModel->executeQueryPdo($q, [':id'=>$checkingId]);
        foreach ($r as $child) {
            if (!isset($visited[$child->id])) {
                $chainIds[] = $child->id;
                $toCheck[] = $child->id;
                $visited[$child->id] = true;
            }
        }
    }

    // Step 3: Now, sum 'platit' for all collected IDs
    $totalPaid = 0;
    foreach ($chainIds as $cid) {
        $q = "SELECT platit FROM inchirieri WHERE id = :id";
        $r = $controller->generalModel->executeQueryPdo($q, [':id'=>$cid]);
        if (!empty($r)) {
            $totalPaid += floatval($r[0]->platit);
        }
    }
    return $totalPaid;
}


function passwordStrength($password) {
	$uppercase = preg_match('@[A-Z]@', $password);
	$lowercase = preg_match('@[a-z]@', $password);
	$number = preg_match('@[0-9]@', $password);
	if(!$uppercase || !$lowercase || !$number || strlen($password) < 8) {
		return false;
	} else {
		return true;
	}
}


function getFieldByItem($item,$search,$fields) {
	foreach($fields as $field) {
		if($field->$item == $search)
			return $field;
	}
} 

function getOptionByItem($item,$search,$options) {
	foreach($options as $option) {
		if($option->$item == $search)
			return $option;
	}
} 


function getOptionsByItem($item,$search,$options) {
	$temp = array();
	$search = explode(",", $search);
	foreach($search as $searchedItem) {
		foreach($options as $option) {
			if($option->$item == $searchedItem)
				array_push($temp, $option);
		}
	}
	return($temp);
} 


?>