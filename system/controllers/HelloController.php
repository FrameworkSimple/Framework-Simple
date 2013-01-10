<?php

class HelloController extends Controller {

	public function before_action(){

		parent::before_action();

		Caching::$id = 1;

	}

	public function index()
	{

		echo "logic time<br />";

	}
}