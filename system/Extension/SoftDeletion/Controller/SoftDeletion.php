<?php
/**
 * The Soft Deletion Controller
 */

/**
 * The Soft Deletion Extension controller
 * @category Core
 * @package  Extensions
 * @subpackage SoftDeletion
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
Class Extension_SoftDeletion_Controller_Delete
{

	/**
	 * runs before the deletion of data in ORM
	 * @param  int $id      the id to be deleted
	 * @param  string $db_name the name of the model
	 * @param  object $model   the model
	 * @return boolean          if the delete should continue
	 */
	public function delete($id, $db_name, $model)
	{

		// if soft_delete is false then stop the function
		if(isset($model->soft_delete) && $model->soft_delete === false) return;

		// if it is timestamp set it to the current time
		if(DELETION_TYPE === 'timestamp')
		{
			$value = 'NOW()';
		}
		// if it is a boolean set it to one
		else if(DELETION_TYPE === 'boolean')
		{
			$value = "1";
		}

		// create the update statement
		$update_statement = "UPDATE $db_name SET ".DELETION_COL_NAME."= $value WHERE id = :id";

		// push the statement into the debug
		array_push(Core::$debug['statements'], $update_statement);

		// prepare the statement for the call
		$stmt = $model->db->prepare($update_statement);

		// if statement ran correctly
		if($stmt->execute(array('id'=>$id)))
		{
			// if a row was deleted
			if($stmt->rowcount() > 0)
			{

				// set success to true
				$model->success = true;

			}
			// if no rows were deleted
			else
			{
				// set success to false
				$model->success = false;

				// set the error message and code
				$model->error = array("msg"=>"ID not found in database","code"=>3);
			}
		}

		// stop the actual deletion from running
		return false;

	}

	/**
	 * runs before the find of data in ORM
	 * @param  object $model the model
	 * @return boolean        if the find should continue
	 */
	public function find(&$model)
	{

		if(isset($model->soft_delete) && $model->soft_delete === false)
		{
			return;
		}
		// if it is timestamp set it to the current time
		if(DELETION_TYPE === 'timestamp')
		{
			array_push($model->options['where'], $model->_name.".".DELETION_COL_NAME." IS NULL");
		}
		// if it is a boolean set it to one
		else if(DELETION_TYPE === 'boolean')
		{
			$model->options['where'][$model->_name.".".DELETION_COL_NAME] = array(1);
		}

	}
}