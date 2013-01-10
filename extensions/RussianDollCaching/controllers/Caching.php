<?php

Class Caching {

	// run when instantiated
	public function __construct()
	{
		// check that the cache is a directory and that we can write to it
		if(!is_dir("../RDCache") || !is_writable("../RDCache")) {

			// if we can't make the directory
			if(!mkdir("../RDCache") || !is_writable("../RDCache")){

				// trigger an error
				trigger_error("Make sure RDCache folder exists and is writable");

			}

		}

	}

	// render a current version of the
	public static function render($id,$controller)
	{

		// path to view
		$file_name= "../RDCache/".$controller::$controller_name."/".$controller::$view_name."-$id.html";

		// get the cached page
		$view = View::get_contents($file_name);

		// if the view doesn't exist
		if(!$view){

			return false;

		}
		// if the view does exist
		else
		{
			// render out the view
			echo $view;

			return true;
		}

	}

	// create the cached view after logic has been run
	public static function create($id,$new_view)
	{


		var_dump($new_view[1]);

		// if the directory doesn't exist
		if(!is_dir("../RDCache/".$file))
		{

			// create the directory
			mkdir("../RDCache/".$controller::$controller_name);

		}

		// the path to the cached view
		$file_name= "../RDCache/".$controller::$controller_name."/".$controller::$view_name."-$id.html";

		// create the file from the view
		file_put_contents($file_name,$new_view,LOCK_EX);

	}

	// get partials for this item
	public function get($ids)
	{

	}
}