<?php

Class StoreController extends Controller {

	static public $template = "site";

	public function index() {

	}

	public function admin() {
		self::$template = "admin";
	}
}
