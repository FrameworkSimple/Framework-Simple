<?php
session_start();
Class Authorization {
	
	public static $alwaysAllowActions = array();
	public static $allowActions = array();
	public static $table = "user";
	public static $usernameField = "email";
	public static $passwordField = "password";
	public static $redirectController = "home";
	public static $redirectAction = "index";
	public static $redirectParams = "";


	public function __construct() {
		self::$alwaysAllowActions = Settings::$auth['alwaysAllowActions'];
		self::$allowActions = array();
		self::$table = Settings::$auth['table'];
		self::$usernameField = Settings::$auth['usernameField'];
		self::$passwordField = Settings::$auth['passwordField'];
		self::$redirectController = Settings::$auth['redirectController'];
		self::$redirectAction = Settings::$auth['redirectAction'];
		self::$redirectParams = Settings::$auth['redirectParams'];
	}
	
	
	public static function isAuthorized() {
		$data = Core::getURL();
		if(!in_array($data['action'],self::$alwaysAllowActions) && !in_array($data['action'], self::$allowActions)) {
			if (isset($_SESSION['user'])) {
				return true;
			}
			Core::redirect(self::$redirectController,self::$redirectAction,self::$redirectParams);
		}else {
			return isset($_SESSION['user']);
		}
	}
	public static function authorize($user) {
		if(!empty($user)) {
			$model = Core::instantiate(self::$table);
			$model->options = array("recursive"=>0);
			$method = "findBy".ucfirst(self::$usernameField);
			$userReturn = $model->$method($user[self::$usernameField]);
			if($model->success && !empty($userReturn) && $userReturn[0]['User'][self::$passwordField]==Core::encript($user[self::$passwordField])) {
				unset($userReturn[0]['User'][self::$passwordField]);
				$_SESSION['user'] = $userReturn[0]['User'];
				return true;
			}else {
				return false;
			}
		}
	}
	public static function user($key=NULL,$value=NULL) {
		if($value != NULL) {
			$_SESSION['user'][$key] = $value;
			return $_SESSION['user'];
		}elseif($key) {
			return $_SESSION['user'][$key];
		}else {
			return $_SESSION['user'];
		}
	}
	public static function logout() {
		$user = array();
		unset($_SESSION['user']);
	}
}