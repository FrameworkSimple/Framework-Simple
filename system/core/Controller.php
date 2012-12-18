<?php
class Controller {
	
	// the name of the view to show
	static public $viewname;
	
	// template to use
	static public $template;
	
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
	public function beforeAction()
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
}