<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<!-- BOOTSTRAP - https://getbootstrap.com/docs/5.1/getting-started/introduction/ -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
	<!-- BOOTSTRAP TABLE - https://bootstrap-table.com/docs/getting-started/introduction/ -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.4/dist/bootstrap-table.min.css">
	<!-- BOOTSTRAP SELECT - https://developer.snapappointments.com/bootstrap-select/ -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
	<!-- MY PERSONAL CSS -->
	<link rel="stylesheet" href="<?=URLROOT;?>/css/style.css">
	<link rel="stylesheet" href="<?=URLROOT;?>/css/colors.css">
	<link rel="stylesheet" href="<?=URLROOT;?>/css/spacing.css">
	<!-- NUCLEO ICONS -->
	<link rel="stylesheet" href="<?=URLROOT;?>/css/icons.css">
	<!-- BOOTSTRAP ICONS -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<!-- FONT AWESOME -->
	<script src="https://kit.fontawesome.com/7e0b171775.js" crossorigin="anonymous"></script>
	<!-- jQuery Timepicker Plugin CSS -->
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
	<!-- CHARTIST JS CHARTS - https://gionkunz.github.io/chartist-js/getting-started.html -->
	<link rel="stylesheet" href="//cdn.jsdelivr.net/chartist.js/latest/chartist.min.css">
	<script src="//cdn.jsdelivr.net/chartist.js/latest/chartist.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/chartist-plugin-pointlabels@0.0.6/dist/chartist-plugin-pointlabels.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/chartist-plugin-tooltips@0.0.17/dist/chartist-plugin-tooltip.min.js"></script>
	<script>
	  const REVIZIEOVER = <?php echo REVIZIEOVER; ?>;
	</script>


	<title><?php echo SITENAME; ?></title>
</head>
<body>
	<?php require APPROOT . '/views/inc/sidenav.php'; ?>
	<div class="page-content bg-light" id="content">
		<?php require APPROOT.'/views/inc/topbar.php'; ?>
		<div class="row p-5 m-0">
			<div class="col-md-12 p-0">
				<!-- Toggle button -->

