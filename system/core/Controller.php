<?php
class Controller {

	// the name of the controller
	static public $controller;

	// name of this controller
	static public $controller_name;

	// the name of the view to show
	static public $view_name;

	// layout to use
	static public $layout = DEFAULT_LAYOUT;

	// information to pass to the layout
	static public $layout_info = array();

	// information to pass to the view
	static public $view_info = array();

	// add information to the layout_info
	public $request;

	// the success dynamic
	static public $success = true;

	static public $allowed_actions = array();

	public function __construct() {

		// set the controller to the name of this class
		self::$controller = get_class($this);

		// set the controller name to the name of the class minus "Controller"
		self::$controller_name = strtolower(str_replace("Controller", "", self::$controller));

		Hooks::register("before_action",array(get_called_class(),"before_action"));
		Hooks::register("after_action",array(get_called_class(),"after_action"));
		Hooks::register("before_render",array(get_called_class(),"before_render"));

	}
	public function layout_data($name, $data)
	{

		self::$layout_info[$name] = $data;

	}
	// add information to the view_info
	public function view_data($name, $data) {

		self::$view_info[$name] = $data;
	}
	// instantiate a model
	public function loadModel($name)
	{

		$model = Core::instantiate($name);

		$this->{$name} = $model;

	}
	public function before_action()
	{

		// if auth is turned on
		if(AUTH)
		{

			// run the authorization
			Auth::isAuthorized();

		}

	}
	public function after_action() {

		if(!self::$success && $this->request['AJAX'])
		{

			header("HTTP/1.1 400 Bad Request");

			return false;

		}

	}

	// do this before view is rendered
	public function before_render() {


	}
}