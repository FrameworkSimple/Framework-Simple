<?php

include ('core/Core.php');
include ('Settings.php');

spl_autoload_register('Core::autoloader');

ignore_user_abort(true);

if(Settings::$debug) {
	ini_set( "display_errors", "1" );
	error_reporting( E_ALL & ~E_NOTICE );
}else {
	error_reporting( 0 );
}

Core::run();
