<?php
	/**
	 * Initialize the testing extension
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
		"Enhance\Core" => "controllers/EnhanceTestFramework.php",
		"\Enhance\TestFixture" => "controllers/EnhanceTestFramework.php",
		"\Enhance\MockFactory" => "controllers/EnhanceTestFramework.php",
		"\Enhance\StubFactory" => "controllers/EnhanceTestFramework.php",
		"\Enhance\Expect" => "controllers/EnhanceTestFramework.php",
		"\Enhance\Assert" => "controllers/EnhanceTestFramework.php",

		)
	);




	var_dump(shell_exec("wget http://codeception.com/codecept.phar; php codecept.phar bootstrap"));


//	echo SYSTEM_PATH."/extensions/Testing/codecept.phar bootstrap"

?>