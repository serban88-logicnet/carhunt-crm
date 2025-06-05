<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


  // DB Params
define("DB_HOST", "localhost");
define("DB_USER", "carhunt");
define("DB_PASS", "5wqMv79jzmOiMLLv");
define("DB_NAME", "carhunt");

  // App Root
define ('APPROOT', dirname(dirname(__FILE__)));
define ('HOMEROOT', dirname(dirname(dirname(__FILE__))));
  // URL Root
define('URLROOT', 'https://carhunt.logicnet.ro');
  // Site Name
define('SITENAME', 'CarHunt CRM');

//overlap time allowed for 2 bookings
define('OVERLAPTIME', 4);
define('REVIZIEOVER', 20000);

define("tabeleCuAssigned",array("organizatii","contracte","sarcini","intalniri","leaduri"));
define("tabeleCuConexiuni",array(
	array("parent"=>"organizatii","parentSg"=>"organizatie","child"=>"echipamente"),
	array("parent"=>"organizatii","parentSg"=>"organizatie","child"=>"contacte"),
	array("parent"=>"organizatii","parentSg"=>"organizatie","child"=>"cazuri")
));

//tabele care au coloana speciala service_assigned
//nota - acestea sunt goale pentru ca pana la urma s-a dorit ca cei de la service sa vada totul
define("tabeleCuAssignedService",array());  //
define("tabeleCuConexiuniService",array(
	// array("parent"=>"organizatii","parentSg"=>"organizatie","child"=>"echipamente"),
//	array("parent"=>"organizatii","parentSg"=>"organizatie","child"=>"contacte"),
//	array("parent"=>"organizatii","parentSg"=>"organizatie","child"=>"cazuri")
));

// Other Globals

