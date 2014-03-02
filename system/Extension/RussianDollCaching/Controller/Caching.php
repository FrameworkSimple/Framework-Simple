<?php
/**
 * The Caching Controller
 */

/**
 * The Russian Doll Caching Extension controller
 * @category Extensions
 * @package  Extensions
 * @subpackage RussianDollCaching
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
Class Extension_RussianDollCaching_Controller_Caching {

	/**
	 * id: int
	 *
	 * id to use to create cached version
	 *
	 * @var string
	 */
	public static $id="";

	/**
	 * create the cached view after logic has been run
	 * @param  string $view      rendered view
	 * @param  string $file_name name of the file
	 * @param  array $data      data for the view
	 */
	public static function create($view,$file_name,$data="")
	{

		$id = empty($data) && !isset($data['id'])?self::$id:$data['id'];

		$file_name = explode("/", $file_name);

		// if the directory doesn't exist
		if(!is_dir(CACHE_PATH.$file_name[0]))
		{

			// create the directory
			mkdir(CACHE_PATH.$file_name[0]);

		}

		// the path to the cached view
		$file_name= CACHE_PATH.$file_name[0]."/".$file_name[1]."-".$id.".html";

		// create the file from the view
		file_put_contents($file_name,$view,LOCK_EX);

	}

	/**
	 * check the cache to see if the file exists
	 * @param  object $controller the controller to check
	 * @param  int $id         the id to look for
	 * @return boolean             if cached was found stop the render
	 */
	public function checkCache($controller, $id="")
	{

		// if there is no id return true to continue running
		if (empty(self::$id) && empty($id)) return true;

		// if an id was passed use that
		if(!empty($id)) self::$id = $id;

		// path to view
		$file_name= CACHE_PATH.$controller::$controller_name."/".$controller::$view_name."-".self::$id.".html";

		// get the cached page and check if it does exist
		if($view = View::GetContents($file_name))
		{

			// echo out the view
			echo $view;

			// clear out the id for the next check
			self::$id = "";

			// stop the rest of the logic from happening
			return false;
		}

		// continue with the logic
		return true;


	}

}