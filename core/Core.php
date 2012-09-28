<?php

Class Core {
	// holds all the classes that have been instatiated
	private static $instantiated = array();

	// variable for all the debug information
	private static $debug = array(
									"statements"=>array(),
									"instantiated" =>array()
								);

	// loads all the classes automaticly
	public static function autoloader($classname)
	{

		// framework specific files
		$files = array(
			"core" =>array(
				"Core"=>"Core.php",
				"Controller"=>"Controller.php",
				"CFDump"=>"CFDump.php",
				"FormHelper"=>"FormHelper.php",
				"Model"=>"Model.php",
				"ORM"=>"ORM.php",
				"Validation"=>"Validation.php",
				"Authorization"=>"Authorization.php",
				"Asset"=>"Asset.php",
				"Database"=>"Database.php")
		);

		// includes framework specific files
		foreach($files as $folder=>$file) {
			foreach($file as $name=>$filePath) {
				if($classname == $name) {
					include_once '../'.$folder."/".$filePath;
					return ;
				}
			}
		}

		// if not in the framework list and includes the word controller instantiate a controller file
		if(strstr($classname,"Controller")) {
			if(strstr($classname,"Test")) {
				include "../tests/".$classname.".php";
				return;
			}else {
				include "../controller/".$classname.".php";
				return;
			}

		}

		// else instantiate a model
		else {
			include "../model/".$classname.".php";
			return;
		}

	}

	// get all the url information
	public static function getURL()
	{

		// the method that was used to make the call
		$method = strtolower($_SERVER['REQUEST_METHOD']);

		// the url that was called
		$url = $_SERVER["REQUEST_URI"];

		// remove leading slash
		$url = substr($url, 1);

		// split on the the question mark if there is one
		// removes the get variables
		$url = preg_split("/[?]/", $url);

		// put into an array all the pieces
		$url = explode("/",$url[0]);

		// get the base directory and put into an array
		$baseDir = preg_split("[/]", Asset::get_base());

		// variable for the request that was made
		$request = array();

		// compare the base and the url
		foreach($url as $string)
		{

			// check if the string is in the base, if not
			if(!in_array($string,$baseDir))
			{

				// push the string in the request
				array_push($request, $string);

			}
		}

		// variable for all the information of the url
		$info_of_url = array();

		// check if it is the root url
		// url : /
		if(count($request) === 0) {

			// set the controller to the default
			$info_of_url['controller'] = Settings::$defaultController.'Controller';

			// set the action to the default
			$info_of_url['action'] = Settings::$defaultAction;

		}
		// check if controller exists
		if(is_file(Settings::$pathToApp."controller/".ucfirst($request[0])."Controller.php"))
		{

			// set the controller
			$info_of_url['controller'] = ucfirst($request[0]).'Controller';

			// if there is an second value
			if (isset($request[1]))
			{

				// check if the action exists, if it does
				if(method_exists($info_of_url['controller'], $request[1]))
				{
					// set the action
					// url: /controller/action
					$info_of_url['action'] = $request[1];

					// if there is a third value
					if(isset($request[2]))
					{

						// set the params
						// url: /controller/action/param
						$info_of_url['params'] = $request[2];

					}

				}

				// if the second argument is numeric
				// url: /controller/param
				else if(is_numeric($request[1]))
				{

					// if rest is turned on and method is a method inside controller
					// url: /controller/param with request
					if(Settings::$rest && method_exists($info_of_url['controller'], $method))
					{

						// set the action to the method
						$info_of_url['action'] = $method;

					}

					// if rest isn't on and default action is a method
					// url: /controller/param without request
					else if(method_exists($info_of_url['controller'], Settings::$defaultAction))
					{

						// set the action to the default
						$info_of_url['action'] = Settings::$defaultAction;

					}


					// set the params to the  second value
					$info_of_url['params'] = $request[1];

				}

			}

			// if there is no second value
			else
			{

				// if rest is turned on and method is a method inside controller
				// url: /controller with request
				if(Settings::$rest && method_exists($info_of_url['controller'], $method))
				{

					// set the action to the method
					$info_of_url['action'] = $method;

				}

				// if rest isn't on and default action is a method
				// url: /controller without request
				else if(method_exists($info_of_url['controller'], Settings::$defaultAction))
				{

					// set the action to the default
					$info_of_url['action'] = Settings::$defaultAction;

				}

			}

		}

		// return the information
		return $info_of_url;

	}

	// create a new class
	public static function instantiate($classname)
	{
		// if it has already been instantiated
		if(isset(self::$instantiated[$classname]))
		{

			//return that one
			return self::$instantiated[$classname];

		}

		// if it hasn't been instantiated
		else
		{

			// push the name into array for debugging
			array_push(self::$debug['instantiated'],$classname);

			// instatiate it and put it in the array and then return it
			return self::$instantiated[$classname] = new $classname;
		}

	}


	// redirect to pages
	public static function redirect($var)
	{

		// TODO: CREATE FUNCTION
	}
	// run the function
	public static function run()
	{

		// start the session variables
		self::instantiate("Authorization");

		// get all the information
		$info_of_url = self::getURL();

		// only do this if there is a controller and an action
		if(isset($info_of_url['controller']) && isset($info_of_url['action']))
		{
			// create the controller
			$controller = self::instantiate($info_of_url['controller']);

			// set up the request on the controller for later use
			$controller->request = array(
											"GET" => $_GET,
											"POST" => $_POST,
											"SERVER" => $_SERVER,
											"TYPE" => $_SERVER['REQUEST_METHOD'],
											"AJAX" => !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
										);

			// if rest is on and the request type was json
			if(Settings::$rest && $controller->request['SERVER']['CONTENT_TYPE'] === "application/json")
			{

				// set the request type's data to the php input stream
				$controller->reqest[$controller->request['TYPE']] = json_decode(file_get_contents("php://input"));

			}
			//TODO: Add XML and other formart support

			// call the before action method
			$controller->beforeAction();

			// set the view
			$controller::$viewname = $info_of_url['action'];

			// set the template if one is not set already
			$controller::$template = empty($controller::$template)?Settings::$defaultTemplate:$controller::$template;

			// if there are params
			if(isset($info_of_url['params']))
			{
				// pass them to the action
				$controller->$info_of_url['action'](implode(",", $data['params']));

			}

			// else pass any information that came through the request
			else
			{

				$controller->$info_of_url['action']($controller->request[$controller->request['TYPE']]);

			}

			// run the after action method
			$controller->afterAction();

			// if it was an ajax request and the return type was json
			if($controller->request['AJAX'] && Settings::$ajaxReturnType === 'json')
			{

				// encode all the view date
				$json = json_encode($info::$view_info);

				// print out the json
				echo $json;

				// TODO: Add more return types
			}
			// if the request is not ajax
			else
			{
				// set the root variable for use within views
				$root = Asset::get_base();

				// name of the controller
				$controller_name = strtolower(str_replace("Controller", "", $info_of_url['controller']));

				// path to view
				$path_to_view = Settings::$pathToApp."view/$controller_name/{$controller::$viewname}.php";

				// render out the view and set it equal to to content_for_layout
				$content_for_layout = self::_get_contents($path_to_view,$controller::$view_info,$root);

				// check if templates are being used
				if(Settings::$templates)
				{

					// set the template path
					$path_to_template = Settings::$pathToApp."view/templates/{$controller::$template}.php";
				}
				// if templating is not on
				else {

					// render out default framework template
					$path_to_template = Settings::$pathToApp."core/Template.php";
				}

				// render out the template with the view content
				echo self::_get_contents($path_to_template,$controller::$layout_info,$root,$content_for_layout);

				// if debug is on
				if(Settings::$debug)
				{
					// render the debug stylesheet
					echo "<style type='text/css'>".self::_get_contents(Settings::$pathToApp."core/debug.css")."</style>";

					// create div to hold information
					echo "<div id='debuger'>";

					// loop through all the different key values in debug
					foreach(self::$debug as $title=>$info)
					{

						// set the key (title) to an h2
						echo "<h2>".$title."</h2>";

						// loop through the value (info)
						foreach ($info as $num => $para)
						{

							// echo out the index number and the value
							echo "<p><span>".$num."</span>".$para."</p>";

						}

					}

					// close the div
					echo "</div>";

				}
			}


		}

		// if there isnt' a controller or action doing the following
		else
		{

			// ERROR HANDLING HERE

		}

	}

	// get the contents of a file
	private static function _get_contents($filename, $data=NULL,$root=NULL, $content_for_layout=NULL) {
	    if (is_file($filename)) {
	        ob_start();
	        include $filename;
	        return ob_get_clean();
	    }
	    return false;
	}

	// split on caps, add underscores and then convert it to lowercase
	static function toDB($string){

		$string = preg_replace('/\B([A-Z])/', '_$1', $string);
    	return strtolower($string);
	}

	// replace underscores with spaces and caplize first letter
	static function toNorm($string) {
		$string = str_replace("_", " ", $string);
		return ucfirst($string);
	}

	// find the underscores and convert the following letter to and uppercase
	static function toCam($string) {
		$func = create_function('$c', 'return strtoupper($c[1]);');
    	$string = preg_replace_callback('/_([a-z])/', $func, $string);
		return ucfirst($string);
	}

}