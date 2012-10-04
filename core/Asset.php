<?php

Class Asset {
	
	// used to set file paths for assets
	public static $paths;

	// default paths for assets
	private static $_paths = array(
		"css"=>"css/",
		"js"=>"js/",
		"img"=>"img/"
	);

	// get the base url so we know the root for assets
	public static function get_base()
	{
		// create base url variable
		$base_url = '';
		
		// see if host name is in the server variable
		if($_SERVER['HTTP_HOST'] && !$rel) {

			// add the protocol and the host name to the base url
			// TODO: make HTTP dynamic so it checks for https
			$base_url .= "http://".$_SERVER['HTTP_HOST'];
		}
		
		// see if script name is in the server variable
		if($_SERVER['SCRIPT_NAME']) {
			
			// add the directory to the base url
			$base_url .= str_replace('\\', '/', dirname($_SERVER["SCRIPT_NAME"]));
		}
		
		// make sure there is an ending slash and then return it
		return rtrim($base_url, '/').'/'; 
	}

	// used for routing to create urls to content dynamically
	// @controller = the controller you want to target
	// @action = the action you want to target
	// @params = the params you may want to pass
	public static function create_url($controller,$action='index',$params=array()) 
	{
		// split the url base on the "/"
		$urlArray = preg_split("/\//", self::get_base());

		// what is the length of the url array
		$length = count($urlArray);

		// remove the last section
		unset($urlArray[$length-1]);

		// remove the second to last section
		unset($urlArray[$length-2]);

		// put the url back together
		$url = implode("/", $urlArray);

		// create a string from the params separated by /
		$paramsURL = implode("/", $params);

		// create an array with the controller and action
		$array = array($controller,$action);

		// if the params is empty then do nothing if there is something then set it to the third index in the array 
		empty($params)?"":$array[2] = $params;

		// if the array is in the routes
		if(in_array($array, Settings::$routes)) {

			// loop through the routes 
			foreach (Settings::$routes as $key => $value) {

				// if the array equals the value
  				if($array == $value){

  					// set the controller to the key
     				$controller = $key;

     				// return the url plus "/" plus the controller
     				return $url."/".$controller;
  				}
  			}
		}

		// return the url all the information
		return $url."/".$controller."/".$action."/".$paramsURL;
	}

	public static function css($stylesheets,$attr=array()) 
	{
		// create a tag with a css type, the files and the global attributes 
		return self::create('css',$stylesheets,$attr);
	}

	public static function js($scripts,$attr=array())
	{
		// create a tag with a js type, the files and the global attributes
		return self::create('js',$scripts,$attr);
	}
	
	public static function img($imgs,$attr=array()) 
	{
		// create a tag with a img type, the files and the global attributes
		return self::create('img',$imgs,$attr);
	}
	
	// create a tag for each of the three types
	private static function create($type,$items,$attrs=array()) 
	{
		// empty string to hold the html string
		$html = '';

		// get the base url for the assets
		$path = self::get_base();

		// if the public variable for paths is set if not then use the default one to get the path for this type
		$path .= isset(self::$paths[$type])?self::$paths[$type]:self::$_paths[$type];

		// if items isn't an array make it one
		$items = is_array($items)?$items:array($items);

		// loop through items
		foreach($items as $item) {

			// if this item is an array then merge the second index with the attrs param if not then just use the attrs param
			$attr = is_array($item)?array_merge($attrs,$item[1]):$attrs;

			// if this item is an array then use the first index as the file name if isn't then just use the index 
			$file = is_array($item)?$item[0]:$item;

			// do different things based on the type
			switch ($type) {

				// if it is a css tag
				case 'css':

					// if the rel attribute is not set then set it to "stylesheet"
					$attr['rel'] = isset($attr['rel'])?$attr['rel']:"stylesheet";

					// if the type attribute is not set then set it to "text/css"
					$attr['type'] = isset($attr['type'])?$attr['type']:"text/css";

					// set the href to the path plus the file name plus ".css"
					$attr['href'] = $path.$file.'.css';

					// create a link tag and put it in the html
					$html .= self::_html_tag('link',$attr);

				break;

				// if it is a javascript tag
				case 'js':

					// if the type attribute is not set then set it to "text/javascript"
					$attr['type'] = isset($attr['type'])?$attr['type']:"text/javascript";

					// set the src to the path plus the file name plus ".js"
					$attr['src'] = $path.$file.'.js';

					// create a script tag and put in the html
					$html .= self::_html_tag('script',$attr," ");

				break;

				// if it is an image tag
				case 'img':

					// set the src to the path plus the file name
					$attr['src'] = $path.$file;

					// create a img tag and out it in the html
					$html .= self::_html_tag('img',$attr);

				break;
			}
		}

		// return the html
		return $html;
	}

	// create an html tag
	private function _html_tag($tag, $attr = array(), $content = false)
	{
		// if the tag has content or if it is self closing
		$has_content = (bool) ($content !== false and $content !== null);

		// start the html tag with a < and the tag name
		$html = '<'.$tag;

		// if the array of attributes is not empty then create a string and add it
		$html .= ( ! empty($attr))?' '.self::_array_to_attr($attr):'';

		// if it has content close the tag with > add the content and closing tag if it is self closing then close it with />
		$html .= $has_content ? '>'.$content.'</'.$tag.'>' : ' />';

		// return the html with and End Of Line At the end
		return $html.PHP_EOL;
	}

	// convert and array of attributes to a string
	private function _array_to_attr($attr) 
	{
		// string for the attribute
		$attr_str = '';
		
		// for each attr get the property and the value
		foreach($attr as $property=>$value) {

			// create a sting with property="value"
			$attr_str .= $property.'="'.$value.'" ';

		}

		// trim off extra space
		return trim($attr_str);
	}
	
	// json encode and echo out object for json view
	public static function json($object)
	{
		// echo out the json object encoded
		echo json_encode($object);
	}
}