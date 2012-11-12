<?php

Class UserController extends Controller {

	static public $template = false;

	public function login() {

	}

	public function settings() {

		self::$template = 'admin';
	}

	public function logout() {

		Core::redirect("store","index");
	}
}