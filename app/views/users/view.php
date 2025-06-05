<?php require APPROOT.'/views/inc/header.php'; ?>
<div class="row">
	<div class="card card-body bg-white shadow mt-0">
		<?php $user = $data['user']; ?>
		<?php flash('user_notices'); ?>
		<div class="row">
			<div class="col-md-6">
				<h3 class="mb-4"><?= $user->name; ?></h3>
			</div>
			<div class="col-md-6 text-end">
				<?php if($user->type == 4): ?>
					<a href="#" class="btn btn-primary js-select-retailer">Conecteaza cu Retailer</a>
				<?php endif; ?>
				<a href="<?= URLROOT; ?>/users/editare/<?= $user->id; ?>" class="btn btn-primary">Editeaza</a>
				<!-- <a href="<?= URLROOT; ?>/users/parola/reset/<?= $user->id; ?>" class="btn btn-primary">Reseteaza Parola</a> -->
			</div>
		</div>
		
		<table class = "table table-striped">
			<thead class="thead-dark">
				<tr>
					<th>Nume</th>
					<th>CUI</th>
					<th>Email</th>
					<th>Tip Cont</th>
					<?= ($user->type == 3)?"<th>Limita (RON)</th><th>Limita Efectiva</th>":""; ?>
					<?= ($user->type == 4)?"<th>Comision (%)</th>":""; ?>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?= $user->name ?></a></td>
					<td><?= $user->cui ?></td>
					<td><?= $user->email ?></td>
					<td><?= writeType($user->type) ?></td>
					<?= ($user->type == 3)?"<td>".myNumberFormat($user->limita_retailer)."</td><td>".myNumberFormat($user->limita_retailer-$user->sumaFacturi)."</td>":""; ?>
					<?= ($user->type == 4)?"<td>".$user->comision_furnizor."</td>":""; ?>
				</tr>
			</tbody>
		</table>
	</div> 
</div>
<?php require APPROOT.'/views/users/view_extras/user_connections.php'; ?>
<?php require APPROOT.'/views/users/view_extras/user_facturi.php'; ?>
<?php require APPROOT.'/views/users/select_retailer_modal.php'; ?>
<?php require APPROOT.'/views/inc/footer.php'; ?>