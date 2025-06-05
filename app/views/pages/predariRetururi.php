<?php require APPROOT . '/views/inc/header.php'; ?>
<div class="row">
	<?php flash('notices'); ?>
	<?php
	$predari = $data['predari']; 
	$retururi = $data['retururi'];


	?>
	<div class="col-md-12">
		<div class="card card-body bg-white shadow mb-4">
			 <!-- Search form -->
            <form method="POST" class="d-flex">
                <input 
                    type="text" 
                    name="carNr" 
                    class="form-control me-2" 
                    placeholder="Caută după Numar"
                    required
                >
                <button type="submit" class="btn btn-primary">Caută</button>
                <a href="<?= URLROOT; ?>/pages/predari-retururi/" class="btn btn-secondary ms-2">Reset</a>
            </form>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card card-body bg-white shadow mt-0">
			<h4 class="mb-0">Predari</h4>
			<div class="row">
				<?php
					foreach($predari as $predare):
				?>
						<div class="col-md-12">
							<div class="card mt-2">
								<div class="card-body">
									<p class="mb-0"><strong>Client:</strong> <?= $predare->nume_client; ?></p>
									<p class="mb-0"><strong>Sosire:</strong> <?= $predare->data_inceput; ?> (<?= $predare->ora_inceput; ?>)</p>
									<p class="mb-2"><strong>Masina:</strong> <?= $predare->license; ?> (Km: <?= $predare->km_actuali; ?>)</p>
									<a href="#" data-km-actuali="<?= $predare->km_actuali; ?>" data-masina-id="<?= $predare->masina_id ?>" data-inchiriere-id="<?= $predare->id; ?>" class="btn btn-secondary btn-sm js-schimbari-predare">Schimba Auto/Date</a>
									<a href="#" data-rest-plata="<?= $predare->restPlata ?>" data-km-actuali="<?= $predare->km_actuali; ?>" data-masina-id="<?= $predare->masina_id ?>" data-inchiriere-id="<?= $predare->id; ?>" class="btn btn-success btn-sm js-record-predare">Inregistreaza</a>
									<a target="_blank" href="<?= URLROOT ?>/index/pdf-contract/<?= $predare->id; ?>/inchiriere" class="btn btn-primary btn-sm">Contract</a>
									<a href="#" class="btn btn-warning btn-sm js-muta-in-buffer" data-inchiriere-id="<?= $predare->id; ?>">Buffer</a>

								</div>
							</div>
						</div>
				<?php
					endforeach;
				?>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card card-body bg-white shadow mt-0">
			<h4 class="mb-0">Retururi</h4>
			<div class="row">
				<?php
					foreach($retururi as $retur):
						if($retur->platit == "") {
							$retur->platit = 0;
						}
						
						// dd($restPlata);

				?>
						<div class="col-md-12">
							<div class="card mt-2">
								<div class="card-body">
									<p class="mb-0"><strong>Client:</strong> <?= $retur->nume_client; ?></p>
									<p class="mb-0"><strong>Sosire:</strong> <?= $retur->data_sfarsit; ?> (<?= $retur->ora_sfarsit; ?>)</p>
									<p class="mb-0"><strong>Masina:</strong> <?= $retur->license; ?> (Km: <?= $retur->km_actuali; ?>)</p>
									<p class="mb-0"><strong>Rest Plata:</strong> <?= number_format($retur->restPlata, 0) ?> Euro</p>
									<p class="mb-2"><strong>Observatii Predare:</strong> <?= $retur->observatii_predare; ?></p>
									<a href="#" data-km-actuali="<?= $retur->km_actuali; ?>" data-masina-id="<?= $retur->masina_id ?>" data-inchiriere-id="<?= $retur->id; ?>" class="btn btn-secondary btn-sm js-schimbari-retur">Schimba Date</a>
									<a href="#" data-rest-plata="<?= $retur->restPlata ?>" data-km-actuali="<?= $retur->km_actuali; ?>" data-masina-id="<?= $retur->masina_id ?>" data-inchiriere-id="<?= $retur->id; ?>" class="btn btn-success btn-sm js-record-retur">Inregistreaza</a>
									<a target="_blank" href="<?= URLROOT ?>/index/pdf-contract/<?= $retur->id; ?>/inchiriere" class="btn btn-primary btn-sm">Contract</a>
									<a href="#" class="btn btn-warning btn-sm js-muta-in-buffer" data-inchiriere-id="<?= $predare->id; ?>">Buffer</a>

								</div>
							</div>
						</div>
				<?php
					endforeach;
				?>
			</div>
		</div>
	</div>
	<div class="col-md-4">
	    <div class="card card-body bg-white shadow mt-0">
	        <h4 class="mb-0">Buffer</h4>
	        <div class="row">
	            <?php foreach($data['buffer'] as $buf): ?>
	                <div class="col-md-12">
	                    <div class="card mt-2">
	                        <div class="card-body">
	                            <p class="mb-0"><strong>Client:</strong> <?= $buf->nume_client; ?></p>
	                            <p class="mb-0"><strong>Masina:</strong> <?= $buf->license; ?> (Km: <?= $buf->km_actuali; ?>)</p>
	                            <!-- Remove from buffer button: -->
	                            <div class="input-group mb-2">
	                                <select class="form-select js-buffer-destinatie" style="max-width:120px;">
	                                    <option value="20">Predare</option>
	                                    <option value="21">Retur</option>
	                                </select>
	                                <button class="btn btn-danger btn-sm js-scoate-din-buffer" 
	                                    data-inchiriere-id="<?= $buf->id; ?>">
	                                    Scoate din Buffer
	                                </button>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            <?php endforeach; ?>
	        </div>
	    </div>
	</div>

</div>
<?php require APPROOT . '/views/pages/modals/schimbariPredareModal.php'; ?>
<?php require APPROOT . '/views/pages/modals/schimbariReturModal.php'; ?>
<?php require APPROOT . '/views/pages/modals/retururiModal.php'; ?>
<?php require APPROOT . '/views/inc/footer.php'; ?>