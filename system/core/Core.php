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

	// run the function
	public static function run()
	{

		// so we can instatinate
		foreach(self::$extensions as $folder) {

			// include the bootstrap file from the extenstion
			include SYSTEM_PATH."/extensions/$folder/bootstrap.php";

		}

		// get all the information
		self::_get_url();


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
			$request_data = $controller->request[$controller->request['TYPE']];


			// if there is request data add it to the params
			if(!empty($request_data)) array_push(self::$info_of_url['params'], $request_data);


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

		self::_output_debug();
	}

	// get all the url information
	private static function _get_url()
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
		self::$info_of_url['ext'] = isset($url[1])?$url[1]:'';

		// variable for the request that was made
		$uri = str_replace(dirname($_SERVER['SCRIPT_NAME'])."/",'',$url[0]);

		// if the uri is just a blank string use an array if it has length then break it into pieces
		$request = !empty($uri)?explode("/", $uri):array("");

		// if the uri is not in the routes
		if(!self::_check_routes($request, $method))
		{

			// if there is no controller
			// url: /
			if(empty($request[0]))
			{
				self::$info_of_url['controller'] = ucfirst(DEFAULT_CONTROLLER)."Controller";
			}
			// if there is a controller
			// url: /controller/(action/params)/(params)
			else
			{

				// set the controller
				self::$info_of_url['controller'] = ucfirst($request[0]).'Controller';

				// remove the controller from the request
				unset($request[0]);

			}
			// if the file doesn't exist
			if(!is_file(SYSTEM_PATH."/controllers/".self::$info_of_url['controller'].".php"))
			{

				trigger_error("404: Controller: ".self::$info_of_url['controller']." Not Found",E_USER_ERROR);
				return;

			}
			// if there is an second value
			if (isset($request[0]))
			{


				// if the second request is a method
				if(method_exists(self::$info_of_url['controller'], $request[0]))
				{

					// set the action
					// url: /controller/action
					self::$info_of_url['action'] = $request[0];

					// remove the action from the request
					unset($request[0]);

				}
				else
				{
					// set the action
					self::_set_action($method);
				}

				// set the params
				// url: /controller/action/param
				self::$info_of_url['params'] = $request;


			}
			// if there is no second value
			else
			{

				// set the action
				self::_set_action($method);

			}
			// if the method doesn't exist
			if(!method_exists(self::$info_of_url['controller'], self::$info_of_url['action']))
			{

				trigger_error("404: Action: ".self::$info_of_url['action']." Not Found",E_USER_ERROR);
				return;

			}

		}

		if(DEBUG) {

			self::$debug['url'] = self::$info_of_url;
		}
	}

	// the debug output if debug is on
	private static function _output_debug()
	{
		// if debug is on
		if(DEBUG)
		{
			include SYSTEM_PATH."/core/Debug.php";
		}
	}

	// check to see if the request is the routes
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
					else if($string == ":action" && isset($request[$index]))
					{

						self::$info_of_url['action'] = $request[$index];

					}
					else if(strtolower($string) === strtolower($request[$index]))
					{
						$route_string .= "/".$request[$index];

					}
					else
					{
						$route_string .="/additional";
					}

				}

			}


			$route = str_replace("/:num", "", $route);
			$route = str_replace("/:any", "", $route);
			$route = str_replace("/:action", "", $route);
			if($route === "/") $route = "";

			if(strtolower("/".$route) === strtolower($route_string))
			{
				self::$info_of_url['controller'] = ucfirst($info[0])."Controller";

				// if the file doesn't exist
				if(!is_file(SYSTEM_PATH."/controllers/".self::$info_of_url['controller'].".php"))
				{

					trigger_error("404: Controller: ".self::$info_of_url['controller']." Not Found",E_USER_ERROR);
					return false;

				}
				if(empty(self::$info_of_url['action'])) isset($info[1])? self::$info_of_url['action'] = $info[1]:self::_set_action($method);

				// if the method doesn't exist
				if(!method_exists(self::$info_of_url['controller'], self::$info_of_url['action']))
				{

					trigger_error("404: Action: ".self::$info_of_url['action']." Not Found",E_USER_ERROR);
					return false;

				}

				self::$info_of_url['params'] = $params;

				return true;
			}


		}

		return false;
	}

	// set the action
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

	// encrypt any sensitive infromation
	public static function encrypt($value)
	{

		if(SALT == "1a2b3c4d5e6f7g8h9i10j11k12l13m14n15o16p") {

			echo "Please change the salt in your settings to a unique set of characters";

		}else {

			return md5($value.SALT);
		}
	}

	// split on caps, add underscores and then convert it to lowercase
	public static function to_db($string){

		$string = preg_replace('/\B([A-Z])/', '_$1', $string);
    	return strtolower($string);
	}

	// replace underscores with spaces and capitalize first letter
	public static function to_norm($string)
	{
		$string = str_replace("_", " ", $string);
		return ucfirst($string);
	}

	// find the underscores and convert the following letter to and uppercase
	public static function to_cam($string)
	{
		$func = create_function('$c', 'return strtoupper($c[1]);');
    	$string = preg_replace_callback('/_([a-z])/', $func, $string);
		return ucfirst($string);
	}

	// add classes to be autoloaded
	public static function add_classes($folder,$files)
	{
		// add the files to that folder
		self::$files['extensions/'.$folder] = $files;
	}
}