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
abstract class Core_Model extends ORM {
	/**
	 * table_name: string
	 *
	 * The table name, defaults to the name of the model in db form
	 * @var string
	 */
	public $table_name;

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
	 * belongs_to: array
	 *
	 * an array of all the models that this model belongs to
	 * This tables has the table_id
	 * @var array
	 */
	public $belongs_to = array();

	/**
	 * has_many: array
	 *
	 * an array of all the models that this model has
	 * @var array
	 */
	public $has_many = array();

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
	public function __construct($DB_HOSTNAME=DB_HOSTNAME,$DB_NAME=DB_NAME,$DB_USERNAME=DB_USERNAME,$DB_PASSWORD=DB_PASSWORD)
	{
		// call the before validation function
		Hook::register("beforeValidation",array(get_called_class(),"beforeValidation"));

		// call the before save function
		Hook::register("beforeSave",array(get_called_class(),"beforeSave"));

		// call the before delete function
		Hook::register("beforeDelete",array(get_called_class(),"beforeDelete"));

		parent::__construct($DB_HOSTNAME,$DB_NAME,$DB_USERNAME,$DB_PASSWORD);

	}

	/**
	 * Run this before any validation happens
	 * @param  array $data all of the fields
	 * @return boolean       if the validation should continue to run
	 */
	public function beforeValidation(&$data,&$rules) {


	}

	/**
	 * Run this before you save any data
	 * @param  array $data all the data that will be saved
	 * @return boolean       if the save should continue
	 */
	public function beforeSave(&$data) {


	}

	/**
	 * Run this before you delete any data
	 * @param  int $id      the id of the object to be deleted
	 * @param  string $db_name the name of database table
	 * @param  object $model   the model that is being called
	 * @return boolean          if the delete should continue to run
	 */
	public function beforeDelete($id,$db_name,&$model)
	{


	}


}