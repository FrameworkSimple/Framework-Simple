<?php

Class Scafolding extends Database
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

			foreach ($result as $table) {

				$stmt = "SHOW CREATE TABLE ".$table;

				// prepare statement
				$stmt = $this->db->prepare($stmt);

				// if the execution works
				if($stmt->execute())
				{

					$statement = $stmt->fetchAll();

					$statement = str_replace("CREATE TABLE `", "", $statement[0]['Create Table']);

					array_push($tables, $statement);

				}


			}

			return $tables;

		}


	}

}