			</div> <!-- end .col -->
		</div> <!-- end .row -->
	</div> <!-- end .container -->
	<!-- JQUERY - https://releases.jquery.com/ -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
	<!-- jQuery Timepicker Plugin JS -->
	<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
	<!-- BOOTSTRAP AND POPPER - https://getbootstrap.com/docs/5.1/getting-started/introduction/ -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
	<!-- BOOTSTRAP TABLE - https://bootstrap-table.com/docs/getting-started/introduction/ -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.4/dist/bootstrap-table.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.4/dist/extensions/filter-control/bootstrap-table-filter-control.min.js"></script>
	<!-- BOOTSTRAP SELECT - https://developer.snapappointments.com/bootstrap-select/  -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
	<!-- PERSONAL JS -->
	<?php if(isset($data['js'])): foreach($data['js'] as $script): ?>
		<script src = "<?=URLROOT;?>/js/<?= $script; ?>.js"></script>
	<?php endforeach; endif;?>
	<script src = "<?=URLROOT;?>/js/main.js"></script>
</body>
</html>