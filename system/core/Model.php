<?php
class Model extends ORM {
	public $tableName;
	public $required = array();
	public $rules = array();
	public $belongsTo = array();
	public $hasMany = array();
	public $success = true;
	public $error = array();

	public function __construct()
	{
		// call the before validation function
		Hooks::register("before_validation",array(get_called_class(),"before_validation"));

		// call the before save function
		Hooks::register("before_save",array(get_called_class(),"before_save"));

		// call the before delete function
		Hooks::register("before_delete",array(get_called_class(),"before_delete"));

		parent::__construct();

	}

	public function before_validation(&$data) {


	}

	public function before_save(&$data) {


	}

	public function before_delete($id,$db_name,&$model)
	{


	}


}