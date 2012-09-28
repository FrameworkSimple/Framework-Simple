<?php
class Database {
	// the database
	protected $db = NULL;
	// the tables that are in the database
	public static $tables;	
	// set up PDO
	public function __construct() {
		$this -> db = new \PDO(Settings::$db['DSN'],Settings::$db['username'],Settings::$db['password']);
		$this -> db -> setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
		$this -> db -> setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);

	}
}