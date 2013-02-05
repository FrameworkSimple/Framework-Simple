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
class TestsController  extends Controller {

	/**
	 * Run the tests
	 * @return [type] [description]
	 */
	public function run()
	{
		\Enhance\Core::discoverTests(SYSTEM_PATH."/extensions/Testing/tests", false);

		\Enhance\Core::runTests();
	}
}