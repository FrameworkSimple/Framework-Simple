<?php
class Database {
	// the database
	public $db = NULL;
	// the tables that are in the database
	public static $tables;
	// set up PDO
	public function __construct() {
		$this -> db = new \PDO(DB_DSN,DB_USERNAME,DB_PASSWORD);
		$this -> db -> setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
		$this -> db -> setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);

	}
}