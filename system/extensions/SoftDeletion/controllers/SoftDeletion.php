<?php
Class SoftDeletion
{

	// runs before the deletion of data in ORM
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
			if($stmt->rowCount() > 0)
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

	public function find(&$model)
	{

		// if it is timestamp set it to the current time
		if(DELETION_TYPE === 'timestamp')
		{
			array_push($model->options['where'], DELETION_COL_NAME." IS NULL");
		}
		// if it is a boolean set it to one
		else if(DELETION_TYPE === 'boolean')
		{
			$model->options['where'][DELETION_COL_NAME] = array(1);
		}

	}
}