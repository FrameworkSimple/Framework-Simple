<?php
/**
 * The JWT User Controller
 */

/**
 * The JWT Extension controller
 * @category Extensions
 * @package  Extensions
 * @subpackage RussianDollCaching
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
Class Extension_JWT_Controller_JWTUser {

  private static $user;
  public static $token;

  public function before_action()
  {

    $headers = array('Authorization'=>false);

    if(function_exists('apache_request_headers')) $headers = apache_request_headers();
    $token = false;

    if(isset($headers['Authorization']) && $headers['Authorization'])
    {
      $token = $headers['Authorization'];
    }
    else
    {
      foreach ($headers as $header => $value) {
        if($header =="Authorization") $token = $value;
      }
    }

    if($token)
    {
      self::$token = str_replace('Bearer ', '', $token);

      self::$user = JWT::decode(self::$token,SALT);

      return Auth::isAuthorized(self::$user['logged_in']);
    }

    return Auth::isAuthorized(false);


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
      $model = Core::instantiate("Model_".Utilities::toCam(AUTH_TABLE));

      // we only want the user table nothing associatied to it
      $model->recursive = 0;

      // create the method name using the username field
      $method = "findBy".ucfirst(AUTH_USERNAME_FIELD)."And".ucfirst(AUTH_PASSWORD_FIELD);

      if(isset($user[AUTH_PASSWORD_FIELD])) {

        $user[AUTH_PASSWORD_FIELD] = Utilities::encrypt($user[AUTH_PASSWORD_FIELD]);
      }

      if(isset($user[AUTH_USERNAME_FIELD])) {

        $user[AUTH_USERNAME_FIELD] = strtolower($user[AUTH_USERNAME_FIELD]);

      }
      // the user returned from the database
      $user_returned = $model->$method($user[AUTH_USERNAME_FIELD],$user[AUTH_PASSWORD_FIELD]);
      $user_returned = $user_returned[0];

      // if successfull, user is not empty, password returned equals the password passed
      if($model->success && !empty($user_returned)) {

        // get rie of the password field out of the user_returned
        unset($user_returned[AUTH_PASSWORD_FIELD]);

        self::$user =  $user_returned;
        // set that the user is logged in
         self::$user['logged_in'] = true;

        // set the session user
        self::user(self::$user);

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

    self::$user = array();
    self::$user['logged_in'] = false;

    // set the session user
    self::user(self::$user);

  }


  public static function user($key=NULL,$value=NULL)
  {

    // if there is a value
    if($value != NULL)
    {

      // set the key value
      self::$user[$key] = $value;

      self::$token = JWT::encode(self::$user,SALT);

      // return the user
      return self::$user;

    }
    // if thee is a key
    else if ($key != NULL)
    {
      // return the value of the key if the key is a string
      if(is_string($key)) return isset(self::$user[$key])?self::$user[$key]:false;

      // else set the user
      self::$user = $key;

      self::$token = JWT::encode(self::$user,SALT);


    }
    // if there is no key or value
    else
    {
      // return the user
      return self::$user;

    }
  }

}

