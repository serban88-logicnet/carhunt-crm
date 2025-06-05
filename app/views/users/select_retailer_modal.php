<div class="modal fade" id="retailer-modal" tabindex="-1" role="dialog" aria-labelledby="retailer-modal" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="retailer-modal">Alege Retailer pentru a conecta</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form method="POST" action="<?= URLROOT; ?>/users/connect/<?= $data['user']->id; ?>">
					<div class="form-group">
						<select class="form-control" name="retailer">
							<?php foreach($data['retaileri'] as $retailer): ?>
								<option value = "<?= $retailer->id; ?>"><?= $retailer->name; ?></option>
							<?php endforeach; ?>
						</select>
						<input type="submit" name="submit" value="Adauga" class="btn btn-primary mt-2">
					</div>
				</form>
			</div>
		</div>
	</div>
</div>