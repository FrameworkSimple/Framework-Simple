<?php

Class Core {
	// holds all the classes that have been instantiated
	private static $instantiated = array();

	// variable for all the debug information
	public static $debug = array(	"statements"   =>array(),
									"instantiated" =>array(),
									"url"          =>array(),
									"views"        =>array()
								);

	private static $files = array(
			"core" =>array(
				"Core"       =>"Core.php",
				"Controller" =>"Controller.php",
				"CFDump"     =>"CFDump.php",
				"FormHelper" =>"FormHelper.php",
				"Model"      =>"Model.php",
				"ORM"        =>"ORM.php",
				"Validation" =>"Validation.php",
				"Auth"       =>"Auth.php",
				"Asset"      =>"Asset.php",
				"Database"   =>"Database.php",
				"View"       =>"View.php",
				"Session"    =>"Session.php",
				"Hooks"		 =>"Hooks.php"),
			"extensions" => array()
		);
	// what extensions to include
	public static $extensions = array();

	// auto routes
	public static $routes = array();

	//info of url
	public static $info_of_url = array("controller"=>"","action"=>"","params"=>array(),"ext"=>"");

	// loads all the classes automatically
	public static function autoloader($classname)
	{

		// includes framework specific files
		foreach(self::$files as $folder=>$file) {
			foreach($file as $name=>$filePath) {
				if($classname == $name) {
					include_once SYSTEM_PATH."/".$folder."/".$filePath;
					return ;
				}
			}
		}

		// if not in the framework list and includes the word controller instantiate a controller file
		if(strstr($classname,"Controller")) {
			if(strstr($classname,"Test")) {
				include SYSTEM_PATH."/tests/".$classname.".php";
				return;
			}else {
				include SYSTEM_PATH."/controllers/".$classname.".php";
				return;
			}

		}

		// else instantiate a model
		else {
			include SYSTEM_PATH."/models/".$classname.".php";
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

		// split on the the question mark if there is one
		// removes the get variables
		$url = preg_split("/[?]/", $url);

		// split on the period
		// get the extension
		$url = preg_split("/[.]/", $url[0]);

		// set the extension to the second half of the split so that we can use it later
		$extension = isset($url[1])?$url[1]:'';

		// variable for the request that was made
		$uri = str_replace(dirname($_SERVER['SCRIPT_NAME'])."/",'',$url[0]);

		// if the uri is just a blank string use an array if it has length then break it into pieces
		$request = !empty($uri)?explode("/", $uri):array();

		// check if it is the root url
		// url : /
		if(count($request) === 0) {

			// if the routes has the root in it
			if(isset(Core::$routes['/'])) {

				// set the controller to the one in the route
				self::$info_of_url['controller'] = Core::$routes['/'][0].'Controller';

				// set the action to the one in the route
				self::$info_of_url['action'] = Core::$routes['/'][1];

				// if there are params
				if(isset(Core::$routes['/'][2]))self::$info_of_url['params'] = Core::$routes['/'][2];

			}
			else {

				// set the controller to the default
				self::$info_of_url['controller'] = ucfirst(DEFAULT_CONTROLLER).'Controller';

				// set the action to the default
				self::$info_of_url['action'] = DEFAULT_ACTION;

			}

			return;

		}
		// if the uri is not in the routes
		else if(!self::_check_routes($request, $method))
		{

			// set the controller
			self::$info_of_url['controller'] = ucfirst($request[0]).'Controller';

			// if there is an extension
			if($extension)

			{
				self::$info_of_url['ext'] = $extension;
			}

			// if there is an second value
			if (isset($request[1]))
			{

				// check if the action exists, if it does
				if(method_exists(self::$info_of_url['controller'], $request[1]))
				{
					// set the action
					// url: /controller/action
					self::$info_of_url['action'] = $request[1];

					// if there is a third value
					if(isset($request[2]))
					{

						unset($request[0]);
						unset($request[1]);

						// set the params
						// url: /controller/action/param
						self::$info_of_url['params'] = $request;

					}

				}

				// if the second argument is numeric
				// url: /controller/param
				else if(is_numeric($request[1]))
				{

					// if rest is turned on and method is a method inside controller
					// url: /controller/param with request
					if(REST && method_exists(self::$info_of_url['controller'], $method))
					{

						// set the action to the method
						self::$info_of_url['action'] = $method;

					}

					// if rest isn't on and default action is a method
					// url: /controller/param without request
					else if(method_exists(self::$info_of_url['controller'], DEFAULT_ACTION))
					{

						// set the action to the default
						self::$info_of_url['action'] = DEFAULT_ACTION;
					}

					unset($request[0]);

					// set the params
					// url: /controller/param
					self::$info_of_url['params'] = $request;

				}

			}

			// if there is no second value
			else
			{

				self::_set_action($method);
				unset($request[0]);
				// set the params to the  second value
				self::$info_of_url['params'] = $request;

			}

		}

		if(DEBUG) {

			self::$debug['url'] = self::$info_of_url;
		}

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
	public static function redirect($controller,$action,$params=array())
	{
		$url = Asset::create_url($controller,$action,$params);
		header( "Location: $url" ) ;

	}

	// run the function
	public static function run()
	{

		// so we can instatinate
		foreach(self::$extensions as $folder) {

			// include the bootstrap file from the extenstion
			include SYSTEM_PATH."/extensions/$folder/bootstrap.php";

		}

		// get all the information
		self::getURL();

		// only do this if there is a controller and an action
		if(!empty(self::$info_of_url['controller']) && !empty(self::$info_of_url['action']))
		{

			// create the controller
			$controller = self::instantiate(ucfirst(self::$info_of_url['controller']));

			// set up the request on the controller for later use
			$controller->request = array(
											"GET" => $_GET,
											"POST" => $_POST,
											"SERVER" => $_SERVER,
											"TYPE" => $_SERVER['REQUEST_METHOD'],
											"AJAX" => !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
										);

			// if rest is on and the request type was json
			if(REST)
			{

				if(isset($controller->request['SERVER']['CONTENT_TYPE']) && $controller->request['SERVER']['CONTENT_TYPE'] === "application/json")
				{

					// set the request type's data to the php input stream
					$controller->request[$controller->request['TYPE']] = json_decode(file_get_contents("php://input"));

				}

				// if params are empty put the variable we got as them
				if(empty(self::$info_of_url['params'])) self::$info_of_url['params'] = array($controller->request[$controller->request['TYPE']]);

			}


			//TODO: Add XML and other format support

			// set the view
			$controller::$view_name = self::$info_of_url['action'];

			// call the before action method and see if we should continue
			// if it comes back false stop running
			if(Hooks::call("before_action") === false)
			{
				self::_output_debug();
				return;
			}

			// if params is not an array
			if(!empty(self::$info_of_url['params'][0]))
			{
				// call the action
				call_user_func_array(array($controller,self::$info_of_url['action']),self::$info_of_url['params']);
			}
			else
			{
				call_user_func(array($controller,self::$info_of_url['action']));
			}


			// run the after action method
			if(Hooks::call("after_action") === false)
			{
				self::_output_debug();
				return;
			}


			// extension
			$extension = !empty(self::$info_of_url['ext'])?".".self::$info_of_url['ext']:DEFAULT_VIEW_TYPE;

			// the name of the controller with out Controller
			$controller_name = strtolower(str_replace("Controller", "", self::$info_of_url['controller']));

			// path to view
			$file_name= "{$controller_name}/{$controller::$view_name}$extension";

			// set the layout to be rendered
			$layout = $controller::$layout;

			// if it is ajax we don't want to render a layout
			if($controller->request['AJAX']) $layout = false;

			// render the page
			if(AUTO_RENDER) View::render($file_name,$controller::$view_info,$layout,$controller::$layout_info);

		}

		// if there isn't' a controller or action doing the following
		else
		{

			// TODO: Put 404 Page saying not controller/action

		}

		self::_output_debug();

	}

	private static function _output_debug()
	{
		// if debug is on
		if(DEBUG)
		{
			// render the debug stylesheet
			echo "<style type='text/css'>".View::get_contents(SYSTEM_PATH."/core/debug.css")."</style>";

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

					// if it is an array then implode it to a string
					if(is_array($para)) $para = implode(",", $para);

					// echo out the index number and the value
					echo "<p><span>".$num."</span>".$para."</p>";

				}

			}

			// close the div
			echo "</div>";
		}

	}

	private static function _check_routes($request,$method)
	{
		// loop through the routes
		foreach(Core::$routes as $route=>$info)
		{

			// put the route into an array
			$route_array = explode("/", $route);

			$params = array();
			$route_string = "";

			foreach($route_array as $index=>$string)
			{

				if(isset($request[$index]))
				{

					if(($string == ":num" && is_numeric($request[$index])) || $string == ":any")
					{

						array_push($params,$request[$index]);

					}
					else if($string == $request[$index])
					{
						$route_string .= "/".$request[$index];

					}

				}

			}

			$route = str_replace("/:num", "", $route);
			$route = str_replace("/:any", "", $route);

			if("/".$route === $route_string)
			{
				self::$info_of_url['controller'] = ucfirst($info[0])."Controller";
				self::$info_of_url['action'] = $info[1];
				self::$info_of_url['params'] = $params;
				return true;
			}

		}

		return false;

	}

	private static function _set_action($method)
	{

		// if rest is turned on and method is a method inside controller
		// url: /controller with request
		if(REST)
		{

			// set the action to the method
			self::$info_of_url['action'] = $method;

		}

		// if rest isn't on and default action is a method
		// url: /controller without request
		else
		{

			// set the action to the default
			self::$info_of_url['action'] = DEFAULT_ACTION;

		}

	}
	static public function encrypt($value) {

		if(SALT == "1a2b3c4d5e6f7g8h9i10j11k12l13m14n15o16p") {

			echo "Please change the salt in your settings to a unique set of characters";

		}else {

			return md5($value.SALT);
		}
	}

	// split on caps, add underscores and then convert it to lowercase
	static function toDB($string){

		$string = preg_replace('/\B([A-Z])/', '_$1', $string);
    	return strtolower($string);
	}

	// replace underscores with spaces and capitalize first letter
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

	// add classes to be autoloaded
	public static function addClasses($folder,$files)
	{
		// add the files to that folder
		self::$files['extensions/'.$folder] = $files;
	}
}