<?php
/**
 * The Testing Controller
 */
/**
 * The Testing Extension controller
 * @category Extensions
 * @package  Extensions
 * @subpackage Testing
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */

require_once("../simpletest/autorun.php");
class TestsController  extends Controller {

	/**
	 * Run the tests
	 *
	 */
	public function run()
	{

		$test_suite = new TestSuite();
		$test_suite->TestSuite('All Tests');
		$files = scandir(SYSTEM_PATH.'/extensions/Testing/tests');

		foreach($files as $file)
		{
			if(strpos($file, ".php"))
			{
				$test_suite->addFile(SYSTEM_PATH.'/extensions/Testing/tests/'.$file);
			}
		}

	}
}