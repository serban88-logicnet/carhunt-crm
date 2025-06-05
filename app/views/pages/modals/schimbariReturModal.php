<!-- Modal for updating the return (retur) date/time -->
<div class="modal fade" id="modalUpdateRetur" tabindex="-1" aria-labelledby="modalUpdateReturLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalUpdateReturLabel">Actualizare Data/Ora Retur</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="updateReturForm">
					<div class="mb-3">
						<label for="dataSfarsitNoua" class="form-label">Data Retur Nouă</label>
						<input type="date" class="form-control prevent-past-date" id="dataSfarsitNoua" name="dataSfarsitNoua" required>
					</div>
					<div class="mb-3">
						<label for="oraSfarsitNoua" class="form-label">Ora Retur Nouă</label>
						<input type="text" class="form-control js-custom-timepicker prevent-past-time" id="oraSfarsitNoua" name="oraSfarsitNoua" required>
					</div>
					<!-- Hidden field to hold the booking ID -->
					<input type="hidden" id="inchiriereIdRetur" name="inchiriereId">
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Inchide</button>
				<button type="button" class="btn btn-primary" id="updateReturButton">Actualizează</button>
			</div>
		</div>
	</div>
</div>
