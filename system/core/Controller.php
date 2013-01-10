<?php
class Controller {

	// the name of the controller
	static public $controller;

	// name of this controller
	static public $controller_name;

	// the name of the view to show
	static public $view_name;

	// template to use
	static public $template = DEFAULT_TEMPLATE;

	// information to pass to the template
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
		Hooks::register("before_render",array(get_called_class(),"before_render"));

	}
	public function content_for_layout($name, $data)
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
	public function afterAction() {

		if(!self::$success && $this->request['AJAX'])
		{

			header("HTTP/1.1 400 Bad Request");

		}
	}

	// do this before view is rendered
	public function before_render() {

		// if auto render should run
		return true;
	}
}