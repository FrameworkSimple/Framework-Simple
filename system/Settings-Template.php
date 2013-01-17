<?php

	// default title
	const TITLE = "Framework Simple";

	// database information

		// dsn information
		const DB_DSN = 'mysql:hostname=localhost;dbname=simple';

		// database username
		const DB_USERNAME = 'root';

		// database password
		const DB_PASSWORD = 'root';

	// default controller when there isn't on available
	const DEFAULT_CONTROLLER = 'Hello';

	// default action when there isn't one available
	const DEFAULT_ACTION = 'index';

	// default action when there isn't one available
	const DEFAULT_LAYOUT = false;

	// the default for how you want views to output when no extension is specified
	const DEFAULT_VIEW_TYPE = '';

	// if you want to use layouts
	const LAYOUTS = true;

	// if you would like to see the debug output
	const DEBUG = true;

	// salt to use for any encryption
	const SALT = "1a2b3c4d5e6f7g8h9i10j11k12l13m14n15o16p";

	// url to the app relative to the index.php inside webroot
	const PATH_TO_APP = "../";

	// if you would like the REST API Settings turned on
		// All calls will be processed based on the type of HTTP Request
		// Any calls made with ajax will automatically return JSON and not render a view
	const REST = true;

	// if you want the pages to auto render out views
	const AUTO_RENDER = true;

	// return this type to an ajax request
	const AJAX_RETURN_TYPE = 'json';

	// is the session is use
	const SESSION = false;

	// settings for authentication

		// do you want to use authentication
		const AUTH = false;

		// the table to use for authentication
		const AUTH_TABLE = "user";

		// the field for the username
		const AUTH_USERNAME_FIELD = "email";

		// the field for the password
		const AUTH_PASSWORD_FIELD = "password";

		// the controller to go to on failure to authenticate
		const AUTH_REDIRECT_CONTROLLER = "home";

		// the action to go to on failure to authenticate
		const AUTH_REDIRECT_ACTION = "index";

	// an array list of extensions you are using
		// hint: list should be file names without the .php
	Core::$extensions = array();

	// set up custom page routes
	Core::$routes = array(
		// route name => array(controller to go to, action, params to pass)
		"/" =>array('hello','index'),
	);