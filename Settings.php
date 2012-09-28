<?php
class Settings {

	// default title
	static public $title = "Film Hubble";

	// default database information
	//static public $db = array('DSN' => 'mysql:hostname=74.53.119.203;dbname=rhigley_Filmhubble','table' => 'filmHubble','username' => 'rhigley','password' => 'r110492');
	static public $db = array('DSN' => 'mysql:hostname=localhost;dbname=filmHubble','table' => 'filmHubble','username' => 'root','password' => 'root');
	// default controller when there isn't on avaliable
	public static $defaultController = 'Home';

	// default action when there isn't one avaliable
	public static $defaultAction = 'index';

	// default action when there isn't one avaliable
	public static $defaultTemplate = 'default';

	// if you want to use templates
	public static $templates = false;

	// if you would like to see the debug outout
	public static $debug = true;

	// salt to use for any encripytion
	public static $salt = "1a2b3c4d5e6f7g8h9i10j11k12l13m14n15o16p";

	// url to the app relative to the index.php inside webroot
	public static $pathToApp = "../";

	// if you would like the REST API Settings turned on
		// All calls will be processed based on the type of HTTP Request
		// Any calls made with ajax will automaticly return JSON and not render a view
	public static $rest = true;

	// return this type to an ajax request
	public static $ajaxReturnType = 'json';

	// settings for authentication
	public static $auth = array(
		// is authentication on?
		"on"=>true,
		// always allow the following action in every controller
		"alwaysAllowActions"=> array(),
		// the table to use for authentication
		"table"=>"user",
		// the field for the username
		"usernameField"=>"email",
		// the field for the password
		"passwordField"=>"password",
		// the controller to go to on failure to authenticate
		"redirectController"=>"home",
		// the action to go to on failure to authenticate
		"redirectAction"=>"index",
		// the params to pass on failure to login
		"redirectParams"=>""
	);

	public static $routes = array(
		// route name => controller to go to, action, params to pass
		"test" =>array('home','index')
	);
}