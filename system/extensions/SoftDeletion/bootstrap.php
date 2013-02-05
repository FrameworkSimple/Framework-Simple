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
	include "Settings.php";

	Core::add_classes("SoftDeletion",array(
		"SoftDeletion"     =>"controllers/SoftDeletion.php",
		)
	);

	Hook::register("before_delete",array("SoftDeletion","delete"));
	Hook::register("before_find",array("SoftDeletion","find"));
?>