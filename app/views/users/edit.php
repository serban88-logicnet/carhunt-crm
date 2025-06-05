<?php require APPROOT.'/views/inc/header.php'; ?>
<div class="row">
	<div class="col-md-8 mx-auto">
		<div class="card card-body bg-white shadow mt-0">
			<?php flash('user_notices'); ?>
			<?php // dd($data); ?>
			<h4>Editare date utilizator</h4>
			<p>Modificati campurile necesare.</p>
			<form action="<?php echo URLROOT;?>/users/editare/<?= $data['values']['id']; ?>" method="POST">
				<?php createForm($data['fields'], $data['values']); ?>
				<input type="submit" value="Salveaza" name="submit" class="btn btn-primary mt-3 float-right">
			</form>
		</div>  
	</div>
</div>
<?php require APPROOT.'/views/inc/footer.php'; ?>