<?php
/**
 * Holds all the information for the database
 */

/**
 * This is all the database information
 *
 * Creates the pdo for the sql database
 *
 * @category   Core
 * @package    Core
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
class Core_Database {
	/**
	 * db
	 *
	 * The database PDO
	 *
	 * @var PDO
	 */
	public $db = NULL;
	/**
	 * tables: array
	 *
	 * the tables that are in the database
	 *
	 * @var tables
	 */
	public static $tables;

	/**
	 * Set up PDO
	 */
	public function __construct($DB_HOSTNAME=DB_HOSTNAME,$DB_NAME=DB_NAME,$DB_USERNAME=DB_USERNAME,$DB_PASSWORD=DB_PASSWORD) {
		$this -> db = new \PDO("mysql:hostname=".$DB_HOSTNAME.";dbname=".$DB_NAME,$DB_USERNAME,$DB_PASSWORD);
		$this -> db -> setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
		$this -> db -> setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);

	}
}