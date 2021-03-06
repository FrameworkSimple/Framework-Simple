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

	Core::addNamespace('Extension_Testing');

	Hook::register("beforeFind",array("Tests","find"));
	Hook::register("beforeSave",array("Tests","save"));
	Hook::register("beforeDelete",array("Tests","delete"));

?>