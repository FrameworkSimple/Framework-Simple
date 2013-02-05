<?php

/**
 * Everything having to do with authentication
 */

/**
 * This handles all the user authentication
 * @category   Core
 * @package    Core
 * @subpackage Helpers
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
Class Auth {

	/**
	 * controllers: array
	 *
	 * all the allow controllers
	 *
	 * an array of strings that holds controllers that are always allowed
	 *
	 * @var array
	 */
	public static $controllers = array();


	/**
	 * actions: array
	 *
	 * all the allowd actions
	 *
	 * an array of strings that holds actions that are allowed for all controllers
	 *
	 * @var array
	 */
	public static $actions = array();

	/**
	 * check if you are authorized to be on this page
	 * @api
	 * @return boolean if authorized
	 */
	public static function is_authorized()
	{

		// get the controller and action
		$url = Core::$info_of_url;

		// check if controller is in allowed controllers
		if(in_array($url['controller'], self::$controllers))
		{

			// return true because it is allowed
			return true;

		}

		// cehck if the action is an allowed actions
		else if(in_array($url['action'], self::$actions))
		{

			// return true because it is allowed
			return true;
		}

		// if it is allowed in this controller only
		else if(in_array($url['action'],$url['controller']::$allowed_actions))
		{

			// return true because it is allowed
			return true;
		}

		// if it isn't in any of the allowed settings
		else
		{

			if(Session::get('logged_in'))
			{

				// return true because user is logged in
				return true;

			}
			else
			{

				// redirect to a new page
				Core::redirect(AUTH_REDIRECT_CONTROLLER,AUTH_REDIRECT_ACTION);

			}

		}

	}

	/**
	 * log in a user
	 * @api
	 * @param  array  $user all the user information
	 * @return boolean       if logged in
	 */
	public static function login($user=array())
	{

		// if there is user information
		if(!empty($user) && isset($user[AUTH_USERNAME_FIELD]) && isset($user[AUTH_PASSWORD_FIELD]))
		{

			// load the model
			$model = Core::instantiate(AUTH_TABLE);

			// we only want the user table nothing associatied to it
			$model->options = array("recursive"=>0);

			// create the method name using the username field
			$method = "findBy".ucfirst(AUTH_USERNAME_FIELD)."And".ucfirst(AUTH_PASSWORD_FIELD);

			// the user returned from the database
			$user_returned = $model->$method($user[AUTH_USERNAME_FIELD],Core::encrypt($user[AUTH_PASSWORD_FIELD]))[0];

			// if successfull, user is not empty, password returned equals the password passed
			if($model->success && !empty($user_returned)) {

				// get rie of the password field out of the user_returned
				unset($user_returned['User'][AUTH_PASSWORD_FIELD]);

				// set the session user
				Session::set('user',$user_returned['User']);

				// set that the user is logged in
				Session::set('logged_in',true);

				// return true so we know it worked
				return true;

			}

		}

		// return false because it isn't a correct user
		return false;

	}

	/**
	 * log out a user
	 * @api
	 */
	public static function logout()
	{

		// set logged in to false
		Session::set('logged_in',false);

	}

	/**
	 * get and set user information in the session
	 * @api
	 * @param  string $key   the key you want to get or set
	 * @param  object $value the value you want to set
	 * @return boolean/object        the key you got or set
	 */
	public static function user($key=NULL,$value=NULL)
	{

		// if there is no value
		if($value != NULL)
		{

			// get the user
			$user = Session::get('user');

			// set the key value
			$user[$key] = $value;

			// set the user
			Session::set('user',$user);

			// return the user
			return Session::get('user');

		}
		// if there is no key
		else if ($key != NULL)
		{

			// return the value of the key
			return isset(Session::get('user')[$key])?Session::get('user')[$key]:false;

		}
		// if there is no key or value
		else
		{

			// return the user
			return Session::get('user');

		}


	}
}