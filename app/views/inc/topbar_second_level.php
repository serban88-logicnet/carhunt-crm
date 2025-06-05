<div class="row p-2 bg-white m-0 top-bar second-top-bar align-middle shadow-sm position-fixed w-100">
	<div class="col-md-12 text-start">
		<?php if(isLoggedIn()): ?>
			<div class="row g-0">
				<div class="col-md-auto">
					<a href="<?= URLROOT; ?>" class="btn btn-light me-2">
						<i class="icon icon-grid-interface it-2"></i> Dashboard
					</a>
				</div>
				<div class="col-md-auto">
					<a href="#"  class="btn btn-light me-2">
						<i class="icon icon-t-add it-2"></i> Adauga
					</a>
				</div>
				<div class="col-md-auto">
					<a href="javascript:window.location.href=window.location.href" class="btn btn-light me-2">
						<i class="icon icon-reload it-2"></i> Refresh
					</a>
				</div>
			</div>
		<?php else: ?>
		<?php endif; ?>
	</div>
</div>