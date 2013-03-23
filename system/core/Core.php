<?php
/**
 * Holds all the important things of the framework
 */

/**
 * This is all the basic functions of the framework. Including: Autoloading, running the framework and utilities
 * @category   Core
 * @package    Core
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */

Class Core {
	/**
	 * instantiated: array
	 *
	 * The classes that have been instantiated
	 *
	 * This will be an array of objects holding all the of classes that have ever been instantiated in this run
	 *
	 * @var array
	 */
	private static $instantiated = array();

	/**
	 * files: array
	 *
	 * Holds all the files to autoload
	 *
	 * array has a key of the folder name and a array of all the file names and path of each file in the folder
	 *
	 * @var array
	 */
	private static $files = array(
			"core" =>array(
				"Asset"      =>"helpers/Asset.php",
				"Auth"       =>"helpers/Auth.php",
				"Hook"		 =>"helpers/Hook.php",
				"Session"    =>"helpers/Session.php",
				"View"       =>"helpers/View.php",
				"Form"		 =>"helpers/Form.php",
				"Controller" =>"Controller.php",
				"Core"       =>"Core.php",
				"Database"   =>"Database.php",
				"Debug"		 =>"Debug.php",
				"Model"      =>"Model.php",
				"ORM"        =>"ORM.php",
				"Validation" =>"Validation.php",
				),

			"extensions" => array()
		);

	/**
	 * paths: array
	 *
	 * the path to various asset folders
	 *
	 * @var [type]
	 */
	public static $paths;


	/**
	 * debug: array
	 *
	 * All the debug information
	 *
	 * this holds all the sequal statements that ran, all the names of the classes that were instantiated, all the information from the url, and all the views that were rendered.
	 *
	 * @var array
	 */
	public static $debug = array(	"statements"   =>array(),
									"instantiated" =>array(),
									"url"          =>array(),
									"views"        =>array()
								);

	/**
	 * extensions: array
	 *
	 * What extensions to include
	 *
	 * @var array
	 */
	public static $extensions = array();

	/**
	 * routes: array
	 *
	 * routes for the framework
	 *
	 * holds all the various dynamic routes for each application
	 * @var array
	 */
	public static $routes = array();

	/**
	 * Info of URL: array
	 *
	 * This will hold all the url information
	 *
	 * Controller: name of the controller to go to
	 *
	 * Action: name of the action to run
	 *
	 * Params: array of params to pass to action
	 *
	 * @var array
	 */
	public static $info_of_url = array("controller"=>"","action"=>"","params"=>array(),"ext"=>"");

	/**
	 * Autoload Classes using the name of the class
	 *
	 * @param  string $classname Name of the string to autoload
	 */
	public static function autoloader($classname)
	{

		// includes framework specific files
		foreach(self::$files as $folder=>$file) {
			foreach($file as $name=>$filePath) {
				if($classname == $name) {
					self::$instantiated[$classname]['file_path'] = "/".$folder."/".$filePath;
					include_once SYSTEM_PATH."/".$folder."/".$filePath;
					return ;
				}
			}
		}

		// if not in the framework list and includes the word controller instantiate a controller file
		if(strstr($classname,"Controller")) {
			if(strstr($classname,"Test") && is_file(SYSTEM_PATH."/tests/".$classname.".php")) {
				self::$instantiated[$classname]['file_path'] = "/tests/".$classname.".php";
				include SYSTEM_PATH."/tests/".$classname.".php";
				return;
			}else if (is_file(SYSTEM_PATH."/controllers/".$classname.".php")){
				self::$instantiated[$classname]['file_path'] = "/controllers/".$classname.".php";
				include SYSTEM_PATH."/controllers/".$classname.".php";
				return;
			}
			else {
				trigger_error("404: Controller: ".$classname." Not Found",E_USER_ERROR);
				return;
			}

		}

		// else instantiate a model
		else if(is_file(SYSTEM_PATH."/models/".$classname.".php")) {
			self::$instantiated[$classname]['file_path'] = "/models/".$classname.".php";
			include SYSTEM_PATH."/models/".$classname.".php";
			return;
		}
		else {
			trigger_error("404: ".$classname." Not Found",E_USER_ERROR);
			return;
		}
	}

	/**
	 * Run the framework.
	 */
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
		if(Hook::call("before_action") === false)
		{
			// output the debug information
			if(!$controller->request['AJAX'])Debug::render();
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
		if(Hook::call("after_action") === false)
		{
			// output the debug information
			if(!$controller->request['AJAX'])Debug::render();
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

		// use the controller path if it is defined, else use the path to the controller to define it.
		$path_to_views = $controller::$path_to_views;

		// if the controller didn't have a path to views
		if(!$path_to_views) {

			// get the directory two levels up of the controller
			$path_to_views = dirname(dirname(self::$instantiated[self::$info_of_url['controller']]['file_path']));

			// add "views" to the file path
			$path_to_views .= strlen($path_to_views) >1 ?"/views/":"views/";

		}

		// render the page
		if(AUTO_RENDER) View::render($file_name,$controller::$view_info,array("layout"=>$layout,"layout_info"=>$controller::$layout_info,"path_to_views"=>$path_to_views));

		// output the debug information
		if(!$controller->request['AJAX'])Debug::render();
	}

	/**
	 * Find the Controller, Action and Params from the url that was called
	 */
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
				array_shift($request);

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
					array_shift($request);

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

	/**
	 * check to see if the request is the routes
	 * @param  array $request the information that came through the url
	 * @param  string $method  the method that called this page
	 * @return Boolean          if the request was in the routes
	 */
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


	/**
	 * Set the action to either the rest or the default depending on settings
	 * @param string $method the method that called this page
	 */
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


	/**
	 * instantiate classes for user
	 * @api
	 * @param  string $classname the name of the class to instatinate
	 */
	public static function instantiate($classname)
	{
		// if it has already been instantiated
		if(isset(self::$instantiated[$classname]['class']))
		{

			//return that one
			return self::$instantiated[$classname]['class'];

		}

		// if it hasn't been instantiated
		else
		{

			// push the name into array for debugging
			array_push(self::$debug['instantiated'],$classname);

			// instatiate it and put it in the array and then return it
			return self::$instantiated[$classname]['class'] = new $classname;
		}

	}

	/**
	 * Redirect to a different location
	 * @api
	 * @param  string $controller the controller name
	 * @param  string $action     the action name
	 * @param  array  $params     the params to pass to the new url
	 */
	public static function redirect($controller,$action,$params=array())
	{
		$url = Asset::create_url($controller,$action,$params);
		header( "Location: $url" ) ;
	}

	/**
	 * encrypt sensitive data using this function
	 * @api
	 * @param  string $value string you want to encrypt
	 * @return string        The encrypted string
	 */
	public static function encrypt($value)
	{

		if(SALT == "1a2b3c4d5e6f7g8h9i10j11k12l13m14n15o16p") {

			echo "Please change the salt in your settings to a unique set of characters";

		}else {

			return md5($value.SALT);
		}
	}


	/**
	 * split on caps, add underscores and then convert it to lowercase
	 * @api
	 * @param  string $string the string to convert
	 * @return string         the converted string
	 */
	public static function to_db($string){

		$string = preg_replace('/\B([A-Z])/', '_$1', $string);
    	return strtolower($string);
	}


	/**
	 * replace underscores with spaces and capitalize first letter
	 * @api
	 * @param  string $string the string to convert
	 * @return string         the converted string
	 */
	public static function to_norm($string)
	{
		$string = str_replace("_", " ", $string);
		return ucfirst($string);
	}


	/**
	 * find the underscores and convert the following letter to and uppercase
	 * @api
	 * @param  string $string the string to convert
	 * @return string         the converted string
	 */
	public static function to_cam($string)
	{
		$func = create_function('$c', 'return strtoupper($c[1]);');
    	$string = preg_replace_callback('/_([a-z])/', $func, $string);
		return ucfirst($string);
	}


	/**
	 * add classes to be autoloaded
	 * @api
	 * @param string $folder the folder name
	 * @param array $files  file names in the folder
	 */
	public static function add_classes($folder,$files)
	{
		// add the files to that folder
		self::$files['extensions/'.$folder] = $files;
	}
}