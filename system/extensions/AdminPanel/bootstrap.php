<?php
	/**
	 * Initialize the Admin Panel extension
	 * @category Extensions
 	 * @package  Extensions
 	 * @subpackage AdminPanel
	 * @author     Rachel Higley <me@rachelhigley.com>
	 * @copyright  2013 Framework Simple
	 * @license    http://www.opensource.org/licenses/mit-license.php MIT
	 * @link       http://rachelhigley.com/framework
	 */
	include "Settings.php";

	Core::add_classes("AdminPanel",array(
		"AdminPanel"     =>"controllers/AdminPanelController.php",
		)
	);

?>