<?php

Class Tables extends Database
{

	public function get_statements()
	{
		$stmt = "SHOW TABLES";

		// prepare statement
		$stmt = $this->db->prepare($stmt);

		// if the execution works
		if($stmt->execute())
		{

			// get all the columns
			$result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

			$tables = array();

			foreach ($result as $table_name) {

				$stmt = "SHOW CREATE TABLE ".$table_name;

				// prepare statement
				$stmt = $this->db->prepare($stmt);

				// if the execution works
				if($stmt->execute())
				{

					$statement = $stmt->fetchAll();

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

	public function run_migration($stmt)
	{
		// prepare statement
		$stmt = $this->db->prepare($stmt);

		// if the execution works
		return $stmt->execute();

	}

}