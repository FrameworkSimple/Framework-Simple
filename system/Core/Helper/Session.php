<?php
/**
 * Handles everything to do with the session
 */

/**
 * Allows you to handle things within the session
 *
 * @category   Helpers
 * @package    Core
 * @subpackage Helpers
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
class Core_Helper_Session {

	/**
	 * set a session variable
	 * @api
	 * @param string $key   the key to set it to
	 * @param object $value the object you want it set to
	 */
	public static function set($key,$value)
	{

		// set a session variable
		$_SESSION[$key] = $value;

	}

	/**
	 * get a session variable
	 * @api
	 * @param  string $key the key you want to get from the session
	 * @return object      the object that was set to that key or false if key does not exist
	 */
	public static function get($key)
	{

		// get a session variable
		return isset($_SESSION[$key])?$_SESSION[$key]:false;
	}

	/**
	 * Remove a key from the sesssion
	 * @api
	 * @param  string $key the key you want to remove
	 */
	public static function remove($key)
	{

		// unset session variable
		unset($_SESSION[$key]);

	}

	/**
	 * Check if the key is in the sesssion
	 * @api
	 * @param  string $key the key you want to check on
	 */
	public static function check($key)
	{

		// isset session variable
		return isset($_SESSION[$key]);
	}
}