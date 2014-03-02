<?php
/**
 * Handles everything having to do with assets
 */

/**
 * This handles the asset pipeline and loading in various assets
 * @category   Helpers
 * @package    Core
 * @subpackage Helpers
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */

Class Core_Helper_Asset {
	/**
	 * paths: array
	 *
	 * default paths for assets
	 *
	 * css => 'css/'
	 * js => 'js/'
	 * img => 'img/'
	 *
	 * @var array
	 */
	public static $paths = array(
		"css"=>"css/",
		"js"=>"js/",
		"img"=>"img/"
	);

	/**
	 * get the base url so we know the root for assets
	 * @api
	 * @return string the base url
	 */
	public static function getbase()
	{
		// create base url variable
		$base_url = '';

		// see if host name is in the server variable
		if($_SERVER['HTTP_HOST']) {

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

	/**
	 * Get the url path relative to the webroot
	 * @api
	 * @return string the relative url
	 */
	public static function relativeurl()
	{

		$url = str_replace('\\', '/', dirname($_SERVER["SCRIPT_NAME"]));

		return rtrim($url, '/').'/';

	}
	/**
	 * used for routing to create urls to content dynamically
	 * Asset::createUrl("controller","action",array(1));
	 * @api
	 * @param  string $controller the controller you want to target
	 * @param  string $action     the action you want to target
	 * @param  array  $params     the params you want to pass
	 * @return string             to controller/action/params
	 */
	public static function createurl($controller,$action='',$params=array())
	{
		// get the url
		$url = self::getBase();

		// create a string from the params separated by /
		$paramsURL = is_array($params)?implode("/", $params):$params;
		$array = array($controller,$action);
		$array_controller = array($controller);

		// if the array is in the routes
		if(in_array($array, Core::$routes) || in_array($array_controller, Core::$routes)) {

			// loop through the routes
			foreach (Core::$routes as $key => $value) {

				// if the array equals the value
  				if($array == $value || $array_controller == $value){

  					// set the url to the key
     				$route_url = $key;

     				// if the route is the root return the url
     				if($route_url == "/") return $url;

     				// index of the param
     				$current_param_index = 0;

     				// loop through the route pieces
     				foreach($route_array = explode("/", $route_url) as $index => $part)
     				{
     					// if it is suppose to be a param
     					if($part === ":num" || $part ===":any" )
     					{

     						// if a param is set for this route set the param
     						if(isset($params[$current_param_index]))
     						{
     							$route_array[$index] = $params[$current_param_index];

     							$current_param_index++;
     						}
     						// if the param isn't set then remove that part of the route
     						else
     						{
     							unset($route_array[$index]);
     						}

     					}
     					if($part === ":action")
     					{
     						if(!empty($action))
     						{
     							$route_array[$index] = $action;
     						}
     						else
     						{
     							unset($route_array[$index]);
     						}
     					}

     				}

     				// piece the route back together
     				$route_url = implode("/", $route_array);

     				// return the url plus "/" plus the controller
     				return $url.$route_url;
  				}
  			}
		}

		// add the controller
		$url .= $controller;

		// add the action if there is one
		if($action) $url .= "/".$action;

		// ad the params if there is any
		if($paramsURL) $url .= "/".$paramsURL;

		// return the url all the information
		return $url;
	}

	/**
	 * Create css link tags
	 * @api
	 * @param  array $stylesheets  names of stylesheets you want to include with any attributes
	 * @param  array  $attr        attributes you want applied to all stylesheets
	 * @return string              link tags to the stylesheets
	 */
	public static function css($stylesheets,$attr=array())
	{
		// create a tag with a css type, the files and the global attributes
		return self::create('css',$stylesheets,$attr);
	}

	/**
	 * create js script tags
	 * @api
	 * @param  array $scripts  names of scripts you want to include with any atrributes
	 * @param  array  $attr    attributes you want applied to all scripts
	 * @return string          script tags to the javascript files
	 */
	public static function js($scripts,$attr=array())
	{
		// create a tag with a js type, the files and the global attributes
		return self::create('js',$scripts,$attr);
	}

	/**
	 * create an image tag
	 * @api
	 * @param  string $imgs the name of the image you want to include
	 * @param  array  $attr attributes you want applied to the image tag
	 * @return string       img tag to the image
	 */
	public static function img($imgs,$attr=array())
	{
		// create a tag with a img type, the files and the global attributes
		return self::create('img',$imgs,$attr);
	}

	/**
	 * create an html tag for scripts, links and imgs.
	 * @param  string $type  type of tag you want to create
	 * @param  array $items the items you want created
	 * @param  array  $attrs attributes you want applied to all of the tags
	 * @return string        the tags that were created
	 */
	private static function create($type,$items,$attrs=array())
	{
		// empty string to hold the html string
		$html = '';

		// get the base url for the assets
		$path = self::getbase();

		// if the public variable for paths is set if not then use the default one to get the path for this type
		$path .= self::$paths[$type];

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
					$html .= self::_htmltag('link',$attr);

				break;

				// if it is a javascript tag
				case 'js':

					// if the type attribute is not set then set it to "text/javascript"
					$attr['type'] = isset($attr['type'])?$attr['type']:"text/javascript";

					// set the src to the path plus the file name plus ".js"
					$attr['src'] = $path.$file.'.js';

					// create a script tag and put in the html
					$html .= self::_htmltag('script',$attr," ");

				break;

				// if it is an image tag
				case 'img':

					// set the src to the path plus the file name
					$attr['src'] = $path.$file;

					// create a img tag and out it in the html
					$html .= self::_htmltag('img',$attr);

				break;
			}
		}

		// return the html
		return $html;
	}

	/**
	 * create an html tag
	 * @param  string  $tag     the tag you want to create
	 * @param  array   $attr    the attributes you want applied
	 * @param  boolean/string $content any content you want inside the tag, default = false
	 * @return string           the html tag
	 */
	private static function _htmltag($tag, $attr = array(), $content = false)
	{
		// if the tag has content or if it is self closing
		$has_content = (bool) ($content !== false and $content !== null);

		// start the html tag with a < and the tag name
		$html = '<'.$tag;

		// if the array of attributes is not empty then create a string and add it
		$html .= ( ! empty($attr))?' '.self::_arraytoattr($attr):'';

		// if it has content close the tag with > add the content and closing tag if it is self closing then close it with />
		$html .= $has_content ? '>'.$content.'</'.$tag.'>' : ' />';

		// return the html with and End Of Line At the end
		return $html.PHP_EOL;
	}

	/**
	 * convert and array of attributes to a string
	 * @param  array $attr an array of attributes
	 * @return string       a string of attributes
	 */
	private static function _arraytoattr($attr)
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

	/**
	 * json encode and echo out object for json view
	 * @api
	 * @param  array $object the array to encode
	 */
	public static function json($object,$echo=true)
	{

		// json encode the object
		$json = json_encode($object);

		// echo out the json object encoded
		if($echo) echo $json;

		// return the object
		return $json;
	}
}