<?php 
function createIndexTable($data, $fields, $sortData, $what, $usefulLinks) {
	// dd($data);

	//get the current page if paginated, for use in counting NR CRT below
	if(!isset($_GET['pagination'])) {
		$currentPage = 1;
	} else {
		$currentPage = $_GET['pagination']['goToPage'];	
	}
	
?>
	<table class = "table table-striped my-list-table">
			<thead class="thead-dark">
				<tr>
					<th>Nr Crt</th>
					<?php foreach($fields as $key=>$field): ?>
						<?php $class = $field->class; ?>
						<th class="<?= $class; ?>">
							<?php 
								echo $field->display_name;
								$sortIcon = 'fa-sort';
								if($field->sortable == 1):
									if(isset($sortData['sort']['sortOrder']) && ($sortData['sort']['sortBy'] == $field->name)) {
										if($sortData['sort']['sortOrder'] == "ASC") {
											$sortOrder = "DESC";
											$sortIcon = 'fa-sort-desc';
										} else {
											$sortOrder = "ASC";
											$sortIcon = 'fa-sort-asc';
										}
									} else {
										$sortOrder = "DESC";
										$sortIcon = 'fa-sort-desc';
									}

									$tempUrl = $_SERVER['REQUEST_URI'];



									if(isset($_GET['url']))
										unset($_GET['url']);
									if(isset($_GET['pagination']))
										unset($_GET['pagination']);
									$myGetArray = $_GET;
									$myGetArray['sort']['sortBy'] = $field->name;
									$myGetArray['sort']['sortOrder'] = $sortOrder;
									
									if(isset($_GET['searchTerms'])):
										// dd(http_build_query($_GET));
								?>
									<a class="text-dark" href="<?= URLROOT ?>/index/cautare/<?= $what; ?>?<?= http_build_query($myGetArray); ?>"><i class='fas <?= $sortIcon ?>'></i></a>
								<?php
									else:
								?>
									<a class="text-dark" href="<?= URLROOT ?>/index/lista/<?= $what; ?>?<?= http_build_query($myGetArray); ?>"><i class='fas <?= $sortIcon ?>'></i></a>
								<?php
									endif;	

									
								endif;
							?>
						</th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php 
					//calculate the NR CRT based on the current page No
					$nrcrt = ($currentPage - 1) * 25 + 1;
					foreach($data as $item):

				?>
					<tr data-item-id="<?= $item->id; ?>">
						<td class=""><?= $nrcrt; ?></td>
						<?php 
						// dd($fields);
						foreach($fields as $key=>$field):
							$fieldName = $field->name;
							$class = $field->class;
							if (in_array($field->type, ["select", "multi_select"])) {
							    $fieldName = $field->name . "Info";
							    
							    echo "<td class='$class'>";
							    
							    if (!empty($item->$fieldName)) {
							        $output = [];
							        
							        foreach ($item->$fieldName as $optionField) {
							            if ($field->visitable == 1) {
							                $objectType = $field->source_list;
							                $output[] = "<a data-item-id='".$optionField->id."' target='_blank' href='" . URLROOT . "/index/detalii/" . $objectType . "/" . $optionField->id . "'>" . $optionField->nume . "</a>";
							            } else {
							                $output[] = $optionField->nume;
							            }
							        }
							        
							        // Join the array with commas and output the result
							        echo implode(", ", $output);
							    }
							    
							    echo "</td>";
							} else {
								if($field->visitable == 1) {
									if($field->name == "nume") {
										echo "<td class='$class'><a href='".URLROOT."/".$usefulLinks['single']."/".$item->id."'>".$item->$fieldName."</a> <a href='".URLROOT."/".$usefulLinks['edit']."/".$item->id."'><i class='bi bi-pencil-square'></i></a></td>";		
											
									} else {
										echo "<td class='$class'><a href='".URLROOT."/".$usefulLinks['single']."/".$item->id."'>".$item->$fieldName."</a></td>";		
									}
								} else {
									echo "<td class='$class'>".$item->$fieldName."</td>";		
								}
							}

						endforeach; 
						?>
					</tr>
				<?php $nrcrt++; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
<?php
}

function createSimpleIndexTable($data, $fields, $what) {
	// dd($sortData);
?>
	<table class = "table table-striped my-list-table">
			<thead class="thead-dark">
				<tr>
					<?php foreach($fields as $key=>$field): ?>
						<th><?= $field->display_name; ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach($data as $item): ?>
					<tr>
						<?php 
						foreach($fields as $key=>$field):
							$fieldName = $field->name;
							if (in_array($field->type, ["select", "multi_select"])) {
							    $fieldName = $field->name . "Info";
							    
							    echo "<td>";
							    
							    if (!empty($item->$fieldName)) {
							        $output = [];
							        
							        foreach ($item->$fieldName as $optionField) {
							            if ($field->visitable == 1) {
							                $objectType = $field->source_list;
							                $output[] = "<a target='_blank' href='" . URLROOT . "/index/detalii/" . $objectType . "/" . $optionField->id . "'>" . $optionField->nume . "</a>";
							            } else {
							                $output[] = $optionField->nume;
							            }
							        }
							        
							        // Join the array with commas and output the result
							        echo implode(", ", $output);
							    }
							    
							    echo "</td>";
							} else {
								if($field->visitable == 1) {
									echo "<td><a href='".URLROOT."/index/detalii/".$what."/".$item->id."'>".$item->$fieldName."</a></td>";		
								} else {
									echo "<td>".$item->$fieldName."</td>";		
								}
							}
						endforeach; 
						?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
<?php
}

function createDetailsTable($fields, $tableName) {
	// dd($fields);
?>
		<table class = "table table-striped table-single-view">
			<tbody>
				<?php foreach($fields as $field): ?>
					<?php 
					$thClass = "";
					if(isset($field->changed)) {
						if($field->changed) {
							$thClass .= "table-warning ";
						}
					}

					if(isset($field->class)) {
						if($field->class) {
							$thClass .= $field->class;
						}
					}

					if(isset($field->separator_start)) {
						if($field->separator_start != "") {
							echo "<tr class='table-primary'><td colspan='2'><h5 class='mb-0'>".$field->separator_start."</h5></td></tr>";
						}
					}

					?>
					<tr class="<?= $thClass; ?>">
						<th><?= $field->display_name; ?></th>
						<td>
							<?php if($field->visitable == 1 && $field->type == "select"): ?>
								<a target="_blank" href="<?= URLROOT.'/'.$field->values['link']; ?>"><?= $field->values['displayValue']; ?></a>
							<?php elseif($field->type == "file"): ?>
								<a target="_blank" href="<?= URLROOT.'/'.$field->values['link']; ?>"><?= $field->values['displayValue']; ?></a>
							<?php elseif($field->type == "multi_file"): ?>
							    <?php 
							        $fileLinks = explode(',', $field->values['link']); // Split the comma-separated string
							        $displayValues = explode(',', $field->values['displayValue']); // Split corresponding display values
							    ?>
							    <?php foreach($fileLinks as $index => $fileLink): ?>
							        <a target="_blank" href="<?= URLROOT.'/'.$fileLink; ?>"><?= isset($displayValues[$index]) ? $displayValues[$index] : 'File '.($index + 1); ?></a><br>
							    <?php endforeach; ?>
							<?php else: ?>
								<?= $field->values['displayValue']; ?>
							<?php endif; ?>
							<span class="fw-light fs-7"><?= isset($field->oldValue)?"($field->oldValue)":"" ?></span>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
<?php 
}

function createSearchSection($fields, $what) {
$cookieName = "searchedFields_".$what;
if(isset($_GET['searchTerms'])) {
	$searchedFields = $_GET['searchTerms'];
}
?>
	<div class="row gy-3">
		<?php foreach($fields as $field): ?>
			<?php if($field->searchable == 1): ?>
				<?php 
					if($field->type == "date"):
						$colSize = 4;
					else:
						$colSize = 2;
					endif;
				?>
				<div class="col-md-<?= $colSize; ?>">
					<label class="fw-bold">
						<?php 
							if($field->type == "date"):
								echo $field->display_name." (intre)";
							else:
								echo $field->display_name;
							endif;
						?>
					</label>
						<?php if($field->type == "select" || $field->type == "multi_select"): ?>
							
							<select data-style="btn-white" data-live-search="true" class="form-control selectpicker" id="<?=$field->name?>" name="<?=$field->name?>">
								<option value="">----</option>
								<?php 
								foreach($field->options as $option):  
									$temp = "";
									if(isset($searchedFields[$field->name]) && ($option->id == $searchedFields[$field->name] )):
										$temp = "selected";
									endif;
								?>
									<option <?= $temp ?> value = "<?= $option->id; ?>"><?= $option->nume; ?></option>
								<?php endforeach; ?>
							 </select>
						<?php elseif($field->type == "date"): ?>
							<div class="row gx-1">
								<div class="col-md-6">
									<input value="<?= (isset($searchedFields[$field->name]))?$searchedFields[$field->name]:""; ?>" type="<?= $field->type; ?>" name="<?= $field->name; ?>_from" class="form-control my-date-search mb-1 js-date-search-from">
								</div>
								<div class="col-md-6">
									<input value="<?= (isset($searchedFields[$field->name]))?$searchedFields[$field->name]:""; ?>" type="<?= $field->type; ?>" name="<?= $field->name; ?>_to" class="form-control my-date-search js-date-search-to">
								</div>
								<input value="<?= (isset($searchedFields[$field->name]))?$searchedFields[$field->name]:"" ?>" type="hidden" name="<?= $field->name; ?>" class="form-control js-date-search-interval">
							</div>
						<?php else: ?>
							<input value="<?= (isset($searchedFields[$field->name]))?$searchedFields[$field->name]:"" ?>" type="<?= $field->type; ?>" name="<?= $field->name; ?>" class="form-control">
						<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

<?php
}

function createItemDetails($items, $fields) {
	// dd($fields);
	foreach($items as $key=>$item) {
		foreach($fields as $key2=>$field) {
			if(($field->type == "select" || $field->type == "multi_select") && $field->source != "") {
				$fieldName = $field->name;
				if(isset($field->options) && !empty($field->options)){
					foreach($field->options as $option) {
						if($field->type == "select") {
							if($option->id == $item->$fieldName) {
								$temp = $fieldName."Info";
								$item->$temp = array();
								array_push($item->$temp, $option);
							}
						} elseif($field->type == "multi_select" && $item->$fieldName != "") {
							
							$valuesArray = explode(",", $item->$fieldName);
							
							foreach($valuesArray as $key=>$value) {
								if($option->id == $value) {
									$temp = $fieldName."Info";

									if(!isset($item->$temp)) {
										$item->$temp = array();
										array_push($item->$temp, $option);
									} else {
										array_push($item->$temp, $option);
									}

									// dd($item->$temp);

								}
							}
						}
						
					}
				}
			}
		}
	}
	// dd($items);
	// die();
	return $items;
}

function removeOptionsFromFields($fields) {
	// dd($fields);
	foreach($fields as $key=>$field) {
		unset($fields[$key]->options);
	}
	return $fields;
}

function personalizeResults($items, $fields, $what) {
	if($what == "cazuri") {
		// $temp = (object) array("name"=>"durata_caz", "display_name"=>"Durata Caz","sort"=>30,"type"=>"text","index_td_class"=>"","visitable"=>0,"sortable"=>1,"show_on_index_page"=>1);
		// $temp = array($temp);
		// array_splice($fields, 9, 0, $temp );
		// foreach($items as $item) {
		// 	if(isset($item->data_inchidere) && ($item->data_inchidere != "0000-00-00")){
		// 		$item->durata_caz = date_diff(date_create($item->data_inchidere),date_create($item->data_deschidere))->days;
		// 	} else {
		// 		$item->durata_caz = 0;
		// 	}
		// }
	}

	return(array($items,$fields));	
}

function createDownloadTable ($data, $fields, $what) {
	// dd($data);
	ob_start();

	// $tableData = array();
?>
	<table class = "table table-striped my-download-table">
			
		<tr>
			<?php foreach($fields as $key=>$field): ?>
				<td><?php echo $field->display_name; ?></td>
			<?php endforeach; ?>
		</tr>
	
		<?php foreach($data as $item): ?>
			<tr>
				<?php 
				foreach($fields as $key=>$field):
					$fieldName = $field->name;
					if(!isset($field->class)) {
						$field->class = "";
					}
					if($field->type == "select" || $field->type == "select_ajax") {
						$fieldName = $field->name."Info";
						if(!empty($item->$fieldName)) {
							echo "<td class='".$field->class."'>".$item->$fieldName->nume."</td>";	
						} else {
							echo "<td class='".$field->class."'></td>";
						}
						
					} else {
						echo "<td class='".$field->class."'>".$item->$fieldName."</td>";		
					}
				endforeach; 
				?>
			</tr>
		<?php endforeach; ?>
			
	</table>
<?php

	$htmlTable = ob_get_clean();
	return $htmlTable;

}



function createDownloadArray($data, $fields, $what) {
    $downloadArray = [];

    // Create the table header row using each field's display name.
    $headerRow = [];
    foreach ($fields as $field) {
        $headerRow[] = $field->display_name;
    }
    $downloadArray[] = $headerRow;

    // Iterate over the data to create each row.
    foreach ($data as $item) {
        $rowData = [];
        foreach ($fields as $field) {
            $fieldName = $field->name;
            // If field->class isn't set, default to empty.
            if (!isset($field->class)) {
                $field->class = "";
            }
            // If the field is a select type, we expect its data to be stored in an additional property.
            if ($field->type == "select" || $field->type == "select_ajax") {
                $fieldName = $field->name . "Info";
                if (!empty($item->$fieldName)) {
                    $value = $item->$fieldName;
                    // Check if it's an object and has property "nume"
                    if (is_object($value) && isset($value->nume)) {
                        $rowData[] = $value->nume;
                    }
                    // Or, if it's an array with a key 'nume'
                    elseif (is_array($value) && isset($value['nume'])) {
                        $rowData[] = $value['nume'];
                    }
                    else {
                        $rowData[] = "";
                    }
                } else {
                    $rowData[] = "";
                }
            } else {
                // For non-select types, simply output the value.
                $rowData[] = isset($item->$fieldName) ? $item->$fieldName : "";
            }
        }
        $downloadArray[] = $rowData;
    }
    return $downloadArray;
} 

?>