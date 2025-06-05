<?php require APPROOT.'/views/inc/header.php'; ?>
<div class="row">
	<div class="col-md-12 mx-auto">
		<div class="card card-body bg-white shadow-sm mt-0">
			<?php flash('notices'); ?>
			<div class="row mb-3">
				<div class="col-md-9">
					<h3><?= $data['title']; ?></h3>
				</div>
			</div>
			<form class="js-edit-form js-edit-form-<?= $data['what']; ?>" action="<?php echo URLROOT;?>/index/editare/<?= $data['what']."/".$data['values']['id']; ?>" method="POST" enctype="multipart/form-data">
				<?php createForm($data['fields'], $data['values'], "edit"); ?>
				<div class="row">
					<div class="col-md">
						<a href="<?= URLROOT; ?>/index/detalii/<?= $data['what']."/".$data['values']['id']; ?>" class="btn btn-dark mt-3">Inapoi</a>
					</div>
					<div class="col-md">
						<input type="submit" value="Salveaza" name="submit" class="btn btn-primary mt-3 float-end">
					</div>
				</div>
			</form>
		</div>  
	</div>
</div>
<?php require APPROOT.'/views/inc/footer.php'; ?>