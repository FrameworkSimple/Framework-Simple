<?php

include ('core/Core.php');
include ('Settings.php');


// if auth is on or session is on
if(Settings::$auth['on'] || Settings::$session['on'])
{

	// turn session on
	session_start();

}

spl_autoload_register('Core::autoloader');

ignore_user_abort(true);

if(Settings::$debug) {
	ini_set( "display_errors", "1" );
	error_reporting( E_ALL & ~E_NOTICE );
}else {
	error_reporting( 0 );
}

Core::run();
