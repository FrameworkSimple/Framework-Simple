<?php
/**
 * Tables Model
 */

/**
 * Tables Model
 * @category Extensions
 * @package  Extensions
 * @subpackage AdminPanel
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
Class Extension_AdminPanel_Models_Tables extends Database
{

	public function getStatements()
	{
		$stmt = "SHOW TABLES";

		// prepare statement
		$stmt = $this->db->prepare($stmt);

		// if the execution works
		if($stmt->execute())
		{

			// get all the columns
			$result = $stmt->fetchall(PDO::FETCH_COLUMN, 0);

			$tables = array();

			foreach ($result as $table_name) {

				$stmt = "SHOW CREATE TABLE ".$table_name;

				// prepare statement
				$stmt = $this->db->prepare($stmt);

				// if the execution works
				if($stmt->execute())
				{

					$statement = $stmt->fetchall();

					$table = array(
						"name"=> $table_name,
						"structure"=> $statement[0]['Create Table']
					);

					$tables[$table_name] = $table;

				}


			}

			return $tables;

		}

	}

	public function runMigration($stmt)
	{
		// prepare statement
		$stmt = $this->db->prepare($stmt);

		// if the execution works
		return $stmt->execute();

	}

}