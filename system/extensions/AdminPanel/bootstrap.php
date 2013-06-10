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
		"AdminPanelController"			  =>"controllers/AdminPanelController.php",
		"AdminPanelUserController"		  =>"controllers/AdminPanelUserController.php",
		"AdminPanelSettingsController"	  =>"controllers/AdminPanelSettingsController.php",
		"AdminPanelScaffoldingController" =>"controllers/AdminPanelScaffoldingController.php",
		"AdminPanelMigrationsController"  =>"controllers/AdminPanelMigrationsController.php",
		"Tables"						  =>"models/Tables.php"
		)
	);

	define('ADMIN_DB',  dirname( __FILE__ ) . "/models/db.json");

?>