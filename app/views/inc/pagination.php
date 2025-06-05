<?php
$totalPages = ceil($data['count']/$data['perPage']);

// Get the full URL
$fullUrl = $_SERVER['REQUEST_URI'];

// Parse the URL to extract the path
$parsedUrl = parse_url($fullUrl, PHP_URL_PATH);


$myGetArray = $_GET;
$myGetArray['pagination']['goToPage'] = $data['currentPage'];

?>

	<nav aria-label="Page navigation" class="mt-4">
		<ul class="pagination">
			<?php 
				if($data['currentPage'] > 2): 
					$myGetArray['pagination']['goToPage'] = 1;
			?>
				<li class="page-item">
					<a class="page-link" href="<?=URLROOT;?><?= $parsedUrl; ?>?<?= http_build_query($myGetArray); ?>" aria-label="First">
						<span aria-hidden="true">Prima Pagina</span>
					</a>
				</li>
			<?php 
				$myGetArray['pagination']['goToPage'] = $data['currentPage'] - 2;
			?>
				<li class="page-item"><a class="page-link" href="<?=URLROOT;?><?= $parsedUrl; ?>?<?= http_build_query($myGetArray); ?>"><?=$data['currentPage'] - 2; ?></a></li>
			<?php endif; ?>
			<?php 
				if ($data['currentPage'] > 1): 
					$myGetArray['pagination']['goToPage'] = $data['currentPage'] - 1;
			?>
				<li class="page-item"><a class="page-link" href="<?=URLROOT;?><?= $parsedUrl; ?>?<?= http_build_query($myGetArray); ?>"><?=$data['currentPage'] - 1; ?></a></li>
			<?php endif; ?>
			<li class="page-item active"><a class="page-link" href="#"><?=$data['currentPage']; ?></a></li>
			<?php 
				if ($data['currentPage'] < $totalPages): 
					$myGetArray['pagination']['goToPage'] = $data['currentPage'] + 1;

			?>
				<li class="page-item"><a class="page-link" href="<?=URLROOT;?><?= $parsedUrl; ?>?<?= http_build_query($myGetArray); ?>"><?=$data['currentPage'] + 1; ?></a></li>
			<?php endif; ?>
			<?php 
				if ($data['currentPage'] < $totalPages-1): 
					$myGetArray['pagination']['goToPage'] = $data['currentPage'] + 2;
			?>
				<li class="page-item"><a class="page-link" href="<?=URLROOT;?><?= $parsedUrl; ?>?<?= http_build_query($myGetArray); ?>"><?=$data['currentPage'] + 2; ?></a></li>
			<?php 
				$myGetArray['pagination']['goToPage'] = $totalPages;
			?>
				<li class="page-item">
					<a class="page-link" href="<?=URLROOT;?><?= $parsedUrl; ?>?<?= http_build_query($myGetArray); ?>" aria-label="Last">
						<span aria-hidden="true">Ultima Pagina</span>
					</a>
				</li>
			<?php endif; ?>
		</ul>
		<!-- <form class = "form-inline">
			<div class="form-group mr-2">
				<input type="text" class="form-control js-go-to-page-input" id="pageNumber"  placeholder="Mergi la pagina">
			</div>
			<button type="submit" class="btn btn-primary js-go-to-page-button" data-page-link="<?=URLROOT;?>/<?= $data['paginationLink']; ?>/">Mergi</button>
		</form> -->
	</nav>
<?php 

?>
