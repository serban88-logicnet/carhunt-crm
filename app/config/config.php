<?php

// Load .env variables
$envPath = dirname(__DIR__, 2) . '/.env';  // go up two levels
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val);
    }
}


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// DB Params
define("DB_HOST", $_ENV['DB_HOST']);
define("DB_USER", $_ENV['DB_USER']);
define("DB_PASS", $_ENV['DB_PASS']);
define("DB_NAME", $_ENV['DB_NAME']);

// App Root
define('APPROOT', dirname(dirname(__FILE__)));
define('HOMEROOT', dirname(dirname(dirname(__FILE__))));

// URL Root
define('URLROOT', $_ENV['URLROOT']);

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

