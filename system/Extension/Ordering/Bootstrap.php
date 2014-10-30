<?php
	/**
	 * Initialize the Ordering extension
	 * @category Extensions
 	 * @package  Extensions
 	 * @subpackage Ordering
	 * @author     Rachel Higley <me@rachelhigley.com>
	 * @copyright  2013 Framework Simple
	 * @license    http://www.opensource.org/licenses/mit-license.php MIT
	 * @link       http://rachelhigley.com/framework
	 */

	Core::addNamespace('Extension_Ordering_Controller');

	// Hook::register("beforeDelete",array("Order","delete"));
	Hook::register("beforeFind",array("Order","find"));
  Hook::register("beforeSave",array("Order","save"));
?>