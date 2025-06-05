<div class="modal fade" id="modalPredareRetur" tabindex="-1" aria-labelledby="inregistrareModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="inregistrareModalLabel">Date Predare</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="predareReturForm" enctype="multipart/form-data">
					<div class="mb-3">
						<label for="fileUpload" class="form-label">Imagini Predare/Retur</label>
						<input type="file" class="form-control" id="fileUpload" name="fileUpload[]" multiple>
					</div>
					<div class="mb-3">
						<label for="kilometraj" class="form-label">Kilometraj</label>
						<input type="text" class="form-control" id="kilometraj" name="kilometraj" required>
					</div>
					<div class="mb-3">
					    <label for="combustibil" class="form-label">Combustibil</label>
					    <select class="form-select" id="combustibil" name="combustibil" required>
					        <option value="" disabled selected>Selectează nivelul</option>
					        <option value="1/4">1/4</option>
					        <option value="1/2">1/2</option>
					        <option value="3/4">3/4</option>
					        <option value="1/1">1/1</option>
					    </select>
					</div>

					<div class="mb-3">
						<label for="observatii" class="form-label">Observații</label>
						<textarea class="form-control" id="observatii" name="observatii" rows="3"></textarea>
					</div>
					<!-- Suma de Incasat -->
					<div id="infoSumaIncasata" class="alert alert-info d-flex align-items-center mb-3">
					  <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
					  Se încarcă conversie...
					</div>


					<!-- New Payment Fields -->
					<div class="mb-3">
					    <label for="incasatAmount" class="form-label">Suma încasată</label>
					    <input type="text" class="form-control" id="incasatAmount" name="incasatAmount" placeholder="0">
					</div>
				<!-- 	<div class="mb-3">
					    <label for="incasatCurrency" class="form-label">Monedă</label>
					    <select class="form-select" id="incasatCurrency" name="incasatCurrency">
					        <option value="EURO" selected>EURO</option>
					        <option value="RON">RON</option>
					    </select>
					</div> -->
					<div class="mb-3">
						<label for="marcataPentru" class="form-label">Marchează pentru</label>
						<select class="form-select js-predare-select" id="marcataPentru" name="marcataPentru">
							<option value="">Selectează...</option>
							<?php foreach($data['marcataPentru'] as $item): ?>
								<option value="<?= $item->id; ?>"><?= $item->nume; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="mb-3" style="display:none;" id="extraInfoDiv">
						<label for="extra-info" class="form-label">Informații Suplimentare</label>
						<textarea class="form-control js-predare-extra-info" id="extra-info" name="extra-info" rows="3"></textarea>
					</div>
					<!-- Hidden fields -->
					<input type="hidden" id="inregistrareInchiriereId" name="inchiriereId">
					<input type="hidden" id="inregistrareMasinaId" name="masinaId">
					<input type="hidden" id="inregistrareActionType" name="actionType">
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Închide</button>
				<button type="button" class="btn btn-primary" id="submitPredareRetur">Trimite</button>
			</div>
		</div>
	</div>
</div>