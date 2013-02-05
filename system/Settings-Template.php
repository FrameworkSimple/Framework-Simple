<?php
	/**
	 * All the settings for the application
	 * @category   Core
	 * @package    Core
	 * @author     Rachel Higley <me@rachelhigley.com>
	 * @copyright  2013 Framework Simple
	 * @license    http://www.opensource.org/licenses/mit-license.php MIT
	 * @link       http://rachelhigley.com/framework
	 */

	/**
	 * title: string
	 *
	 * the title of the application
	 */
	const TITLE = "Framework Simple";


	/**
	 * db_dsn: string
	 *
	 * the database DSN information
	 */
	const DB_DSN = 'mysql:hostname=localhost;dbname=simple';

	/**
	 * db_username: string
	 *
	 * the username for the database
	 */
	const DB_USERNAME = 'root';

	/**
	 * db_password:string
	 *
	 * the password for the database
	 */
	const DB_PASSWORD = 'root';

	/**
	 * default_controller: string
	 *
	 * the default controller for the root
	 */
	const DEFAULT_CONTROLLER = 'Hello';

	/**
	 * default_action: string
	 *
	 * the action used if non is specified
	 */
	const DEFAULT_ACTION = 'index';

	/**
	 * default_layout: string/boolean
	 *
	 * the layout to use by default
	 */
	const DEFAULT_LAYOUT = false;

	/**
	 * default_view_type: string
	 *
	 * the default view type ("html","json","xml",etc.)
	 */
	const DEFAULT_VIEW_TYPE = '';

	/**
	 * layouts: boolean
	 *
	 * if layouts are turned on
	 */
	const LAYOUTS = true;

	/**
	 *	debug: boolean
	 *
	 * if you want to show debug information
	 */
	const DEBUG = true;

	/**
	 * salt: string
	 *
	 * the salt used for encrypting your data
	 */
	const SALT = "1a2b3c4d5e6f7g8h9i10j11k12l13m14n15o16p";

	/**
	 * path_to_app: string
	 *
	 * url to the app relative to the index.php inside webroot
	 */
	const PATH_TO_APP = "../";

	/**
	 * rest: boolean
	 *
	 * if you would like the REST API Settings turned on
	 *  - All calls will be processed based on the type of HTTP Request
	 *  - Any calls made with ajax will automatically return JSON and not render a view
	 */
	const REST = true;

	/**
	 * auto_render: boolean
	 *
	 * if you want the pages to auto render out views
	 */
	const AUTO_RENDER = true;

	/**
	 * ajax_return_type: boolean
	 *
	 * return this type to an ajax request
	 */
	const AJAX_RETURN_TYPE = 'json';

	/**
	 * session: boolean
	 *
	 * if the session will be used
	 */
	const SESSION = false;


	/**
	 * auth: boolean
	 *
	 * if you want to use authentication
	 */
	const AUTH = false;

	/**
	 *	auth_table: string
	 *
	 *  the table to use for authentication
	 */
	const AUTH_TABLE = "user";

	/**
	 * auth_username_field: string
	 *
	 * the field for the username
	 */
	const AUTH_USERNAME_FIELD = "email";

	/**
	 * auth_password_field: string
	 *
	 * the field for the password
	 */
	const AUTH_PASSWORD_FIELD = "password";

	/**
	 * auth_redirect_controller: string
	 *
	 * the controller to go to on failure to authenticate
	 */
	const AUTH_REDIRECT_CONTROLLER = "home";

	/**
	 * auth_redirect_action: string
	 *
	 * the action to go to on failure to authenticate
	 */
	const AUTH_REDIRECT_ACTION = "index";

	/**
	 * an array list of extensions you are using
	 * - hint: list should be file names without the .php
	 */

	Core::$extensions = array();

	/**
	 * set up custom page routes
	 */
	Core::$routes = array(
		// route name => array(controller to go to, action, params to pass)
		"/" =>array('hello','index'),
	);