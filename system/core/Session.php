<?php

class Session {
	

	public static function set($key,$value)
	{

		// set a session variable 
		$_SESSION[$key] = $value;

	}

	public static function get($key)
	{

		// get a session variable
		return $_SESSION[$key];

	}

	public static function remove($key)
	{

		// unset session variable
		unset($_SESSION[$key]);

	}

	public static function check($key)
	{

		// isset session variable
		return isset($_SESSION[$key]);
	}
}