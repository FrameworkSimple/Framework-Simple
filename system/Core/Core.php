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

Class Core_Core {
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
	 * Redirect: boolean
	 *
	 * Allow the core to redirect pages.
	 *
	 * Can be turned off for testing purposes.
	 */
	public static $redirect = true;

	public static $namespaces = array('Core','Core_Helper',"Extension");

	/**
	 * Autoload Classes using the name of the class
	 *
	 * @param  string $classname Name of the string to autoload
	 */
	public static function autoLoader($class_name)
	{

		$class_path = str_replace("_", "/", $class_name);
		$full_path = SYSTEM_PATH.$class_path.".php";
		if(!file_exists($full_path))
		{
			foreach (self::$namespaces as $ns) {
				$full_class_name = $ns."_".$class_name;
				$class_path = str_replace("_", "/", $full_class_name);
				$full_path = SYSTEM_PATH.$class_path.".php";
				if(file_exists($full_path))
				{
					self::$instantiated[$class_name]['file_path'] = $class_path;
					include_once $full_path;


					class_alias($full_class_name,$class_name);
				}
			}
		}
		elseif(file_exists($full_path))
		{
			self::$instantiated[$class_name]['file_path'] = $class_path;
			include_once $full_path;
		}
		else {
			self::error("404: ".$class_name." Not Found at path ".$full_path,E_USER_NOTICE);
			return;
		}


	}

	/**
	 * Run the framework.
	 */
	public static function run()
	{

		// so we can instantiate
		foreach(self::$extensions as $folder) {

			// include the bootstrap file from the extension
			include SYSTEM_PATH."Extension/$folder/Bootstrap.php";
			if(file_exists(SYSTEM_PATH."Extension/$folder/SettingsApplication.php")) include SYSTEM_PATH."Extension/$folder/SettingsApplication.php";
			if(file_exists(SYSTEM_PATH."Extension/$folder/SettingsEnvironment.php")) include SYSTEM_PATH."Extension/$folder/SettingsEnvironment.php";
		}

		// set up the request on the controller for later use
		$request = array(
						"GET" => $_GET,
						"POST" => $_POST,
						"SERVER" => $_SERVER,
						"TYPE" => $_SERVER['REQUEST_METHOD'],
						"AJAX" => !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['HTTP_ACCEPT'],"application/json") !== false
					);

		// turn off redirection for ajax requests
		if($request['AJAX']) self::$redirect = false;

		// if rest is on and the request type was json
		if(REST)
		{

			if($request['AJAX'])
			{

				//the php input stream
				$stream = !empty($_REQUEST)?$_REQUEST: json_decode(file_get_contents("php://input"),true);

				// remove the php input
				if(isset($stream["PHPSESSID"])) unset($stream["PHPSESSID"]);

				// set the request type's data to the php input stream if there is content
				if($stream) $request[$request['TYPE']] = $stream;

			}
			$request_data = isset($request[$request['TYPE']])?$request[$request['TYPE']]:array();

			// if there is request data add it to the params
			if(!empty($request_data)) array_push(self::$info_of_url['params'], $request_data);

		}
		// get all the information
		self::_getUrl();

		// create the controller
		$controller = self::instantiate(self::$info_of_url['controller']);

		$controller->request = $request;

		//TODO: Add XML and other format support

		// set the view
		$controller::$view_name = self::$info_of_url['action'];

		// call the before action method and see if we should continue
		// if it comes back false stop running
		if(Hook::call("beforeAction") === false)
		{
			// output the debug information
			if(!$controller->request['AJAX'])Debug::render();
			return;
		}
		// if params is not an array
		if(isset(self::$info_of_url['params'][0]) && (!empty(self::$info_of_url['params'][0]) || self::$info_of_url['params'][0] === "0"))
		{
			// call the action
			call_user_func_array(array($controller,self::$info_of_url['action']),self::$info_of_url['params']);
		}
		else
		{
			call_user_func(array($controller,self::$info_of_url['action']));
		}


		// run the after action method
		if(Hook::call("afterAction") === false)
		{
			// output the debug information
			if(!$controller->request['AJAX'])Debug::render();
			return;
		}


		// extension
		$extension = !empty(self::$info_of_url['ext'])?".".self::$info_of_url['ext']:DEFAULT_VIEW_TYPE;

		// the name of the controller with out Controller

		$controller_name = Utilities::toDb(str_replace("Controller_", "", self::$info_of_url['controller']));

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
			$path_to_views .= "/View/";

		}

		// render the page
		if(AUTO_RENDER) View::render($file_name,$controller->view_info,array("layout"=>$layout,"layout_info"=>$controller::$layout_info,"path_to_views"=>$path_to_views));

		// output the debug information
		if(!$controller->request['AJAX'])Debug::render();
	}

	/**
	 * Find the Controller, Action and Params from the url that was called
	 */
	private static function _getUrl()
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

		// if there is a / at the beginning
		if(strpos($uri,"/") === 0) $uri = substr($uri, 1);

		// if the uri is just a blank string use an array if it has length then break it into pieces
		$request = !empty($uri)?explode("/", $uri):array("");

		// if the uri is not in the routes
		if(!self::_checkRoutes($request, $method))
		{

			// if there is no controller
			// url: /
			if(empty($request[0]))
			{
				self::$info_of_url['controller'] =  "Controller_".ucfirst(DEFAULT_CONTROLLER);
			}
			// if there is a controller
			// url: /controller/(action/params)/(params)
			else
			{

				// set the controller
				self::$info_of_url['controller'] =  "Controller_".ucfirst($request[0]);

				// remove the controller from the request
				array_shift($request);

			}

			// if there is an second value
			if (isset($request[0]) && (!empty($request[0]) || $request[0] === "0"))
			{



				// if the second request is a method
				if(method_exists(self::$info_of_url['controller'], $request[0]))
				{
					// set the action
					// url: /controller/action
					self::$info_of_url['action'] = $request[0];

					// remove the action from the request
					array_shift($request);

					// set the params
					// url: /controller/action/param

					self::$info_of_url['params'] = array_merge(self::$info_of_url['params'], $request);

				}
				else
				{
					// set the params
					// url: /controller/action/param
					self::$info_of_url['params'] = array_merge(self::$info_of_url['params'], $request);

					// set the action
					self::_setAction($method);
				}


			}
			// if there is no second value
			else
			{

				// set the action
				self::_setAction($method);

			}
			// if the method doesn't exist
			if(!method_exists(self::$info_of_url['controller'], self::$info_of_url['action']))
			{

				foreach (self::$namespaces as $ns) {
					if(method_exists($ns."_".self::$info_of_url['controller'], self::$info_of_url['action']))
						return true;
				}
				self::error("404: Action: ".self::$info_of_url['action']." Not Found",E_USER_ERROR);
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
	private static function _checkRoutes($request,$method)
	{
		// loop through the routes
		foreach(self::$routes as $route=>$info)
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
				self::$info_of_url['controller'] = "Controller_".ucfirst($info[0]);

				self::$info_of_url['params'] = $params;


				if(empty(self::$info_of_url['action'])) isset($info[1])? self::$info_of_url['action'] = $info[1]:self::_setAction($method);

				// if the method doesn't exist
				if(!method_exists(self::$info_of_url['controller'], self::$info_of_url['action']))
				{
					foreach (self::$namespaces as $ns) {
						if(method_exists($ns."_".self::$info_of_url['controller'], self::$info_of_url['action']))
							return true;
					}
					self::$info_of_url['action'] = "";
					self::$info_of_url['params'] = array();
					self::error("404: Action: ".self::$info_of_url['action']." Not Found",E_USER_ERROR);
					return false;

				}

				return true;
			}



		}

		return false;
	}


	/**
	 * Set the action to either the rest or the default depending on settings
	 * @param string $method the method that called this page
	 */
	private static function _setAction($method)
	{

		// if rest is turned on and method is a method inside controller
		// url: /controller with request
		if(REST)
		{

			// set the action to the method
			self::$info_of_url['action'] = ($method === "get" && empty(self::$info_of_url['params']))? "index" : $method;

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
	public static function instantiate($classname,$params=array())
	{
		// if it has already been instantiated
		if(isset(self::$instantiated[$classname]['class']) && self::$instantiated[$classname]['params'] === $params)
		{

			//return that one
			return self::$instantiated[$classname]['class'];

		}

		// if it hasn't been instantiated
		else
		{

			if(empty($params)) $params = array();

			// push the name into array for debugging
			array_push(self::$debug['instantiated'],$classname);

			$reflector = new ReflectionClass($classname);
			self::$instantiated[$classname]['params'] = $params;
			self::$instantiated[$classname]['class'] = $reflector->newInstanceArgs($params);

			// instatiate it and put it in the array and then return it
			return self::$instantiated[$classname]['class'];
		}

	}

	public static function error($message, $level=E_USER_NOTICE,$header='404')
	{
		$backtrace = debug_backtrace();
		$caller = next($backtrace);
		switch ($header) {
			case '400':
				header("HTTP/1.1 400 Bad Request");
				break;
			case '401':
				header("HTTP/1.1 401 Unauthorized");
				break;
			case '404':
				header("HTTP/1.1 404 Not Found");
				break;

			default:
				header("HTTP/1.1 400 Bad Request");
				break;
		}
		if(isset($caller['file']) && $caller['function'] && $caller['line']) $message = $message.' in <strong>'.$caller['function'].'</strong> called from <strong>'.$caller['file'].'</strong> on line <strong>'.$caller['line'].'</strong>'."\n<br />error handler";
		trigger_error($message, $level);


	}

	/**
	 * Redirect to a different location
	 * @api
	 * @param  string $controller the controller name
	 * @param  string $action     the action name
	 * @param  array  $params     the params to pass to the new url
	 */
	public static function redirect($controller,$action=null,$params=array())
	{
		// only redirect if it is coming from a real page
		if(self::$redirect)
		{
			if($action)
			{
				$url = Asset::createUrl($controller,$action,$params);
				header( "Location: $url" ) ;
			}
			else
			{
				header( "Location: $controller" ) ;
			}

		}
	}

	/**
	 * add classes to be autoloaded
	 * @api
	 * @param string $folder the folder name
	 * @param array $files  file names in the folder
	 */
	public static function addNamespace($namespace)
	{
		// add the namespace to the ones we look through
		array_push(self::$namespaces, $namespace);
	}
}