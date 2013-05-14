<?php
/**
 * Handles everything to do with rendering views
 */

/**
 * Allows you to render views
 *
 * @category   Helpers
 * @package    Core
 * @subpackage Helpers
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
Class View
{

	/**
	 * Render a view
	 * @api
	 * @param  string  $file       file you want to render
	 * @param  array   $data       data you want to be passed to the view
	 * @param  array   $options    all the options for rendering the view: layout, layout_info, render, and path_to_views
	 * @return string              the view that was rendered
	 */
	public static function render($file,$data=array(),$options = array())
	{

		$default_options = array(
			"layout"=>FALSE,
			"layout_info"=>array(),
			"render"=>TRUE,
			"path_to_views"=>"/views/"
		);

		$options = array_merge($default_options,$options);

		// call the before_render hook and if it returns false then stop the function
		if(Hook::call("before_render") === false) return;

		// check to see if it is an index array
		$indexed = !self::_is_assoc($data);

		// set the file path of the view
		$file_path = SYSTEM_PATH.$options['path_to_views'].$file.".php";

		//split the name
		$split = preg_split("/[.]/", $file);

		// if there is an extension
		$ext = isset($split[1]);

		if(!is_file($file_path))
		{

			if($ext)
			{
				switch ($ext) {
					case 'json':
						// render the json object using the data
						$json = Asset::json($data,false);

						if($options['render']) echo $json;

						return $json;

					break;

					//TODO: Add other extension types
				}

			}
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

				Hook::call("after_render",$view_data,$file,$id);

			}

			if($options['render']) echo $view;

			return $view;

		}
		// if the data is not an indexed array
		else
		{
			// get the content
			$view = self::get_contents($file_path,$data,$root);

			// if there is a layout file and layouts are on
			if($options['layout'] && LAYOUTS)
			{

				// layout file path
				$layout_path = SYSTEM_PATH.$options['path_to_views']."layouts/".$options['layout'].".php";

				if(!is_file($layout_path))
				{
					trigger_error("404: Layout File: ".$options['layout']." Not Found",E_USER_ERROR);
					return;
				}

				if(DEBUG)
				{

					array_push(Core::$debug['views'],$layout_path);
				}

				// get the whole page including layout
				$view = self::get_contents($layout_path,$options['layout_info'],$root,$view);
			}

			$id = isset($data['id'])?$data['id']:'';

			Hook::call("after_render",$view,$file,$data);

			// render out the view
			if($options['render']) echo $view;

			// return the text
			return $view;
		}

	}

	/**
	 * check to see if array is associative or indexed
	 * @param  array  $array the array you want to check on
	 * @return boolean        if the array is associative
	 */
	private static function _is_assoc($array)
	{
		if(is_array($array))return array_keys($array) !== range(0, count($array) - 1);
		return false;
	}

	/**
	 * get the contents of a file
	 * @param  string $filename           the name of the file
	 * @param  array $data                the data you want the view to have
	 * @param  string $root               the path to the root
	 * @param  string $content_for_layout the string of the view if rendering a layout
	 * @return string                  	  the content of the file
	 */
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