<?php require APPROOT.'/views/inc/header.php'; ?>
<div class="row">
	<div class="col-md-12 mx-auto">
		<div class="card card-body bg-white shadow-sm mt-0">
			<?php flash('notices'); ?>
			<h4><?= $data['title'] ?></h4>
			<form class="js-create-form js-create-form-<?= $data['what']; ?>" data-form-type="create-form-<?= $data['what']; ?>" action="<?php echo URLROOT;?>/index/creare/<?= $data['what']; ?>" method="POST" enctype="multipart/form-data">
				<?php createForm($data['fields'], $data['values'], "create"); ?>
				<input type="submit" value="Salveaza" name="submit" class="btn btn-primary mt-3 float-right">
			</form>
		</div>  
	</div>
</div>
<script type="text/javascript">
	<?php if(isset($data['from'])): ?>
		var get = <?= $data['from']; ?>;
	<?php endif; ?>
</script>
<?php // dd($data['get']); ?>
<?php require APPROOT.'/views/inc/footer.php'; ?>