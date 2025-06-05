<div class="row">
	<div class="card card-body bg-white shadow mt-4">
		<h4 class="mb-4">
			<?= ($user->type == 3)?"Furnizori":""; ?>
			<?= ($user->type == 4)?"Retaileri":""; ?>
		</h4>
		<table class = "table table-striped">
			<thead class="thead-dark">
				<tr>
					<th>Nume</th>
					<th>CUI</th>
					<th>Email</th>
					<?= ($user->type == 3)?"<th>Comision (%)</th>":""; ?>
					<?= ($user->type == 4)?"<th>Limita Totala (RON)</th>":""; ?>
					<th>Operatiuni</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($user->connections as $connection): ?>
				<tr>
					<td><a href="<?= URLROOT; ?>/users/show/<?= $connection->originalId; ?>" ><?= $connection->name ?></a></td>
					<td><?= $connection->cui ?></td>
					<td><?= $connection->email ?></td>
					<?= ($user->type == 3)?"<td>".$connection->comision_furnizor."</td><td><a href='".URLROOT."/users/unlink/".$user->id."/".$connection->originalId."/retailer'>Sterge Legatura</a></td>":""; ?>
					<?= ($user->type == 4)?"<td>".myNumberFormat($connection->limita_retailer)."</td><td><a href='".URLROOT."/users/unlink/".$connection->originalId."/".$user->id."/furnizor'>Sterge Legatura</a></td>":""; ?>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>  
</div>