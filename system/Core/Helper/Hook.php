<?php
/**
 * Handles everything to do with hooks
 */

/**
 * This handles the ablitiy to call and register hooks.
 *
 * You can do this before certain methods in the framework.
 *
 *
 * @category   Helpers
 * @package    Core
 * @subpackage Helpers
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */

Class Core_Helper_Hook {

	/**
	 * Hooks: array
	 *
	 * an array of all the hooks that have been registered
	 *
	 * @var array
	 */
	private static $hooks = array();

	/**
	 * Call a hook at a specific point in the process.
	 *
	 * This will call all the methods that were registered earlier
	 *
	 * @param  string $hook the name of the hook to call
	 * @param  array  $args any arguements you want to be passed to the methods
	 * @return boolean      if the framework should continue running
	 */
	public static function call($hook, $args = array())
	{

		// if the hook is in the hooks array
		if(isset(self::$hooks[$hook])) {

			$return;

			$hook = array_reverse(self::$hooks[$hook]);

			// loop through the hook and get the function information
			foreach($hook as $fun) {

				// create a new instance of the class
				$class = Core::instantiate($fun[0]);

				// call the function inside the class with the arguments
				$return = call_user_func_array(array($class,$fun[1]),$args);

			}

			return $return;
		}

	}

	/**
	 * Register a hook to be called when the hook is called
	 * @api
	 * @param  string $hook     name of the hook you want to be linked to
	 * @param  string $function the method you want called when hook is called
	 */
	public static function register($hook,$function)
	{

		// if the hook is NOT already in the hooks array
		if(!isset(self::$hooks[$hook]))
		{

			// create a new array for this hook
			self::$hooks[$hook] = array();

		}

		// if the function is an array
		if(is_array($function)) {

			// push the new function into the hooks array
			array_push(self::$hooks[$hook],$function);

		}
		// if the function is just a string
		else {

			$backtrace = debug_backtrace();

			// get the name of the file that called this file
			$file = str_replace('.php','',str_replace(SYSTEM_PATH."core/",'',$backtrace[0]['file']));


			// push the new function into the hooks array
			array_push(self::$hooks[$hook],array($file,$function));
		}

	}

}