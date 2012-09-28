<?php

Class Asset {
	// used to set file paths for assets
	public static $paths;
	private static $_paths = array(
		"css"=>"css/",
		"js"=>"js/",
		"img"=>"img/"
	);
	public static function get_base() {
		// create base url variable
		$base_url = '';
		// see if host name is in the server variable
		if($_SERVER['HTTP_HOST'] && !$rel) {

			// add the protocal and the host name to the base url
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
	public static function create_url($controller,$action,$params=array()) {
		$urlArray = preg_split("/\//", self::get_base());
		$length = count($urlArray);
		unset($urlArray[$length-1]);
		unset($urlArray[$length-2]);
		$url = implode("/", $urlArray);
		$paramsURL = implode("/", $params);
		$array = array($controller,$action);
		empty($params)?"":$array[2] = $params;
		if(in_array($array, Settings::$routes)) {
			foreach (Settings::$routes as $key => $value) {
  				if($array == $value){
     				$controller = $key;
     				return $url."/".$controller;
  				}
  			}
		}
		return $url."/".$controller."/".$action."/".$paramsURL;
	}
	public static function css($stylesheets,$attr=array()) {
		return self::create('css',$stylesheets,$attr);
	}
	public static function js($scripts,$attr=array()) {
		return self::create('js',$scripts,$attr);
	}
	public static function img($imgs,$attr=array()) {
		return self::create('img',$imgs,$attr);
	}
	private static function create($type,$items,$attrs=array()) {
		$html = '';
		$path = self::get_base();
		$path .= isset(self::$paths[$type])?self::$paths[$type]:self::$_paths[$type];
		$items = is_array($items)?$items:array($items);
		foreach($items as $item) {
			$attr = is_array($item)?array_merge($attrs,$item[1]):$attrs;
			$file = is_array($item)?$item[0]:$item;
			switch ($type) {
				case 'css':
					$attr['rel'] = isset($attr['rel'])?$attr['rel']:"stylesheet";
					$attr['type'] = isset($attr['type'])?$attr['type']:"text/css";
					$attr['href'] = $path.$file.'.css';
					$html .= self::_html_tag('link',$attr);
				break;
				case 'js':
					$attr['type'] = isset($attr['type'])?$attr['type']:"text/javascript";
					$attr['src'] = $path.$file.'.js';
					$html .= self::_html_tag('script',$attr," ");
				break;
				case 'img':
					$attr['src'] = $path.$file;
					$html .= self::_html_tag('img',$attr);
				break;
			}
		}
		return $html;
	}

	private function _html_tag($tag, $attr = array(), $content = false)
	{
		$has_content = (bool) ($content !== false and $content !== null);
		$html = '<'.$tag;

		$html .= ( ! empty($attr))?' '.self::_array_to_attr($attr):'';
		$html .= $has_content ? '>' : ' />';
		$html .= $has_content ? $content.'</'.$tag.'>' : '';

		return $html.PHP_EOL;
	}
	private function _array_to_attr($attr) {
		$attr_str = '';
		foreach($attr as $property=>$value) {
			$attr_str .= $property.'="'.$value.'" ';
		}
		return trim($attr_str);
	}
}