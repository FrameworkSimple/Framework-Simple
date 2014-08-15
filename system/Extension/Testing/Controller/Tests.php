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
class Extension_Testing_Controller_Tests extends Controller {

	private $test_suite;

	static public $allowed_actions = array('run');

	private $_test_db = array();

	/**
	 * Run the tests
	 *
	 */
	public function run()
	{

		$this->test_suite = new TestSuite();
		$this->test_suite->testsuite('All Tests');

		Core::$redirect = false;

		$this->_addTests(SYSTEM_PATH.TESTS_FOLDER);

		if (TextReporter::incli()) {
		    exit ($this->test_suite->run(new TextReporter()) ? 0 : 1);
		}
		$this->test_suite->run(new HtmlReporter());

	}

	private function _addTests($folder)
	{
		$folder .= "/";
		$files = scandir($folder);


		foreach($files as $file)
		{

			if(strpos($file, ".php"))
			{
				$this->test_suite->addfile($folder.$file);
			}

			else if(is_dir($folder.$file) && $file !== "." && $file !== "..")
			{
				$this->_addTests($folder.$file);
			}

		}

	}

	public function find(&$model)
	{

		$this->_createTable($model);
	}

	public function save(&$data,&$model)
	{
		$this->_createTable($model);
	}

	public function delete($id, $name, &$model)
	{
		$this->_createTable($model);
	}

	private function _createTable(&$model)
	{

		if( Core::$info_of_url['controller'] == __CLASS__)
		{
			$stmt =  $model->db->prepare("CREATE DATABASE IF NOT EXISTS ".DB_NAME."_test");

			// run the statement
			if(!$stmt->execute())
			{
				return;
			}

			if(!isset($this->_test_db[$model->_name]))
			{
				$table_name = Utilities::toDb($model->_name);

				$this->_setTable($model, $table_name,$model->_name);

			}

			if($model->options['recursive'] >= 2)
			{
				foreach($model->has_many as $table)
				{

					if(!isset($this->_test_db[$table])) $this->_setTable($model,Utilities::toDb($table),$table);

				}

			}
			if($model->options['recursive'] === 1 || $model->options['recursive'] === 3)
			{
				foreach($model->belongs_to as $table)
				{

					if(!isset($this->_test_db[$table])) $this->_setTable($model,Utilities::toDb($table),$table);

				}

			}


			$db = new \PDO("mysql:hostname=".DB_HOSTNAME.";dbname=".DB_NAME."_test",DB_USERNAME,DB_PASSWORD);
			$db -> setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
			$db -> setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);

			$model->db = $db;


		}
	}

	private function _setTable($model, $table_name,$name)
	{

		$stmt = $model->db->prepare("DROP TABLE IF EXISTS ".DB_NAME."_test.".$table_name."; CREATE TABLE ".DB_NAME."_test.".$table_name." LIKE ".DB_NAME.".".$table_name);

		if($stmt->execute())
		{


			$stmt = $model->db->prepare("INSERT INTO ".DB_NAME."_test.".$table_name." SELECT * from ".DB_NAME.".".$table_name);

			if($stmt->execute())
			{

				$this->_test_db[$name] = "created";

			}

		}

	}
}