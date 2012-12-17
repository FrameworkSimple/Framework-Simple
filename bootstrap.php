<?php

include ('core/Core.php');
include ('Settings.php');


// if auth is on or session is on
if(AUTH || SESSION)
{

	// turn session on
	session_start();

}

spl_autoload_register('Core::autoloader');

ignore_user_abort(true);

if(DEBUG) {
	ini_set( "display_errors", "1" );
	error_reporting( E_ALL & ~E_NOTICE );
}else {
	error_reporting( 0 );
}

Core::run();
