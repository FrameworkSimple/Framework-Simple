<?php
/**
 * The Ordering Controller
 */

/**
 * The Ordering Extension controller
 * @category Core
 * @package  Extensions
 * @subpackage ORder
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
Class Extension_Ordering_Controller_Order
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
		if(isset($model->ordering) && $model->ordering === false)
		{
			return;
		}


	}

	/**
	 * runs before the find of data in ORM
	 * @param  object $model the model
	 * @return boolean        if the find should continue
	 */
	public function find(&$model)
	{
		if(!isset($model->ordering) || $model->ordering === false)
		{
			return;
		}

		if(!$model->order_by) $model->order_by = array($model->_name, ORDERING_COL_NAME,'ASC');

	}

	/**
	 * runs before the save of data
	 * @param  object $model the model
	 * @return boolean        if the find should continue
	 */
	public function save(&$data,&$model)
	{
		if(!isset($model->ordering) || $model->ordering === false)
		{
			return;
		}

		$original_data = $data;
		$data = array();

		// TODO: if this has an order in the data
			// if this number is already exists
				// update all the items above it to plus one
			//	save the item

		// if this is a new save
		if(!isset($original_data['id']))
		{

			$original = array();
			foreach ($model->_default_options as $prop => $value) {
				$original[$prop] = $model->{$prop};
			}

			$model->recursive = 0;
			$model->limit = array(0,1);
			$model->order_by = array($model->_name,ORDERING_COL_NAME);
			$model->columns = array($model->_name=>array('id',ORDERING_COL_NAME));

			// get the column that is unique
			$method = "findBy".Utilities::toCam($model->ordering);

			// get the last order id
			$result = $model->$method($original_data[Utilities::toDb($model->ordering)]);

			// add the last order id plus one to the data to be saved
			if($model->success)
			{

				$original_data[ORDERING_COL_NAME] = $result[0][ORDERING_COL_NAME] + 1;
			}
			else
			{
				$original_data[ORDERING_COL_NAME] = 1;
			}

			foreach ($original as $prop => $value) {
				$model->{$prop} = $value;
			}

		}
		$data = $original_data;

	}

	public static function getNext($current_id, $model, $recursive=0)
	{
		$original = array();
		foreach ($model->_default_options as $prop => $value) {
			$original[$prop] = $model->{$prop};
		}
		$original_data = $data;
		$data = array();

		$name = preg_split("/Model_/",get_class($model))[1];
		$model->recursive = $recursive;
		$model->limit = array(0,1);
		$model->order_by = array($name,ORDERING_COL_NAME,'ASC');

		$model->where = array(
			$name.'.'.ORDERING_COL_NAME.' > (Select '.ORDERING_COL_NAME.' from '.Utilities::toDb($name).' where id = '.$current_id.')',
			$name.'.'.Utilities::toDb($model->ordering).' = (SELECT '.Utilities::toDb($model->ordering).' from '.Utilities::toDb($name).' where id = '.$current_id.')'
			);

		$result = $model->findAll();

		foreach ($original as $prop => $value) {
			$model->{$prop} = $value;
		}
		$data = $original_data;


		return $result;
	}

	public static function getPrev($current_id, $model, $recursive=0)
	{
		$original = array();
		foreach ($model->_default_options as $prop => $value) {
			$original[$prop] = $model->{$prop};
		}
		$original_data = $data;
		$data = array();

		$name = preg_split("/Model_/",get_class($model))[1];
		$model->recursive = $recursive;
		$model->limit = array(0,1);
		$model->order_by = array($name,ORDERING_COL_NAME);
		$model->where = array(
			$name.'.'.ORDERING_COL_NAME.' < (Select '.ORDERING_COL_NAME.' from '.Utilities::toDb($name).' where id = '.$current_id.')',
			$name.'.'.Utilities::toDb($model->ordering).' = (SELECT '.Utilities::toDb($model->ordering).' from '.Utilities::toDb($name).' where id = '.$current_id.')'
			);

		$result = $model->findAll();



		foreach ($original as $prop => $value) {
			$model->{$prop} = $value;
		}
		$data = $original_data;


		return $result;
	}
}