<?php 

function checkFormErrors($errors) {
	foreach ($errors as $error) {
		if (!empty($error)) {
			$count_errors++;
		} 
	}
	if ($count_errors) {
		flash('facturi_notices','Aveti '.$count_errors.' erori in formular. Va rugam sa le rezolvati.', 'alert alert-danger');
		return true();
	} else {
		return false();
	}
}


function addValuesToFields($fields, $item) {
	// dd($fields);
	foreach($fields as $key=>$field) {
		$fieldName = $field->name;
		$field->values = array();
		$field->values['value'] = property_exists($item, $fieldName) ? $item->$fieldName : "";
		if($field->type == "select" || $field->type == "multi_select") {
			if($field->source_list == "utilizatori-service" || $field->source_list == "utilizatori-vanzari") {
				$field->source_list = "utilizatori";
			}
			if(isset($field->options) && !empty($field->options)){
				$field->values['value'] = explode(",", $field->values['value'] ?? "");
				$field->values['displayValue'] = "";
				foreach($field->options as $option) {
					if(in_array($option->id, $field->values['value'])) {
						$field->values['displayValue'] .= $option->nume.",";
					} else {
						$field->values['displayValue'] .= "";
					}
				}
			$field->values['value'] = implode(",", $field->values['value']);
			} else {
				$field->values['displayValue'] = "";
			}
			$field->values['displayValue'] = substr($field->values['displayValue'], 0, -1);

			$field->values['link'] = "index/detalii/".$field->source_list."/" . (property_exists($item, $fieldName) ? $item->$fieldName : "");
		} elseif($field->type == "file") {
			$field->values['displayValue'] = $item->$fieldName;
			$field->values['link'] = "uploads/".$item->$fieldName;
		} elseif($field->type == "multi_file") {
		    // Multiple file upload case (CSV list of file names)
		    if(isset( $item->$fieldName)) {
		    	$fileNames = explode(',', $item->$fieldName); // Split the CSV list into an array
			    $fileLinks = [];
			    $displayValues = [];

			    foreach($fileNames as $fileName) {
			        $trimmedFileName = trim($fileName); // Trim to ensure no extra spaces
			        if (!empty($trimmedFileName)) {
			            $fileLinks[] = "uploads/".$trimmedFileName; // Generate the link for each file
			            $displayValues[] = $trimmedFileName; // Use the file name as the display value
			        }
			    }

			    // Store the comma-separated file links and display values
			    $field->values['displayValue'] = implode(',', $displayValues);
			    $field->values['link'] = implode(',', $fileLinks);	
		    } else {
		    	$field->values['displayValue'] = $field->values['link'] = "";
		    }
			    
		} else {
			$field->values['displayValue'] = property_exists($item, $fieldName) ? $item->$fieldName : "";
			$field->values['link'] = "";
		}
	}
	// dd($fields);
	return $fields;	
}





?>