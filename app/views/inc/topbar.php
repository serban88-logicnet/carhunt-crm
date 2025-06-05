 <div class="row p-2 bg-white m-0 top-bar">
 	<div class="col-md-6 text-start">
 		<?php if(isLoggedIn()): ?>
 			<div class="row g-0">
 				<div class="col-md-auto pe-2">
 					<button id="sidebarCollapse" type="button" class="btn p-1"><i class="fa fa-bars"></i><small class="text-uppercase font-weight-bold"></small></button>
 				</div>
 				<div class="col-md-auto">
 					<a href="<?= URLROOT; ?>" class="btn btn-light me-2">
 						<i class="bi bi-grid-3x3-gap-fill"></i> Dashboard
 					</a>
 				</div>
 				<?php if(isset($data['what'])): ?>
 					<div class="col-md-auto">
 						<a href="<?= URLROOT; ?>/index/creare/<?= $data['what']; ?>"  class="btn btn-light me-2">
 							<i class="bi bi-plus-circle"></i> Adauga
 						</a>
 					</div>
 				<?php endif; ?>
 				<div class="col-md-auto">
 					<a href="javascript:window.location.href=window.location.href" class="btn btn-light me-2">
 						<i class="bi bi-arrow-repeat"></i> Refresh
 					</a>
 				</div>
 				<?php if(isset($data['what'])): ?>
 					<div class="col-md-auto">
 						<a href="<?= URLROOT; ?>/index/lista/<?= $data['what']; ?>" class="btn btn-secondary me-2">
 							<i class="bi bi-arrow-clockwise"></i> Resetare Filtre
 						</a>
 					</div>
 				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="col-md-6 text-end">
 			<?php if(isLoggedIn()): ?>
           <!--  <a href="#" class="btn btn-light me-2">
               <i class="fas fa-bell fa-fw"></i>
           </a> --> 
           <a href="<?= URLROOT; ?>/index/editare/utilizatori/<?= $_SESSION['user_id']; ?>" class="btn btn-light me-2">
           	<i class="bi bi-person-circle"></i> Salut <strong><?= getUserName(); ?>!</strong>
           </a>
           <a href='<?= URLROOT; ?>/users/logout' class="btn btn-secondary">
           	<i class="bi bi-box-arrow-left"></i> Log Out
           </a>
       <?php else: ?>
       	<a href='<?= URLROOT; ?>/users/login' class="btn btn-secondary ms-4">
       		<i class="bi bi-box-arrow-right"></i> Log In
       	</a>
       <?php endif; ?>
   </div>
</div>
