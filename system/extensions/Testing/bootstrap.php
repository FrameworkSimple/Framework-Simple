<?php
	/**
	 * Initialize the testing extension
	 *
	 * This testing suite uses Simple Test (http://www.simpletest.org/)
	 * Put all testing in the /tests/ package to be used
	 *
	 * @category Extensions
 	 * @package  Extensions
 	 * @subpackage Testing
	 * @author     Rachel Higley <me@rachelhigley.com>
	 * @copyright  2013 Framework Simple
	 * @license    http://www.opensource.org/licenses/mit-license.php MIT
	 * @link       http://rachelhigley.com/framework
	 */
	Core::add_classes("Testing",array(
		"TestsController"     =>"controllers/TestsController.php",
		"SimpleBrowser"       =>"simpletest/Browser.php",
		"TestSuite"			  =>"simpletest/test_case.php",
		"UnitTestCase"        =>"simpletest/unit_tester.php"
		)
	);

	Hook::register("before_find",array("TestsController","find"));
	Hook::register("before_save",array("TestsController","save"));
	Hook::register("before_delete",array("TestsController","delete"));

?>