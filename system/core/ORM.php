<?php
/**
 * Holds all the ORM calls
 */

/**
 * This is the ORM. It allows you to use Object Relational Mapping to call find, save, and delete
 * @category   Core
 * @package    Core
 * @extends    Database
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
Class ORM extends Database {

	/**
	 * options: array
	 *
	 * the options you want to use for this call
	 * @var array
	 */
	public $options = array();

	/**
	 * _defaultOptions: array
	 *
	 * the default options that will be overwritten by $options
	 * @var array
	 */
	private $_defaultOptions = array(
			"recursive"=>3,
			"fields"=>array(),
			"limit"=>"",
			"addToEnd"=>"",
			"joins"=>array(),
			"where"=>array(),
			"returnSaved"=>false,
			"byCol"=>false,
			"orderBy"=> "",
			"key"=>array()
			);
	/**
	 * _name: string
	 *
	 * the name of this model
	 * @var string
	 */
	public $_name = "";

	/**
	 * _tables: array
	 *
	 * an array of all the tables we are using with their columns
	 * @var array
	 */
	private $_tables = array();

	/**
	 * _data: array
	 *
	 * the data we will be using for the call
	 * @var array
	 */
	private $_data = array();

	/**
	 * This is called whenever a call is made on this model
	 * @param  string $method the method that was called
	 * @param  object $value  the params that were passed
	 * @return object         the response we got from the database
	 */
	public function __call($method, $value)
	{
		$this->_name = get_called_class();

		// set all the options for this call
		$this->options = array_merge($this->_defaultOptions,$this->options);

		// set the default of success to true
		$this->success = true;

		// set blank variables
		$call = "";

		if(strstr($method,"findAll"))
		{
			// set the call to "_findAll"
			$call = "_find";
		}
		else if(strstr($method,"findBy"))
		{
			// remove "findBy" and then make the db version of the columns
			$cols = Core::to_db(str_replace("findBy","",$method));

			// seperate the columns into an array
			$cols = explode("_and_", $cols);

			// set the values for the columns
			$vals = $value;

			// loop over the columns
			foreach($cols as $index=>$col)
			{
				// push the column and the value to the where array
				$this->options['where']["$col"] = array($vals[$index],$this->_name);
			}
			// set the call to "_findBy"
			$call = "_find";
		}
		else if(strstr($method,"save"))
		{
			// data to save
			$this->_data = $value[0];

			// set the call to "_save"
			$call = "_".$method;
		}
		else if(strstr($method,"delete"))
		{
			// id to delete
			$this->_data = $value[0];

			// set tthe call to "_delete"
			$call = "_".$method;
		}

		// make the call to the function passing the data and save it to the response
		$response = $this->$call($this->_data);

		// reset all the options to the default
		$this->options = array_merge($this->options,$this->_defaultOptions);

		// clear all the data
		$this->_data = array();

		// clear all the hasManyTables
		$this->_hasManyTables = array();

		// return the response
		return $response;
	}

	/**
	 * searchs the database for information
	 * @return object the response from the database
	 */
	private function _find()
	{

		if(Hook::call("before_find", array(&$this)) === false) return;

		// set all the joins to be added
		$joins = $this->_setJoins();

		// create the select statement
		$select = $this->_createSelect();

		// create order by statement
		$order = $this->_createOrder();

		// the dabase name for this model
		$dbName = Core::to_db($this->_name);

		// create the where statement
		$where = $this->_createWhere();

		// create the limit statement
		$limit = $this->_createLimit();

		// create statement
		$statement = "SELECT $select FROM $dbName AS $this->_name $joins $where ".$this->options['addToEnd']." $order $limit";

		// send statement to the debugger
		array_push(Core::$debug['statements'],$statement);

		// prepare the statement
		$stmt = $this->db->prepare($statement);

		// run the statement
		if($stmt->execute($this->_data))
		{

			// get all the results
			$results = $stmt->fetchAll();

			// set up the return array for all results
			$return_results = array();

			// where we are in the $return_results
			$return_results_index = 0;

			// has many ids so that we can make sure we only get one of each type of data
			$ids = array();

			// the current results has_many index
			$current = array();

			// length of the results
			$length = count($results);

			// if there were no results
			if($length == 0){

				// set success to false
				$this->success = false;

				// set the error
				$this->error = array("msg"=>"No Results found","code"=>3);

				return;
			}

			// set up the return array for one result
			$current_result = array();

			// set up the previous id so that we can group rows together
			$prev_id = $this->options['recursive'] <= 1?false:$results[0][$this->_name.'$id'];

			// loop through the results
			foreach($results as $i=>$result)
			{

				// loop through this result
				foreach($result as $col=>$val)
				{
					// split the column name into the table and the column
					$info = preg_split("/[$]/",$col);
					$table = $info[0];
					$col = $info[1];

					// if there are no joins then don't worry about the table data because it is all from the same table
					if($this->options['recursive'] === 0)
					{
						//set the column
						$current_result[$col] = $val;

					}
					// if we have has many joins then we have to worry about multiple rows
					else
					{
						// if this col is the id
						if($col == "id")
						{

							if($prev_id !== false && $prev_id !== $result[$this->_name.'$id'])
							{

								// set the current result
								$return_results[$return_results_index] = $current_result;

								// reset the current_result
								$current_result = array();

								// increase the index
								$return_results_index++;

								// set the previous id
								$prev_id = $result[$this->_name.'$id'];

								// reset the ids
								$ids = array();

								// reset all the indexes
								foreach($current as $current_setting)
								{
									$current_setting['index'] = 0;
								}

							}
							// if the id doesn't have a value then don't set any of the contents
							if($val === NULL)
							{
								if($table === $this->_name)
								{
									$current[$table] = array("set"=>true);
								}
								else
								{
									$current[$table] = array("set"=>false);
								}
							}
							else
							{

								// if we don't have that table in the $has_many ids set it
								if(!isset($ids[$table]))
								{
									$ids[$table] = array($val);
									$current[$table] = array("set"=>true,"index"=>0,"id"=>$val);
									if(isset($this->options['key'][$table]))$current[$table]['index'] = $result[$table."$".$this->options['key'][$table]];

								}
								// if we already had this id stop set
								else if(in_array($val,$ids[$table]))
								{
									$current[$table]['set'] = false;
								}
								// if we haven't then set it to
								else
								{
									$current[$table]['set'] = true;
									$current[$table]['id'] = $val;
									$current[$table]['index']++;
									if(isset($this->options['key'][$table]))$current[$table]['index'] = $result[$table."$".$this->options['key'][$table]];
									array_push($ids[$table], $val);
								}

							}

						}

						// if the table isn't in the result
						if(!isset($current_result[$table]))
						{
							$current_result[$table] = array();
						}

						// if the key is set or if it is a has many and set is turned on then add this info
						if($current[$table]['set'] && ( isset($this->options['key'][$table]) || in_array($table, $this->hasMany) ) )
						{
							// if we haven't set up this index to have an array
							if(!isset($current_result[$table][$current[$table]['index']]))
							{
								$current_result[$table][$current[$table]['index']] = array();
							}
							// set the column
							$current_result[$table][$current[$table]['index']][$col] = $val;

						}
						// if it is not a has many table then it is just associative, only do this once per result
						else if($current[$table]['set'])
						{
							$current_result[$table][$col] = $val;
						}

					}

				}
				// if there are no previous ids or this the last row
				if($prev_id === false || $i === $length -1)
				{

					// set the current result
					$return_results[$return_results_index] = $current_result;

					// reset the current_result
					$current_result = array();

					// increase the index
					$return_results_index++;

				}


			}
			return $return_results;
		}
		// if the statement didn't work
		else
		{
			// set success equal to false
			$this->success = false;

			// check if we can get errorInfo
			if($stmt->errorInfo())
			{

				// get the errorinfo
				$info = $stmt->errorInfo();

				// set the error message and code
				$this->error = array("msg"=>$info[2],"code"=>3);

			}

			// if we can't get the error info
			else
			{

				// set the error message and code
				$this->error = array("msg"=>"There was an error in the call","code"=>3);

			}
		}

	}

	/**
	 * save information to the database
	 * @return object the id or the saved result
	 */
	private function _save() {

		// insert or update
		$insert = isset($this->_data['id'])?false:true;

		// valid is true by default
		$valid = true;

		// before validation run this function
		if(Hook::call("before_validation", array(&$this->_data,&$this->rules)) === false) return;

		// create the validtor
		$validator = new Validation();

		// set the database
		$validator->db = $this->db;

		// if insert
		if($insert)
		{

			// validate information
			$valid = $validator->validate($this->_name,$this->_data,$this->required,$this->rules);

		}
		else
		{
			$valid = $validator->validate($this->_name,$this->_data,$this->required,$this->rules,false);
		}

		if($valid === true) {

			// run the before save function
			if(Hook::call("before_save", array(&$this->_data,&$this)) === false) return;

			// set the database name
			$dbName = Core::to_db($this->_name);

			// set up the table
			$this->_setTable($dbName);

			// empty array to run the statement on
			$evaulate = array();

			// the first part of the insert statement
			$insertStmt1 = "INSERT INTO $dbName (";

			// the second part of the insert statement
			$insertStmt2 = ") VALUES (";

			// the first part of the update statement
			$updateStmt1 = "UPDATE $dbName SET ";

			// the second part of the update statement
			$updateStmt2 = " WHERE id=:id";

			// loop throught the data and make sure it belongs in this table and then add it to the statement
			foreach($this->_data as $col=>$val) {

				// check if the column is in this table
				if(isset(parent::$tables[$dbName][$col]))
				{

					// set the value into the array to be evaulated
					$evaulate[$col] = $val;

					// if it is a insert statement
					if($insert)
					{

						// set the column
						$insertStmt1 .= "$col, ";

						// set the value name
						$insertStmt2 .= ":$col, ";

					}
					// if it is a update
					else
					{

						// set the column equal to the value name
						$updateStmt1 .= "$col=:$col, ";

					}
				}
			}

			// remove the comma and space at the end
			$insertStmt1 = substr($insertStmt1,0,-2);

			// remove the comma and space at the end and add a closing parenthesis
			$insertStmt2 = substr($insertStmt2, 0, -2).")";

			// remove the comma and space at the end
			$updateStmt1 = substr($updateStmt1, 0,-2);

			// creat the statement
			$statement = $insert?$insertStmt1.$insertStmt2:$updateStmt1.$updateStmt2;

			// push the statement into the debug
			array_push(Core::$debug['statements'], $statement);

			// prepare the statement for the call
			$stmt = $this->db->prepare($statement);

			// if statement ran correctly
			if($stmt->execute($evaulate))
			{
				// if a row was saved
				if($stmt->rowCount() > 0)
				{

					// set success to true
					$this->success = true;

					// get the id of the inserted
					$id = $insert?$this->db->lastInsertId():$this->_data['id'];


					if($this->options['returnSaved'])
					{

						$this->_data = array();
						return call_user_func(array(get_called_class(),"findById"),$id);
					}

					// return the id
					return $id;
				}
				// if no rows were saved
				else
				{
					// set success to false
					$this->success = false;

					// set the error message and code
					$this->error = array("msg"=>"ID not found in database or nothing changed","code"=>3);
				}
			}
			// if the statement didn't run
			else
			{

				// set success equal to false
				$this->success = false;

				// check if we can get errorInfo
				if($stmt->errorInfo() && ($info=$stmt->errorInfo()) && $info[2])
				{

					// set the error message and code
					$this->error = array("msg"=>$info[2],"code"=>3);

				}

				// if we can't get the error info
				else
				{

					// set the error message and code
					$this->error = array("msg"=>"There was an error in the call","code"=>3);

				}
			}


		}
		// if data did not validate
		else
		{
			// set success to false
			$this->success = false;

			// set the error
			$this->error = array("msg"=>"Data did not pass validation", "code"=>2,"fields"=>$valid);

		}

	}

	/**
	 * delete information from the database
	 * @param  int $id the id you want to be deleted
	 */
	private function _delete($id)
	{

		// set the database name
		$dbName = Core::to_db($this->_name);

		// call the before delete function
		if(Hook::call("before_delete",array($id, $dbName, &$this)) === false) return;

		// create the delete statement
		$statement = "DELETE FROM $dbName where id = :id";

		// push the statement into the debug
		array_push(Core::$debug['statements'], $statement);

		// prepare the statement for the call
		$stmt = $this->db->prepare($statement);

		// if statement ran correctly
		if($stmt->execute(array('id'=>$id)))
		{
			// if a row was deleted
			if($stmt->rowCount() > 0)
			{

				// set success to true
				$this->success = true;

			}
			// if no rows were deleted
			else
			{
				// set success to false
				$this->success = false;

				// set the error message and code
				$this->error = array("msg"=>"ID not found in database","code"=>3);
			}
		}
		// if the statement didn't run
		else
		{

			// set success equal to false
			$this->success = false;

			// check if we can get errorInfo
			if($stmt->errorInfo())
			{

				// get the errorinfo
				$info = $stmt->errorInfo();

				// set the error message and code
				$this->error = array("msg"=>$info[2],"code"=>3);

			}

			// if we can't get the error info
			else
			{

				// set the error message and code
				$this->error = array("msg"=>"There was an error in the call","code"=>3);

			}
		}

	}

	/**
	 * create the select statement with all the fields
	 * @return string the select statement
	 */
	private function _createSelect()
	{
		// set the blank statement
		$selectStatement = "";



		foreach($this->_tables as $table)
		{
			// if no fields then get all the fields
			if(empty($this->options['fields']))
			{
				// set the table structure
				if($this->_setTable($table))
				{

					// loop through the parent tables
					foreach(parent::$tables[$table] as $col=>$val)
					{

						// create a select statement with an alias
						$selectStatement .= $table.".".$col." AS '".$table."$".$col."', ";

					}
				}

			}

			// only add the fields in the fields option
			else
			{

				// if the table is in the fields
				if(isset($this->options['fields'][$table]))
				{

					// loop through the table
					foreach($this->options['fields'][$table] as $col)
					{

						// create a select statement for each field with an alias
						$selectStatement .= $table.".".$col." AS '".$table."$".$col."', ";
					}
				}
			}
		}

		// remove the last comma and return the statement
		return substr($selectStatement,0,-2);
	}


	/**
	 * set the table structure in the $_tables
	 * @param string $table the table we need to set up
	 */
	private function _setTable($table)
	{

		// if we don't already have the table then get it
		if(!isset(parent::$tables[$table]))
		{
			// the database name
			$dbName = Core::to_db($table);

			// the statement to get all the columns
			$statement = "SHOW COLUMNS from ".$dbName;

			// push the statement into the debugger
			array_push(Core::$debug['statements'], $statement);

			// prepare statement
			$stmt = $this->db->prepare($statement);

			// if the execution works
			if($stmt->execute())
			{

				// get all the columns
				$result = $stmt->fetchAll();

				// create and empty array
				$tableArray = array();

				// loop through the results
				foreach($result as $col) {

					// set the column name to 0
					$tableArray[$col['Field']] = 0;

				}

				// set the temp array to the parent array
				parent::$tables[$table] = $tableArray;

				// everything worked and table was set up
				return true;
			}
			// if excute doesn't work
			else
			{
				// set success to false
				$this->success = false;

				// set the error message and code
				$this->error = array("msg"=>"Error getting the columns from the database","code"=>3);

				// something went wrong
				return false;
			}
		}

		return true;
	}


	/**
	 * set up all the joins in the options
	 */
	private function _setJoins()
	{
			// reverse the order so that later they will be the right order
			$this->options['joins'] = array_reverse($this->options['joins']);

			// if the recursive is 2 or 3 then push the tables for the has many in to the joins
			if($this->options['recursive'] >= 2 && !empty($this->hasMany))
			{
				// loop through each has many
				foreach($this->hasMany as $table)
				{

					// push the table into joins
					array_push($this->options['joins'], array($table,$this->_name));
				}

			}

			// if the recursive is 1 or 3 then pish the tables for the belongsTo into the joins
			if(($this->options['recursive'] == 3 || $this->options['recursive'] == 1) && !empty($this->belongsTo))
			{
				// loop thrugh each belongsTo
				foreach($this->belongsTo as $table)
				{
					// push the table into the joins
					array_push($this->options['joins'],array($this->_name,$table));
				}
			}

			// reverse the orde so that they go in the right order
			$this->options['joins'] = array_reverse($this->options['joins']);

			// call and retrun the createJoins function
			return $this->_createJoins();
	}

	/**
	 * create all the join statements
	 * @return string the join statement
	 */
	private function _createJoins()
	{
		// get all the joins
		$joins = $this->options['joins'];

		// set empty variable
		$statement = "";

		// an array of all the aliases that have already been added
		$this->_tables = array($this->_name);

		// loop through the joins
		foreach($joins as $tables)
		{
			// set the tables and their database names
			$table1 = $tables[0];
			$table2 = $tables[1];
			$direction = isset($tables[2])?$tables[2]:"LEFT";
			$dbTable1 = Core::to_db($table1);
			$dbTable2 = Core::to_db($table2);

			if(!in_array($table1, $this->hasMany) && $table1 != $this->_name)
			{
				array_push($this->hasMany, $table1);
			}
			if(!in_array($table2, $this->belongsTo))
			{
				array_push($this->belongsTo, $table2);
			}

			// if the alias is already created then use the other table
			if(in_array($table1, $this->_tables))
			{

				// create the join statement
				$statement .= " $direction JOIN $dbTable2 AS $table2 ON $table1.".$dbTable2."_id = $table2.id";

				// push the table into the alias
				array_push($this->_tables, $table2);
			}

			// else use the first table
			else
			{

				// create the join table
				$statement .= " $direction JOIN $dbTable1 AS $table1 ON $table1.".$dbTable2."_id = $table2.id";

				// push the table into the alias
				array_push($this->_tables, $table1);

			}
		}

		// return the statement
		return $statement;
	}

	/**
	 * create the where statement
	 * @return string the where statement
	 */
	private function _createWhere()
 	{
 		if(!empty($this->options['where'])) {
	 		// start where statement
	 		$where = "WHERE ";

	 		// loop through the where options
	 		foreach($this->options['where'] as $col=>$val)
	 		{

	 			// if there is no column name
	 			if(is_int($col)) {

	 				$where .= $val." AND ";

	 			}
	 			else {

	 				// set the column equal to the value for the excute
					$this->_data[$col] = $val[0];

					// if there is a table name
					if(isset($val[1])) $where .= $val[1].".";

	 				// set the col equal to the value
	 				$where .= "$col = :$col AND ";

	 			}



	 		}

	 		// remove the last AND and return the statement
	 		return substr($where,0,-4);
 		}
 		// if there are no wheres return an empty string
 		else {
 			return "";
 		}
 	}

 	/**
 	 * create a limit statement if needed
 	 * @return string the limit statement
 	 */
 	private function _createLimit()
 	{
 		// set the limit string
 		$limit = "";

		// if there is a limit
		if(!empty($this->options['limit']))  {

			// create the limit clause
			$limit = "LIMIT ".$this->options['limit'][0].", ".$this->options['limit'][1];

		}

		return $limit;
 	}

 	/**
 	 * create the order by statement
 	 * @return string the order by statement
 	 */
 	private function _createOrder()
 	{

 		// blank statement for order
 		$order = "ORDER BY ";

 		// if the options haven't been set
 		if(empty($this->options['orderBy']))
 		{
 			// set the order by statement
			$order .= "$this->_name.id";

		}
		// if it was set
		else
		{
			// set the table name
			$table = $this->options['orderBy'][0];

			// set the col name
			$col = $this->options['orderBy'][1];

			// set the direction to sort
			$direction = isset($this->options['orderBy'][2])?$this->options['orderBy'][2]:"DESC";

			// set the statement
			$order .= "$table.$col $direction";


		}
		return $order;
 	}
 }