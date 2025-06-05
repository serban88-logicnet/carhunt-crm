<?php require APPROOT . '/views/inc/header.php'; ?>
<div class="row">
	<div class="col-md-6 mx-auto">
		<div class="card card-body bg-white shadow mt-0">
			<?php flash('register_success'); ?>
			<h2>Login</h2>
			<form action="<?php echo URLROOT; ?>/users/login" method="post">
				<div class="mb-3 row">
					<label for="email" class="col-sm-4 col-form-label">Email:<sup>*</sup></label>
					<div class="col-sm-8">
						<input type="text" name="email" class="form-control form-control-lg <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['email']; ?>">
						<span class="invalid-feedback"><?php echo $data['email_err']; ?></span>
					</div>
				</div>    
				<div class="mb-3 row">
					<label for="password" class="col-sm-4 col-form-label">Password:<sup>*</sup></label>
					<div class="col-sm-8">
						<input type="password" name="password" class="form-control form-control-lg <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['password']; ?>">
						<span class="invalid-feedback"><?php echo $data['password_err']; ?></span>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 text-end">
						<input type="submit" class="btn btn-success btn-block" value="Login">
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<?php require APPROOT . '/views/inc/footer.php'; ?>
