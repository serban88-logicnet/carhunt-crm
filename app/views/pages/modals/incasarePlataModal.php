<!-- Modal -->
<div class="modal fade" id="incasareModal" tabindex="-1" aria-labelledby="incasareModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="incasareModalLabel">Inregistreaza Incasare</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="incasareForm" method="POST">
					<div class="mb-3">
						<label for="incasareInput" class="form-label">Incasare</label>
						<p>Total platit pana acum: <span id="total-platit-pana-acum">0</span> RON</p>
						<p>Rest Plata: <span id="rest-de-plata">0</span> RON</p>
						<input type="text" class="form-control" id="incasareInput" name="incasare" placeholder="Introdu suma" required>
						<div class="invalid-feedback">
							Te rog sa introduci un numar valid.
						</div>
					</div>
					<button type="submit" class="btn btn-primary">Trimite</button>
				</form>
			</div>
		</div>
	</div>
</div>
