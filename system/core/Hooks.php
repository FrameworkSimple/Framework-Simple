<?php

Class Hooks {

	// hold all the hooks in here
	private static $hooks = array();

	// call a hook
	public static function call($hook)
	{

		// if the hook is in the hooks array
		if(isset(self::$hooks[$hook])) {

			$return;

			$hook = array_reverse(self::$hooks[$hook]);

			// loop through the hook and get the function information
			foreach($hook as $fun) {

				// create a new instance of the class
				$class = Core::instantiate($fun[0]);

				// create a blank array of arguments
				$args = array();

				// if there are more then one argument
				if(func_num_args() >= 2)
				{

					// remove the first argument
					$args = array_slice(func_get_args(),1);

					// loop through the array of arguments and set them equal to their reference
					foreach($args as $k => &$arg){

						$args[$k] = &$arg;

					}

				}

				// call the function inside the class with the arguments
				$return = call_user_func_array(array($class,$fun[1]),$args);

			}

			return $return;
		}

	}

	// register a new function for a hook
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

			// get the name of the file that called this file
			$file = str_replace('.php','',str_replace(SYSTEM_PATH."/core/",'',debug_backtrace()[0]['file']));


			// push the new function into the hooks array
			array_push(self::$hooks[$hook],array($file,$function));
		}

	}

}