<?php
class Model extends ORM {
	public $tableName;
	public $required = array();
	public $rules = array();
	public $belongsTo = array();
	public $hasMany = array();
	public $success = true;
	public $error = array();
	
	public function beforeValidation($data) {
		return $data;
	}
	
	public function beforeSave($data) {

		return $data;
	}
}