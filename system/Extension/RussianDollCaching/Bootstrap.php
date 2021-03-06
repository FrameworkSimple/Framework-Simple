<?php
	/**
	 * Initialize the Russian Doll Caching extension
	 * @category Extensions
 	 * @package  Extensions
 	 * @subpackage RussianDollCaching
	 * @author     Rachel Higley <me@rachelhigley.com>
	 * @copyright  2013 Framework Simple
	 * @license    http://www.opensource.org/licenses/mit-license.php MIT
	 * @link       http://rachelhigley.com/framework
	 */

	Core::addNamespace('Extension_RussianDollCaching');

	// check that the cache is a directory and that we can write to it
	if(!is_dir(CACHE_PATH) || !is_writable(CACHE_PATH)) {

		// if we can't make the directory
		if(!mkdir(CACHE_PATH) || !is_writable(CACHE_PATH)){

			// trigger an error
			Core::error("Make sure RDCache folder exists and is writable");

		}

	}
	Hook::register("beforeAction",array("Caching","checkCache"));
?>