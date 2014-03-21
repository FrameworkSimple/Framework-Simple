<?php
/**
 * Global settings for the application
 * @category   Core
 * @package    Core
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */

/**
 * Title: string
 *
 * the title of the application
 */
const TITLE = "Framework Simple";

/**
 * Default Controller: string
 *
 * the default controller for the root
 */
const DEFAULT_CONTROLLER = 'Hello';

/**
 * Default Action: string
 *
 * the action used if non is specified
 */
const DEFAULT_ACTION = 'index';

/**
 * Default Layout: string/boolean
 *
 * the layout to use by default
 */
const DEFAULT_LAYOUT = false;

/**
 * Default View Type: string
 *
 * the default view type ("html","json","xml",etc.)
 */
const DEFAULT_VIEW_TYPE = '';

/**
 * Layouts: boolean
 *
 * if layouts are turned on
 */
const LAYOUTS = true;

/**
 * Rest: boolean
 *
 * if you would like the REST API Settings turned on
 *  - All calls will be processed based on the type of HTTP Request
 *  - Any calls made with ajax will automatically return JSON and not render a view
 */
const REST = true;

/**
 * Auto Render: boolean
 *
 * if you want the pages to auto render out views
 */
const AUTO_RENDER = true;

/**
 * Ajax Return Type: string
 *
 * return this type to an ajax request
 */
const AJAX_RETURN_TYPE = 'json';

/**
 * Session: boolean
 *
 * if the session will be used
 */
const SESSION = false;

/**
 * Authorize: boolean
 *
 * if you want to use authentication
 */
const AUTH = false;

/**
 *	Authorize Table: string
 *
 *  the table to use for authentication
 */
const AUTH_TABLE = "user";

/**
 * Authorize Username Field: string
 *
 * the field for the username
 */
const AUTH_USERNAME_FIELD = "email";

/**
 * Authorize Password Field: string
 *
 * the field for the password
 */
const AUTH_PASSWORD_FIELD = "password";

/**
 * Authorize Redirect Controller: string
 *
 * the controller to go to on failure to authenticate
 */
const AUTH_REDIRECT_CONTROLLER = "home";

/**
 * Authorize Redirect Action: string
 *
 * the action to go to on failure to authenticate
 */
const AUTH_REDIRECT_ACTION = "index";

/**
 * Salt: string
 *
 * the salt used for encrypting your data
 */
const SALT = "1a2b3c4d5e6f7g8h9i10j11k12l13m14n15o16p";

/**
 * Extensions: array
 *
 * an array list of extensions you are using
 * - hint: list should be file names without the .php
 */

Core::$extensions = array("AdminPanel");

/**
 * Routes: array
 *
 * set up custom page routes
 * route name => array(controller to go to, action, params to pass)
 */
Core::$routes = array(

	"/" =>array('hello','index'),
);
