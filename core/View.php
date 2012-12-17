<?php

// class for helping render views
Class View 
{

	// render a view
	public static function render($file,$data=array(),$template=FALSE,$templateInfo=array())
	{

		// check to see if it is an index array
		$indexed = !self::_is_assoc($data);

		// set the file path of the view
		$file_path = PATH_TO_APP."views/".$file.".php";

		if(DEBUG) {

			array_push(Core::$debug['views'],$file_path);
		}
		// set the root url
		$root = Asset::get_base();

		// if the data is an indexed array
		if($indexed)
		{
			// create blank string for view
			$view = "";

			// loop through each piece
			foreach($data as $snippet)
			{

				// render the content for this piece
				$view_data = self::get_contents($file_path,$snippet,$root);

				// if the file exists then add it to the string
				$view .= $view_data?$view_data:'';
			}

			

		}
		// if the data is not an indexed array
		else
		{
			// get the content
			$view = self::get_contents($file_path,$data,$root);
		}
	
		// if we got a view
		if($view)
		{

			// if there is a template file and templates are on
			if($template && TEMPLATES)
			{

				// template file path
				$template_path = PATH_TO_APP."views/templates/".$template.".php";

				if(DEBUG) {

					array_push(Core::$debug['views'],$template_path);
				}

				// get the whole page including template
				$template = self::get_contents($template_path,$templateInfo,$root,$view);

				// render out the template
				echo $template;

			}
			// if there isn't a template
			else 
			{


				// render out the view
				echo $view;

			}

		}
		// if a view file didn't exist
		else
		{

			//split the name 
			$split = preg_split("/[.]/", $file);

			// if there is an extension
			$ext = isset($filename[1]);

			// if there is an extension like json or xml
			if($ext && $filename[1] === 'json')
			{

				// render the json object using the data
				Asset::json($data);

			}
			else
			{

				// TODO: Add 404 Error Handling
				echo "404 Error: View File Didn't Exist <br />";
				echo $file_path;
				
			}

		}
	}

	// check to see if array is assocative or indexed
	private static function _is_assoc($array)
	{
		return array_keys($array) !== range(0, count($array) - 1);
	}

	// get the contents of a file
	public static function get_contents($filename, $data=NULL,$root=NULL, $content_for_layout=NULL) {
	    if (is_file($filename)) {
	        ob_start();
	        include $filename;
	        return ob_get_clean();
	    }
	    return false;
	}
}