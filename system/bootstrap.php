<?php
/**
 * Sets up and runs the framework
 * @category   Core
 * @package    Core
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */

/**
 * start_time: timestamp
 *
 * the time that the script started running
 */
define("START_TIME", microtime(true));

include ('core/Core.php');
include ('Settings-Application.php');
include ('Settings-Environment.php');


/**
 * system_path: string
 *
 * the path to the system
 */
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
