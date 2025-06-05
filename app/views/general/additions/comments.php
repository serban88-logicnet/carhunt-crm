<?php
if(isset($data['hasComments']) && ($data['hasComments'] == 1)):
	if(!empty($data['comments'])):
?>
	<div class="row mt-3" id="comentarii">
		<div class="col-md-12 mx-auto">
			<div class="card card-body bg-white shadow-sm mt-0">
				<h5 class="mb-0">Comentarii</h5>
<?php
		foreach($data['comments'] as $comment):
?>
			<div class="row border-bottom mt-3">
				<div class="col-md">
					<p class='mb-1'><?= $comment->continut; ?></p>
				</div>
				<div class="w-100"></div>
				<div class="col-md">
					<p class='fs-7'><?= $comment->userInfo->nume; ?> | <?= $comment->created_at; ?></p>
				</div>
			</div>
<?php
		endforeach;
?>
			</div>
		</div>
	</div>
<?php
	endif;  // end if(!empty($data['comments'])):
	if($data['item']->deleted != 1):
?>
	<div class="row mt-3">
		<div class="col-md-12 mx-auto">
			<div class="card card-body bg-white shadow-sm mt-0">
				<form action="<?php echo URLROOT;?>/index/comentarii/adauga/<?= $data['what']."/".$data['item']->id; ?>" method="POST">
					<textarea placeholder="Adauga comentariu..." class="form-control" name="comment" id="comment" rows="3"></textarea>
					<div class="col-md">
						<input type="submit" value="Adauga" name="submit" class="btn btn-primary mt-3 float-end">
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php
	endif; //end if($data['item']->deleted != 1):
endif;  //end if(isset($data['hasComments']) && ($data['hasComments'] == 1)):
?>