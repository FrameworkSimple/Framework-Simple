<?php
class Settings {

	// default title
	static public $title = "Framework Simple";

	// database information
	static public $db = array('DSN' => 'mysql:hostname=localhost;dbname=simple','username' => 'root','password' => 'root');

	// default controller when there isn't on available
	public static $defaultController = 'Hello';

	// default action when there isn't one available
	public static $defaultAction = 'index';

	// default action when there isn't one available
	public static $defaultTemplate = false;

	// the default for how you want views to output when no extension is specified 
	public static $defaultViewType = '';

	// if you want to use templates
	public static $templates = true;

	// if you would like to see the debug output
	public static $debug = true;

	// salt to use for any encryption
	public static $salt = "1a2b3c4d5e6f7g8h9i10j11k12l13m14n15o16p";

	// url to the app relative to the index.php inside webroot
	public static $pathToApp = "../";

	// if you would like the REST API Settings turned on
		// All calls will be processed based on the type of HTTP Request
		// Any calls made with ajax will automatically return JSON and not render a view
	public static $rest = true;

	// return this type to an ajax request
	public static $ajaxReturnType = 'json';

	// settings for session
	public static $session = array(

		// do you want session on?
		"on" => false
	);

	// settings for authentication
	public static $auth = array(
		
		// is authentication on?
		"on"=>false,
		
		//always allow these controllers
		"controllers"=>array(),
		
		//always allow these actions
		"actions"=>array(),
		
		// the table to use for authentication
		"table"=>"user",
		
		// the field for the username
		"username_field"=>"email",
		
		// the field for the password
		"password_field"=>"password",
		
		// the controller to go to on failure to authenticate
		"redirect_controller"=>"home",
		
		// the action to go to on failure to authenticate
		"redirect_action"=>"index",
		
		// the params to pass on failure to login
		"redirect_params"=>array()
	);

	// an array list of extensions you are using
		// hint: list should be file names without the .php
	public static $extensions = array();

	// set up custom page routes
	public static $routes = array(
		// route name => array(controller to go to, action, params to pass)
		"/" =>array('hello','index'),
	);
}