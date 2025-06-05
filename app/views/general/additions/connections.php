<?php
if(isset($data['connections'])):
	// dd($data['connections']);
	foreach($data['connections'] as $connection): ?>
		<?php  
		// dd($connection['items']); 
		?>
		<div class="row mt-3">
			<div class="col-md-12 mx-auto">
				<div class="card card-body bg-white shadow-sm mt-0 overflow-scroll">
					<div class="row js-connections-header">
						<div class="col-md-auto">
							<h4><a href="#" class="js-show-connections"><?= ucfirst($connection['what']); ?></a></h4>
						</div>
						<div class="col-md-auto pt-1">
							<a href="#" class="js-show-connections"><i class="icon icon-circle-e-down-12"></i></a>
						</div>
						<div class="col-md text-end">
							<?php if($data['item']->deleted != 1): ?>
								<a  target="_blank" class="btn btn-primary btm-sm" href="<?= URLROOT; ?>/index/creare/<?= $connection['what']; ?>?from=<?= $data['what']; ?>&id=<?= $data['item']->id; ?>">Adauga</a>
							<?php endif; ?>
						</div>
					</div>
					<div class="row mt-3 js-connections-content">
						<div class="col-md">
							<?php createSimpleIndexTable($connection['items'], $connection['fields'], $connection['what']); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php 
	endforeach;
endif; 
?>