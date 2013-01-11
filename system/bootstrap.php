<?php

// start measuring time
$startTime = microtime(true);

include ('core/Core.php');
include ('Settings.php');

define("SYSTEM_PATH", __DIR__);

// if auth is on or session is on
if(AUTH || SESSION)
{

	// turn session on
	session_start();

}

spl_autoload_register('Core::autoloader');

ignore_user_abort(true);


if(DEBUG) {
	ini_set( "display_errors", 1 );
	error_reporting( -1);
}else {
	error_reporting( 0 );
}

Core::run();

// if debug is on
if(DEBUG)
{

	// see how much time has passed
	echo "Time:  " . number_format(( microtime(true) - $startTime), 4) . " Seconds\n";

}
