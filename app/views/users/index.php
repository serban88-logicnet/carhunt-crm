<?php require APPROOT.'/views/inc/header.php'; ?>
<div class="row">
	<div class="card card-body bg-white shadow mt-0">
		<div class="row">
			<div class="col-md">
				<h3 class="mb-4"><?= $data['title']; ?></h3>
			</div>
			<div class="col-md">
				<?php flash('user_notices'); ?>
			</div>
		</div>
		<table class = "table table-striped">
			<thead class="thead-dark">
				<tr>
					<th>Nume</th>
					<th>CUI</th>
					<th>Email</th>
					<th>Tip Cont</th>
					<th>Data Creere</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($data['users'] as $user): ?>
					<tr class="<?= ($user->type == 3)?"":""; ?> <?= ($user->type == 4)?"":""; ?>" >
						<td><a href="<?= URLROOT; ?>/users/show/<?= $user->id ?>"><?= $user->name ?></a></td>
						<td><?= $user->cui ?></td>
						<td><?= $user->email ?></td>
						<td><?= writeType($user->type) ?></td>
						<td><?= $user->created_at ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>  
</div>
<?php require APPROOT.'/views/inc/footer.php'; ?>