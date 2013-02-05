<?php
/**
 * Holds all the bastics of a model
 */

/**
 * This is the base set up of the model
 * @category   Core
 * @package    Core
 * @extends    ORM
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
class Model extends ORM {
	/**
	 * tableName: string
	 *
	 * The table name, defaults to the name of the model in db form
	 * @var string
	 */
	public $tableName;

	/**
	 * required: array
	 *
	 * an array of all the required columns
	 * @var array
	 */
	public $required = array();

	/**
	 * rules: array
	 *
	 * an array of all the rules of validation to follow
	 * @var array
	 */
	public $rules = array();

	/**
	 * belongsTo: array
	 *
	 * an array of all the models that this model belongs to
	 * This tables has the table_id
	 * @var array
	 */
	public $belongsTo = array();

	/**
	 * hasMany: array
	 *
	 * an array of all the models that this model has
	 * @var array
	 */
	public $hasMany = array();

	/**
	 * success: boolean
	 *
	 * if this model was successful in it's call
	 * @var boolean
	 */
	public $success = true;

	/**
	 * error: array
	 *
	 * an array of any errors that may have occurred during the call
	 * @var array
	 */
	public $error = array();

	/**
	 * Register all of the hooks for the model
	 */
	public function __construct()
	{
		// call the before validation function
		Hook::register("before_validation",array(get_called_class(),"before_validation"));

		// call the before save function
		Hook::register("before_save",array(get_called_class(),"before_save"));

		// call the before delete function
		Hook::register("before_delete",array(get_called_class(),"before_delete"));

		parent::__construct();

	}

	/**
	 * Run this before any validation happens
	 * @param  array $data all of the fields
	 * @return boolean       if the validation should continue to run
	 */
	public function before_validation(&$data) {


	}

	/**
	 * Run this before you save any data
	 * @param  array $data all the data that will be saved
	 * @return boolean       if the save should continue
	 */
	public function before_save(&$data) {


	}

	/**
	 * Run this before you delete any data
	 * @param  int $id      the id of the object to be deleted
	 * @param  string $db_name the name of database table
	 * @param  object $model   the model that is being called
	 * @return boolean          if the delete should continue to run
	 */
	public function before_delete($id,$db_name,&$model)
	{


	}


}