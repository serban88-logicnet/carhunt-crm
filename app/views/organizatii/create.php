<?php require APPROOT.'/views/inc/header.php'; ?>
<div class="row">
	<div class="col-md-12 mx-auto">
		<div class="card card-body bg-white shadow mt-0">
			<?php flash('notices'); ?>
			<h4><?= $data['title'] ?></h4>
			<form action="<?php echo URLROOT;?>/organizatii/creare/" method="POST">
				<?php createForm($data['fields'], $data['values']); ?>
				<input type="submit" value="Salveaza" name="submit" class="btn btn-primary mt-3 float-right">
			</form>
		</div>  
	</div>
</div>
<?php require APPROOT.'/views/inc/footer.php'; ?>