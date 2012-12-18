<?php
class Model extends ORM {
	// if you want to include joins
	public $recursive = 2;
	// tables to join to
	public $belongsTo = "";
	// the name of the table in the database
	public $tableName;
	// all the fields to validate
	public $validate;
	// all the required fields
	public $required;
	// the options for each method
	public $options = array();
	// tables to joing on
	public $hasMany = "";
	// on save return the object that you saved
	public $returnSave = false;
	// custom where statement
	public $where = "";

	public static $success = false;
	public static $error = array();

	// the variables to use within this scope

	// if you want to include joins
	private $_recursive;
	// tables to join to
	private $_belongsTo = "";
	// the name of the table in the database
	private $_tableName;
	// all the fields to validate
	private $_validate;
	// all the required fields
	private $_required;
	// the options for each method
	private $_options = array();
	// the name of the alias
	private $_name;
	// the validation class
	private $_validator;
	// the thing to select
	private $_selectStatement = "";
	// the limit to put on the select
	private $_limit;
	// tables to join on 
	private $_hasMany = "";
	// on save return the object that you saved
	private $_returnSave;
	// you can add sql to the end of the statement
	private $_addToEnd = "";
	// set the custom where statement
	private $_where = "";
	// set the name and table name of the model
	public function __construct() {
		parent::__construct();
		$this->_name = get_called_class();
		if($this->tableName == NULL) {
			$this->_tableName = Core::toDB($this->_name);
		}
		else {
			$this->_tableName = $this->tableName;
		}
		$this->_validator = Core::instantiate("Validation");
		$this->_validator->db = $this->db;
		
	}
	public function __call($method, $value) {
		$data = array();
		$options = NULL;
		// if the call is to the findBy method do the following
		if(strstr($method,"findBy")) {
			// get the column by replacing the findBy
			$data['cols'] = Core::toDB(str_replace("findBy","",$method));
			$data['cols'] = explode("_and_", $data['cols']);
			// argument in the method
			$data['vals'] = $value[0];
			if(isset($value[1])) {
				$options = $value[1];
			}
			
			$method = 'findBy';
		}elseif(strstr($method,"findAll")) {
			if(isset($value[0])) {
				$options = $value[0];
			}
		}elseif(strstr($method,"save")) {
			$data = $value[0];
			if(isset($value[1])) {
				$options= $value[1];
			}
		}elseif(strstr($method,"delete")) {
			$data['col'] = $value[0];
			$data['val'] = $value[1];
			if(isset($value[2])) {
				$options= $value[2];
			}
		}
		$this->_set();
		if($options != NULL) {
			$this->_options = array_merge($this->options,$options);
		}
		if(!empty($this->_options)) {
			$this->_options();
		}
		
		$method = "_".$method;
		$return = $this->$method($data);
		$this->_clear();
		return $return;
	}
	private function _set() {
		$this->_required = $this->required;
		$this->_validate = $this->validate;
		$this->_belongsTo = $this->belongsTo;
		$this->_hasMany = $this->hasMany;
		$this->_recursive = $this->recursive;
		$this->_returnSave = $this->returnSave;
		$this->_where = $this->where;
		self::$success = false;
		self::$error = array();
		if(!empty($this->tableName)) {
			$this->_tableName = $this->tableName;
		}
		$this->_options = $this->options;
		
	}
	private function _clear() {
		$this->_required = array();
		$this->_validate = array();
		$this->_belongsTo = array();
		$this->_hasMany = array();
		$this->_recursive = 1;
		$this->_returnSave = false;
		$this->_where = $this->where;
		if(!empty($this->tableName)) {
			$this->_tableName = $this->tableName;
		}
		$this->_selectStatement = "";
		$this->_options = array();
		$this->_limit = "";
	}
	private function _options() {
		foreach ($this->_options as $key=>$value) {
			switch ($key) {
				case 'fields':
					$selectStatement = "";
					foreach($value as $name=>$data) {
						$tmp = array();
						if(gettype($data)=="array") {
							foreach($data as $num=>$col) {
								$tmp[$col]=0;
							}
							$selectStatement = $selectStatement.$this->_createSelect($name,$tmp);
						}else {
							$tmp[$data] = 0;
							$selectStatement = $selectStatement.$this->_createSelect($this->_name,$tmp);
						}
					}
					$selectStatement = substr($selectStatement,0,-2);
					$this->_selectStatement = $selectStatement;
				break;
				case 'recursive':
					$this->_recursive = $value;
				break;
				case 'limit':
					$limits = $value;
					$stmt = "LIMIT ".$limits[0].", ".$limits[1];
					$this->_limit = $stmt;
				break;
				case 'required':
					$this->_required = $value;
				break;
				case 'validate':
					$this->_validate = $value;
				break;
				case 'belongsTo':
					$this->_belongsTo = $value;
				break;
				case 'tableName':
					$this->_tableName = $value;
				break;
				case 'addToEnd':
					$this->_addToEnd = $value;
				case 'where':
					$this->_where = $value;
				break;
			}

		}
	}
	// get all the data from the table
	public function _findAll($data) {		
		if(empty($this->_selectStatement)) {
			// get the columns for the select statement
			$this->_selectStatement = $this->_setColumns();
		}
		$joins = $this->_addJoins();
		// create the select statement with the columns, tablename and alias
		$txt = "SELECT ".$this->_selectStatement." from ".$this->_tableName." AS ".$this->_name.$joins." ".$this->_addToEnd." ".$this->_where.$this->_limit;
		// prepart the statement for PDO
		$return = $this->_getData($txt);
		if(count($return)>0 && !isset($return['success'])) {
			self::$success = true;
		}elseif(count($return)==0) {
			self::$success = false;
			self::$error = array("code"=>4,"message"=>"No Results Found");
		}
		// return the return array
		return $return;
	}
	private function _findBy($data) {
		if(empty($this->_selectStatement)) {
			// get the columns for the select statement
			$this->_selectStatement = $this->_setColumns();
		}
		$joins = $this->_addJoins();
		$where = "WHERE";
		$data['values'] = array();
		foreach($data['cols'] as $index=>$col) {
			$where .= " $this->_name.$col = :$col AND";
			if(is_array($data['vals'])) {
				$data['values'][$col] = $data['vals'][$index];
			}else {
				$data['values'][$col] = $data['vals'];
			}
		}
		$where = substr($where, 0,-4);
		// create the select statement
		$txt = "SELECT ".$this->_selectStatement." from ".$this->_tableName." AS ".$this->_name.$joins." ".$this->_addToEnd." ".$where." ".$this->_limit;
		// get the data for that statement
		$return = $this->_getData($txt,$data['values']);
		if(count($return)>0 && !isset($return['success'])) {
			self::$success = true;
		}elseif(count($return)==0) {
			self::$success = false;
			self::$error = array("code"=>4,"message"=>"No Results Found");
		}
		// return the return array
		return $return;
	}
	// insert or update the the data that is passed
	public function _save($data) {
			// set validator information
			$this->_validator->validate = $this->_validate;
			$valid = true;
			// set the return to be an array
			$return = array();		

			// for each array in the data
			foreach ($data as $name => $value) {
				// if the value is an array and the name is not the same as this model then do the following
				if(gettype($value)=="array" && $name != $this->_name) {

					// create the table name
					$tblName = Core::toDB($name);
					// run the insert function and add the return to the return arrray
					$return[$name] = $this->_insert($name, $tblName,$value);
					// set the tablename underscore id in the table that relates to this model with the id that was just added
					$data[$this->_name][$tblName."_id"] = $return[$name]["id"];
				}elseif(gettype($value)!="array") {
					unset($data[$name]);
					$data[$this->_name][$name]= $value;
				}
			}
			if(count($data)==1) {
				$return = $this->_insert($this->_name,$this->_tableName,$data[$this->_name]);
			}else {
				$return[$this->_name] = $this->_insert($this->_name,$this->_tableName,$data[$this->_name]);	
			}
			
			// run the insert function the on the data for this model
			
			// return the return array
			return $return;		
	}
	// delete from database
	public function _delete($data) {
		$stmt = "DELETE FROM $this->_tableName where ${data['col']} = :${data['col']}";
		array_push(Core::$debug['statements'], $stmt);
		$stmt = $this -> db -> prepare($stmt);
		
		if($stmt -> execute(array(":${data['col']}" => $data['val']))) {
			if($stmt->rowCount()>0) {
				self::$success = true;
			}else {
				self::$success = false;
				self::$error = array("code"=>4,"message"=>"id not found");
			}
		}else {
			$errorInfo = $stmt->errorInfo();
			self::$success = false;
			self::$error = array("code"=>4,"message"=>$errorInfo[2]);
		}
	}
	// run this function before validation;
	public function beforeValidate($value) {
		return $value;
	}

	// run this function before save but after validation 
	public function beforeSave($value) {
		return $value;
	}
	// insert or update the information
	private function _insert($name, $tblName,$value) {
		// create the columns variable
		$cols = "";
		// create the values veriable
		$vals = "";
		// get the columns that relate to this table
		$realCols = $this->_getColumns($name);
		// the things to pass through the execute function
		$inserts = array();
		// check to see if there is an id, if so then update if not then insert
		$insert = !isset($value['id']);
		// the list of things to update
		$updateList = "";
		// for each item in the value arguement
		foreach ($value as $col => $val) {
			// if the column in a real column related to the table then do the following
			if(isset($realCols[$col])) {
				// if insert then set up the insert list else set up the update list
				if($insert) {
					$cols = $cols.$col.', ';
					$vals =  $vals.':'.$col.', ';
				}else {

					$updateList = $updateList.$col."= :".$col.", ";
				}
				// add to the array of things to be passed through the execute function
				$inserts[$col] = $val; 
				
			}
		}
		// if insert then create the insert stament else create the update statement
		if($insert) {
			if($tblName == $this->_tableName) {
				$this->_validator->required = $this->_required;
			}else {
				$model = Core::instantiate("$name");
				$this->_validator->required = $model->required;
			}
			$cols = substr($cols,0,-2);
			$vals = substr($vals, 0,-2);
			$stmt = "INSERT into $tblName($cols) values($vals) $this->_addToEnd;";
		}else {
			$this->_validator->required = array();
			$updateList = substr($updateList, 0, -2);
			$id = $value['id'];
			$stmt = "UPDATE $tblName SET $updateList WHERE id=$id $this->_addToEnd";
		}
		$inserts = $this->beforeValidate($inserts);
		$valid = $this->_validator->validate($tblName,$value);
		if($valid === true) {
			array_push(Core::$debug['statements'], $stmt);
			$inserts = $this->beforeSave($inserts);

			// prepare the statement
			$stmt = $this -> db -> prepare($stmt);
			// execute the statement with the inserts array
			if($stmt -> execute($inserts) && $stmt->rowCount() >0) {

				// get the id of the last inserted or updated item
				$id = $insert?$this->db->lastInsertId():$id;
				if($this->_returnSave) {
					if(!empty($this->_selectStatement)) {
						$selectStatement = $this->_selectStatement;
						$joins = $this->_addJoins();
					}else {
						// get all the columns for the select statment
						$selectStatement = $this->_createSelect($name,$realCols);
						$selectStatement = substr($selectStatement,0,-2);
						$joins = $this->_addJoins();
						$selectStatement .= $this->_selectStatement;
					}
					
					
					// create the select statement
					$select = "Select $selectStatement from $tblName AS $name $joins where $name.id=:id";
					
					// get the data with that statment
					$tbl = $this->_getData($select,array("id"=>$id));
					// return the data
					self::$success = true;
					return $tbl[0];
				}
				self::$success = true;
				return array("id"=>$id);
			}else {

				$info = $stmt->errorInfo();
				$errorInfo = $stmt->rowCount()>0?"id was not found":$info[2];
				self::$success = false;
				self::$error = array("code"=>4,"message"=>$errorInfo);
				return;
			}
		}
		self::$success = false;
		self::$error = array("code"=>2,"message"=>$valid);
		return;
	}
	private function _getData($stmt,$data=NULL) {
		array_push(Core::$debug['statements'], $stmt);
		// prepare the statement for PDO
		$stmt = $this -> db -> prepare($stmt);
		// create the reture array
		$return = array();
		// execute the select statement
		if($stmt -> execute($data)) {
			// get all the data from the database
			$val = $stmt -> fetchAll();
			// the length of the data
			$valLen = count($val);
			if($valLen >0) {
				// loop through the result
				$ids = array();
				$currentQuery = -1;
				$currentHasMany = 0;
				for($i=0;$i<$valLen;$i++) {
					
					$bool = isset($ids[$val[$i][$this->_name."$"."id"]]);
					if($bool) {
						$currentQuery = $ids[$val[$i][$this->_name."$"."id"]];
					}else {
						$currentQuery++;
						$return[$currentQuery] = array();	
						$currentHasMany = 0;
					}
					foreach ($val[$i] as $key => $value) {
						// split the name of the column on the dollar sign
						$info =preg_split("/[$]/",$key);
						// set the table name
						$table = $info[0];
						// set the column name
						$col = $info[1];
						// if the table isn't set in the return, set it from the tables array
						if($this->_recursive == 0 && !isset($return[$currentQuery])) {
							$return[$currentQuery] = array();
						}
						if($table == $this->_name && $col == "id") {
							$ids[$value] = $currentQuery;
						}
						if(gettype($this->_hasMany) == "array" && $this->_recursive == 2) {
							$set = false;
							$multi = false;
							foreach($this->hasMany as $key=>$hasvalue) {
								if(gettype($key)=="string" && $key == $table) {
									$set = true;
									
								}else if($hasvalue == $table) {
									$set = true;

								}
							}
							
							if($set) {
								
								$return[$currentQuery][$table][$currentHasMany][$col] = $value;
							}else  {
								
								$return[$currentQuery][$table][$col] = $value;
							}
						}else {
							if($this->_recursive == 0) {
								$return[$currentQuery][$col] = $value;
							}else {
								// set the value to the table and column in that iteration
								$return[$currentQuery][$table][$col] = $value;
							}
						}
					}
					$currentHasMany++;

				}
				if($valLen >1) {
					self::$success = true;
				}
				
			}else {
				self::$success = false;
				self::$error = array("code"=>4,"message"=>"no results found");
				return;
			}
		}else {
			$errorInfo = $stmt->errorInfo();
			self::$success = false;
			self::$error = array("code"=>4,"message"=>$errorInfo[2]);
			return;
		}
		return $return;
	}
	private function _setColumns() {
		// the alias of the table
		$name =$this->_name;
		// get the columns for that table
		$this->_getColumns($name);
		// generate the columns for the select statement
		$selectStatement = $this->_createSelect($name,parent::$tables[$name]);
		// delete the last comma
		$selectStatement = substr($selectStatement,0,-2);
		// return the columns for the table and joins
		return $selectStatement;
	}
	// create a list of all the columns to selec
	private function _createSelect($tableName, $data) {
		// set up the selectStatement variable
		$selectStatement = "";
		// loop through the list of columns
		foreach($data as $col=>$value) {
			// add the string to statement for this column
			$selectStatement = $selectStatement.$tableName.".".$col." AS ".$tableName."$".$col.", ";
		}
		// return the list of all the columns to select
		return $selectStatement;
	}
	// get the list of columns from the database
	private function _getColumns($name) {
		// get to see if we have already queryed for this table
		if(!isset(parent::$tables[$name])) {
			// get the name of the table to search for
			$tableName = Core::toDB($name);
			// creat the show statement
			$show = "SHOW COLUMNS from ${tableName}";
			// prepare the statement for PDO
			array_push(Core::$debug['statements'], $show);
			$show = $this->db->prepare($show);
			// execute the statement
			if($show -> execute()) {
				// get all the data
				$table = $show->fetchAll();
				// the length of the table
				$len = count($table);
				// create a cols array
				$cols = array();
				// loop through the tables
				for($i=0;$i<$len;$i++) {
					// the field from the table
					$field = $table[$i]["Field"];
					// set the field in the cols array
					$cols[$field] = 0;
				}
			}else {
				if($stmt) {
					$errorInfo = $stmt->errorInfo();
					self::$success = false;
					self::$error = array("code"=>4,"message"=>$errorInfo[2]);
					return;
				}
				else {
					self::$success = false;
					self::$error = array("code"=>4,"message"=>"problem with the statement");
					return;
				}
			}
			// set the array to the parent so that we only have to check once
			parent::$tables[$name] =  $cols;

		}
		// return the array of all the columns in that table
		return parent::$tables[$name];
	}
	// create the join statement
	private function _addJoins() {
		// set the text variable
		$text = "";
		$tableName = $this->_name;
		
		// only do this is the recursive is set to 1 and there are things to join on
		if($this->_recursive >= 1 && gettype($this->_belongsTo) == "array") {
			$this->_selectStatement = $this->_selectStatement.", ";
			foreach($this->_belongsTo as $original=>$joinOn) {
				if(gettype($original)== "string") {
					$text .= $this->_joins($tableName,$original,"belongsTo");
					$text .= $this->_joins($original,$joinOn,"belongsTo");
				}else {

					$text .= $this->_joins($tableName,$joinOn,"belongsTo");
				}
			}	
			$this->_selectStatement = substr($this->_selectStatement,0,-2);
		}

		if($this->_recursive == 2 && gettype($this->_hasMany) == "array") {
			$this->_selectStatement = $this->_selectStatement.", ";
			// loop through the array
			foreach($this->_hasMany as $original=>$joinOn) {
				if(gettype($original)== "string") {
					$text .= $this->_joins($tableName,$original,"hasMany");
					$text .= $this->_joins($original,$joinOn,"belongsTo");
				}else {

					$text .= $this->_joins($tableName,$joinOn,"hasMany");
				}
			}
			$this->_selectStatement = substr($this->_selectStatement,0,-2);
		}
		// return the join statement
		return $text;
	}
	private function _joins($original,$joinOn,$type) {
		// the name of the table
		$joinOnName = Core::toDB($joinOn);
		$this->_getColumns($joinOn);
		$tableName = Core::toDB($original);

		if(!isset($this->_options['fields'])) {
			// get the columns for that join
			$this->_selectStatement .= $this->_createSelect($joinOn,parent::$tables[$joinOn]);
		}
		// create the statement for the join
		if($type == "belongsTo") {
			$text =  $text." LEFT JOIN ${joinOnName} AS ${joinOn} ON $original.${joinOnName}_id = ${joinOn}.id ";	
		}else {
			$text =  $text." LEFT JOIN ${joinOnName} AS ${joinOn} ON $joinOn.".$tableName."_id = $original.id ";
		}
		
		// get the list of the columns from the database so that we can use them later
		return $text;
	}
}