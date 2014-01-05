<?php
/**
 * This is the basic setup of a controller
 */

/**
 * The basic controller
 * @category   Core
 * @package    Core
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
class Controller {

	/**
	 * controller: string
	 *
	 * the name of the controller with "Controller" on the end
	 *
	 * @var string
	 */
	static public $controller;

	/**
	 * controller_name: string
	 *
	 * name of this controller without "Controller" on the end
	 *
	 * @var string
	 */
	static public $controller_name;

	/**
	 * view_name: string
	 *
	 * the name of the view to show
	 *
	 * @var string
	 */
	static public $view_name;

	/**
	 * path_to_views: string
	 *
	 * the path to where the views are stored
	 *
	 * @var string
	 */
	static public $path_to_views = "";

	/**
	 * layout: string
	 *
	 * the name of the layout to use
	 *
	 * @var string
	 */
	static public $layout = DEFAULT_LAYOUT;

	/**
	 * layout_info: array
	 *
	 * information to pass to the layout
	 *
	 * @var array
	 */
	static public $layout_info = array();

	/**
	 * view_info: array
	 *
	 * information to pass to the view
	 *
	 * @var array
	 */
	public $view_info = array();

	/**
	 * request: array
	 *
	 * the request information that was sent
	 *
	 * @var [type]
	 */
	public $request;

	/**
	 * success: boolean
	 *
	 * if the action was completed successfully
	 *
	 * @var boolean
	 */
	static public $success = true;

	/**
	 * allowed_actions: array
	 *
	 * all the actions you want to be allowed for this controller
	 *
	 * @var array
	 */
	static public $allowed_actions = array();


	/**
	 * set up this controller with hooks and names
	 */
	public function __construct() {

		// set the controller to the name of this class
		self::$controller = get_class($this);

		// set the controller name to the name of the class minus "Controller"
		self::$controller_name = strtolower(str_replace("Controller", "", self::$controller));

		Hook::register("before_action",array(get_called_class(),"before_action"));
		Hook::register("after_action",array(get_called_class(),"after_action"));
		Hook::register("before_render",array(get_called_class(),"before_render"));

	}
	/**
	 * setter for layout infomation
	 * @param  string $name the key of the information
	 * @param  object $data the data for this key
	 */
	public function layout_data($name, $data)
	{

		self::$layout_info[$name] = $data;

	}

	/**
	 * setter for view infomation
	 * @param  string $name the key of the information
	 * @param  object $data the data for this key
	 */
	public function view_data($name, $data) {

		$this->view_info[$name] = $data;
	}

	/**
	 * instantiate a model
	 * @param  string $name model name
	 */
	public function loadModel($name)
	{

		$model = Core::instantiate($name);

		$this->{$name} = $model;

	}

	/**
	 * run this before any action
	 *
	 * check if the person should be on this page
	 */
	public function before_action()
	{

		// if auth is turned on
		if(AUTH)
		{

			// run the authorization
			return Auth::is_authorized();

		}

	}

	/**
	 * run this after any action
	 */
	public function after_action() {

		if(!self::$success && $this->request['AJAX'])
		{

			header("HTTP/1.1 400 Bad Request");

			return false;

		}

	}

	/**
	 * run before view is rendered
	 */
	public function before_render() {


	}
}