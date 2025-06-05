<?php require APPROOT.'/views/inc/header.php'; ?>
<?php // dd($_GET); ?>
<?php //dd($_SERVER); ?>
<div class="row">
	<?php flash('notices'); ?>
	<?php if(!empty($data['searchableFields'])): ?>
		<div class="card card-body bg-white shadow mt-0">
			<?php flash('search_notices'); ?>
			<h5>Cautare</h5>
			<form class="" action="<?php echo URLROOT;?>/index/cautare/<?= $data['what']; ?>"/" method="POST">
				<?php createSearchSection($data['searchableFields'],$data['what']); ?>
				<div class="row">
					<div class="col-md">
						<input type="submit" value="Cautare" name="submit" class="btn btn-primary mt-3 float-end">
			<!-- 			<input type="submit" value="Cautare cu Exceptie" name="submit" class="btn btn-primary mt-3 float-end me-3"> -->
					</div>
				</div>
			</form>
		</div>
	<?php endif; ?>

	<div class="card card-body bg-white shadow mt-3 overflow-scroll">
		<div class="row">
			<div class="col-md-6">
				<h3 class="mb-4"><?= $data['title']; ?></h3>
				<h5 class="mb-4">Nr. Rezultate: <?= $data['count']; ?></h5>
			</div>
			<div class="col-md-6 text-end">
				<?php if(isset($data['content']['buttons'])): ?>
					<?php foreach($data['content']['buttons'] as $button): ?>
						<?php 
						if(isset($button['target'])) {
							$target = $button['target'];
						} else {
							$target = "";
						}
						?>
						<a href="<?= URLROOT.'/'.$button['link'] ?>" target="<?= $target; ?>" class="btn <?= $button['class'] ?>"><?= $button['name'] ?></a>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php createIndexTable($data['items'], $data['fields'],$data['sortData'], $data['what'], $data['usefulLinks']); ?>
	</div> 
	
	<?php
		if(isset($data['allItems'])):
			$testArray = createDownloadArray($data['allItems'], $data['fields'], $data['what']);

			// $htmlTable = createDownloadTable($data['allItems'], $data['fields'], $data['what']); 	
			// // dd($data['fields']);
			// //echo $htmlTable;
			// $dom = new DOMDocument();
			// $dom->loadHTML($htmlTable);
			// $table = $dom->getElementsByTagName('table')->item(0);
			// $rows = $table->getElementsByTagName('tr');
			// $tableData = array();

			// // Loop through each row
			// foreach ($rows as $row) {
			//     // Get all the cells in the row
			//     $cells = $row->getElementsByTagName('td');

			//     // Initialize an empty array to store the cell values
			//     $row_data = array();
			//     // Loop through each cell
			//     foreach ($cells as $cell) {
			//         // Add the cell value to the row data array
			//         $row_data[] = $cell->nodeValue;
			//     }
			//     // Add the row data to the data array
			//     $tableData[] = $row_data;
			// }


			$fileName = "download-".$_SESSION['user_id'].".csv";
			$fp = fopen($fileName, 'w');
			// dd($tableData);
			// Loop through the data array and write each row to the CSV file
			foreach ($testArray as $row) {
			    fputcsv($fp, $row);
			}
			// dd($fp);
			// Close the file
			fclose($fp);
			// redirect('/download.csv');


		endif;
	?>
</div>

<?php 
	if($data['function'] != "istoricLista"):
		require APPROOT.'/views/inc/pagination.php';
	endif;
	require APPROOT.'/views/inc/footer.php'; 
?>