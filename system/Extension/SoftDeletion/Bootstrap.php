<?php
	/**
	 * Initialize the soft deletion extension
	 * @category Extensions
 	 * @package  Extensions
 	 * @subpackage SoftDeletion
	 * @author     Rachel Higley <me@rachelhigley.com>
	 * @copyright  2013 Framework Simple
	 * @license    http://www.opensource.org/licenses/mit-license.php MIT
	 * @link       http://rachelhigley.com/framework
	 */

	Core::addNamespace('Extension_SoftDeletion');

	Hook::register("beforeDelete",array("Delete","delete"));
	Hook::register("beforeFind",array("Delete","find"));
?>