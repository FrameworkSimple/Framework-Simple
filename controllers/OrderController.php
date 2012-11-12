<?php

Class OrderController extends Controller {

	static public $template = "admin";

	// view all the orders
	public function admin()
	{
		self::$template = "admin";
	}

	// view orders to send to distribution 
	public function submit()
	{
		self::$template = "admin";
	}

	// send the orders to distribution
	public function admin_send()
	{

	}

	// review your order
	public function review()
	{
		self::$template = "site";
	}

	// order checkout 
	public function checkout()
	{
		self::$template = "site";
	}

	// send the order
	public function send()
	{

	}

}