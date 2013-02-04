<?php

// class for helping render views
Class View
{

	// render a view
	public static function render($file,$data=array(),$layout=FALSE,$layoutInfo=array())
	{

		// call the before_render hook and if it returns false then stop the function
		if(Hooks::call("before_render") === false) return;

		// check to see if it is an index array
		$indexed = !self::_is_assoc($data);

		// set the file path of the view
		$file_path = SYSTEM_PATH."/views/".$file.".php";

		if(!is_file($file_path))
		{

			trigger_error("404: View: ".$file." Not Found",E_USER_ERROR);
			return;
		}

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

				$id = isset($snippet['id'])?$snippet['id']:'';

				Hooks::call("after_render",$view_data,$file,$id);

			}

			echo $view;

			return $view;

		}
		// if the data is not an indexed array
		else
		{
			//split the name
			$split = preg_split("/[.]/", $file);

			// if there is an extension
			$ext = isset($filename[1]);

			// if there is an extension and it is json
			if($ext && $filename[1] === 'json')
			{

				// render the json object using the data
				Asset::json($data);

				return Asset::json($data);

			}
			// TODO: add more file ext types (xml, html, etc.)
			// render the page normally
			else
			{
				// get the content
				$view = self::get_contents($file_path,$data,$root);

				// if there is a layout file and layouts are on
				if($layout && LAYOUTS)
				{

					// layout file path
					$layout_path = SYSTEM_PATH."/views/layouts/".$layout.".php";

					if(!is_file($layout_path))
					{
						trigger_error("404: Layout File: ".$layout." Not Found",E_USER_ERROR);
						return;
					}

					if(DEBUG)
					{

						array_push(Core::$debug['views'],$layout_path);
					}

					// get the whole page including layout
					$view = self::get_contents($layout_path,$layoutInfo,$root,$view);
				}

				$id = isset($data['id'])?$data['id']:'';

				Hooks::call("after_render",$view,$file,$data);

				// render out the view
				echo $view;

				// return the text
				return $view;
			}
		}

	}

	// check to see if array is associative or indexed
	private static function _is_assoc($array)
	{
		return array_keys($array) !== range(0, count($array) - 1);
	}

	// get the contents of a file
	public static function get_contents($filename, $data=NULL,$root=NULL, $content_for_layout=NULL) {

		// if there is data
		if(is_array($data) && self::_is_assoc($data)) {

			// set the key value pairs to variables with the name of the key
			extract($data);

		}

    	// start the output buffer
        ob_start();

        // include the file
        include $filename;

        // return a clean stream
        return ob_get_clean();

	}
}