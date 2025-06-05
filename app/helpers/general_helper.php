<?php
	session_start();

	// Flash message helper
	// EXAMPLE - flash('register_success', 'You are now registered', 'alert alert-danger')
	// DISPLAY IN VIEW - echo flash('register_success') 
	function flash($name = '', $message = '', $class = 'alert alert-success') {

		if (!empty($name)) {
			if (!empty($message) && empty($_SESSION[$name])) {
				if (!empty($_SESSION[$name])) {
					unset($_SESSION[$name]);
				}

				if (!empty($_SESSION[$name.'_class'])) {
					unset($_SESSION[$name.'_class']);
				}

				$_SESSION[$name] = $message;
				$_SESSION[$name.'_class'] = $class;
			} elseif (empty($message) && !empty($_SESSION[$name])) {
				$class = !empty($_SESSION[$name.'_class']) ? $_SESSION[$name.'_class'] : '';
				echo '<div class="'.$class.'" id = "msg-flash">'.$_SESSION[$name].'</div>';
				unset($_SESSION[$name]);
				unset($_SESSION[$name.'_class']);
			}
		}

	}

	function dd($variable, $print = 1) {
		echo "<pre>";
			if ($print)
				print_r($variable);
			else
				var_dump($variable);
		echo "</pre>";
		die();
	}

	function myNumberFormat($number) {
		return number_format($number,2,",",".");
	}

	//simple page redirect
	function redirect($page) {
		header('location: '.URLROOT. '/' . $page);
		die();
	}

	function refresh() {
		header("Refresh:0");
	}