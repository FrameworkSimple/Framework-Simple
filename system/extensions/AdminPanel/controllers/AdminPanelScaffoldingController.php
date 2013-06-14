<?php
/**
	 * The Admin Panel Scaffolding Controller
	 */

	/**
	 * The Admin Panel Extension Scaffolding controller
	 * @category Extensions
	 * @package  Extensions
	 * @subpackage AdminPanel
	 * @author     Rachel Higley <me@rachelhigley.com>
	 * @copyright  2013 Framework Simple
	 * @license    http://www.opensource.org/licenses/mit-license.php MIT
	 * @link       http://rachelhigley.com/framework
	 */
Class AdminPanelScaffoldingController extends Controller
{
	static public $layout = "admin_panel";

	public function index()
	{

		// get the tables model
		$this->loadModel("Tables");

		// get all the tables witht their create statements
		$tables = $this->Tables->get_statements();

		$this->view_data('tables',$tables);
	}

	public function post($info=NULL) {

		$model = Core::instantiate("Tables");

		$tables = $model->get_statements();

		$information = array();
		$information['hasMany'] = array();
		$information['belongsTo'] = array();

		foreach($tables as $index=>$table) {
			$table['structure'] = str_replace("CREATE TABLE `", "",$table['structure']);
			$table_info = explode(") ENGINE", $table['structure'])[0];
			$table = array();
			$table['cols_info'] = explode("\n", $table_info);
			$information['tables'][$index]['name'] = Core::to_cam(str_replace("` (", "", $table['cols_info'][0]));
			unset($table['cols_info'][0]);
			$information['tables'][$index]['cols'] = array();
			foreach($table['cols_info'] as $col) {
				if(strpos($col, "FOREIGN KEY")) {
					$key_info = explode("REFERENCES `", $col);
					$key_table = Core::to_cam(explode("` ", $key_info[1])[0]);
					if(!isset($information['hasMany'][$key_table])) $information['hasMany'][$key_table] = array();
					if(!isset($information['belongsTo'][$information['tables'][$index]['name']])) $information['belongsTo'][$information['tables'][$index]['name']] = array();
					array_push($information['hasMany'][$key_table],$information['tables'][$index]['name']);
					array_push($information['belongsTo'][$information['tables'][$index]['name']],Core::to_cam($key_table));

				}
				else if(strpos($col, "UNIQUE KEY"))
				{
					$key_info = explode("(", $col)[1];
					$key_info = explode(")", $key_info)[0];
					$key_info = str_replace("`", "", $key_info);
					$key_columns = explode(",", $key_info);
					foreach ($key_columns as $key_column) {
						$information['tables'][$index]['cols'][$key_column]['unique'] = true;
					}

				}
				else if($col  && !strpos($col,"KEY")) {
					$col_info = explode("`", $col);
					$information['tables'][$index]['cols'][$col_info[1]] = array();
					$information['tables'][$index]['cols'][$col_info[1]]['name'] = $col_info[1];
					if(!isset($information['tables'][$index]['required'])) {
						$information['tables'][$index]['required'] = array();
					}
					if(strpos($col_info[2], "NOT NULL")) {
						if(!strpos($col_info[2], "AUTO_INCREMENT")) {
							if((strpos($col_info[2], "DEFAULT") && strpos($col_info[2], "''")) || !strpos($col_info[2], "DEFAULT"))
							{
								array_push($information['tables'][$index]['required'],$information['tables'][$index]['cols'][$col_info[1]]['name'] );
							}
						}
					}
					$information['tables'][$index]['cols'][$col_info[1]]['type'] = explode(" ",$col_info[2])[1];
					if(strpos($information['tables'][$index]['cols'][$col_info[1]]['type'],"(")){
						$col_type_info = explode("(", $information['tables'][$index]['cols'][$col_info[1]]['type']);
						$information['tables'][$index]['cols'][$col_info[1]]['type'] = $col_type_info[0];
						$information['tables'][$index]['cols'][$col_info[1]]['length'] = str_replace(")", "", $col_type_info[1]);
					}

				}
			}

		}

		// make sure there are folders for the models, controlers, and views
		$this->_createFolders();

		if($info['layout'])
		{
			$layout = $this->_view_layout();
			file_put_contents(SYSTEM_PATH."/views/layouts/page.php", $layout);
		}

		foreach ($information['tables'] as $table)
		{

			// create the name with underscores
			$underscores = Core::to_db($table['name']);

			// if this table is not one that we want to build scaffolding stop the current iteration
			if(!isset($info[$underscores])) continue;

			// create the normal name for the database
			$normal = Core::to_norm($table['name']);

			// the path to the controller
			$controller_path = SYSTEM_PATH."/controllers/".$table['name']."Controller.php";

			// the path to the model
			$model_path = SYSTEM_PATH."/models/".$table['name'].".php";

			// if we want to build the controller
			if(isset($info[$underscores]["controller"]))
			{

				$controller_info = $info[$underscores]["controller"];
				// if the controller file doesn't exist
				if(!is_file($controller_path))
				{
					// set the basic top setup
					$controller = $this->_controller_base($normal,$underscores,$table['name']);

					// if we want the index function build and add it
					if(isset($controller_info['index'])) $controller .= $this->_controller_index($normal,$underscores,$table['name']);

					// if we want the get function build and add it
					if(isset($controller_info['get'])) $controller .= $this->_controller_get($normal,$underscores,$table['name']);

					// if we want the post function build and add it
					if(isset($controller_info['post'])) $controller .= $this->_controller_post($normal,$underscores,$table['name']);

					// if we want the update function build and add it
					if(isset($controller_info['update'])) $controller .= $this->_controller_update($normal,$underscores,$table['name']);

					// if we want the delete function build and add it
					if(isset($controller_info['delete'])) $controller .= $this->_controller_delete($normal,$underscores,$table['name']);

					// close the class
					$controller .= "\n}";

				}
				else
				{


					$controller = file_get_contents($controller_path);
					$controller = preg_split("/(extends Controller\s+{\s+)/", $controller, NULL, PREG_SPLIT_DELIM_CAPTURE);
					$controller[2] = substr(trim($controller[2]),0,-1);
					$controller[3] = "\n}";
					$function_split = preg_split("/(?=\/\*\*)([.\n\s\S]*?)(?=public func)/", $controller[2],NULL, PREG_SPLIT_DELIM_CAPTURE);

					foreach ($function_split as $index=>$function) {

						if(strpos($function, "public function ") === 0)
						{


							$function = str_replace("public function ", "", $function);

							if(isset($controller_info['index']) && strpos($function, "index(") === 0)
								$function_split[$index] = $this->_controller_index($normal,$underscores,$table['name'],false);
							if(isset($controller_info['get']) && strpos($function, "get(") === 0)
								$function_split[$index] = $this->_controller_get($normal,$underscores,$table['name'],false);
							if(isset($controller_info['post']) && strpos($function, "post(") === 0)
								$function_split[$index] = $this->_controller_post($normal,$underscores,$table['name'],false);
							if(isset($controller_info['update']) && strpos($function, "update(") === 0)
								$function_split[$index] = $this->_controller_update($normal,$underscores,$table['name'],false);
							if(isset($icontroller_info['delete']) && strpos($function, "delete(") === 0)
								$function_split[$index] = $this->_controller_delete($normal,$underscores,$table['name'],false);

							$function_split[$index] .= "\n\t";
						}

					}


					$controller[2] = implode("", $function_split);

					$controller = implode("", $controller);

				}

				// write the file
				file_put_contents($controller_path,$controller);

			}

			if(isset($info[$underscores]["model"]))
			{

				// create the model
				$model = "";

				// model information
				$model_information = $info[$underscores]["model"];


				// if the model file doesn't already exist
				if(!is_file($model_path))
				{
					$model = "<?php\nClass ".$table['name']." extends Model\n{\n";


					if(isset($information['belongsTo'][$table['name']]) && isset($model_information["belongsTo"]))
						$model .= $this->_model_belongsTo($information['belongsTo'][$table['name']]);

					if(isset($information['hasMany'][$table['name']]) && isset($model_information['hasMany']))
						$model .= $this->_model_hasMany($information['hasMany'][$table['name']]);

					if(!empty($table['required']) && isset($model_information['required']))
						$model .= $this->_model_required($table['required']);

					if(!empty($table['cols']) && isset($model_information['rules']))
						$model .= $this->_model_rules($table['cols']);

					$model .= "\n\n}";

				}
				else
				{
					$model = file_get_contents($model_path);

					if(isset($information['belongsTo'][$table['name']]) && isset($model_information['belongsTo']))
						$model = preg_replace("/\n\t+(?=public \Sbelongs)([.\n\s\S]*?)(?<=;)\n/", $this->_model_belongsTo($information['belongsTo'][$table['name']]), $model);

					if(isset($information['hasMany'][$table['name']]) && isset($model_information['hasMany']))
						$model = preg_replace("/\n\t+(?=public \ShasMany)([.\n\s\S]*?)(?<=;)\n/", $this->_model_hasMany($information['hasMany'][$table['name']]), $model);

					if(!empty($table['required']) && isset($model_information['required']))
						$model = preg_replace("/(\n\t+?=public \Srequired)([.\n\s\S]*?)(?<=;)\n/", $this->_model_required($table['required']), $model);

					if(!empty($table['cols']) && isset($model_information['rules']))
						$model = preg_replace("/\n\t+(?=public \Srules)([.\n\s\S]*?)(?<=;)\n/", $this->_model_rules($table['cols']), $model);

				}

				file_put_contents($model_path, $model);

			}

			if(isset($info[$underscores]['view']))
			{

				// the folder for the views
				$view_folder = SYSTEM_PATH."/views/".$underscores;

				if(!is_dir($view_folder))
				{

					// create the directory
					mkdir($view_folder);

				}

				$view_info = $info[$underscores]['view'];

				if($view_info['index'])
				{

					$index = $this->_view_index($table['cols'],$underscores);

					file_put_contents($view_folder."/index.php", $index);

				}

				if($view_info['get'])
				{

					$get = $this->_view_get($table['cols'],$underscores);

					file_put_contents($view_folder."/get.php", $get);

				}

				if($view_info['post'])
				{

					$post = $this->_view_post($underscores);

					file_put_contents($view_folder."/post.php", $post);

				}

				if($view_info['update'])
				{

					$update = $this->_view_update($underscores);

					file_put_contents($view_folder."/update.php", $update);

				}

				if($view_info['form'])
				{

					$form = $this->_view_form($table['cols'],$underscores);

					file_put_contents($view_folder."/_form.php", $form);

				}

			}
		}
	}

	private function _createFolders()
	{
		if(!is_dir(SYSTEM_PATH."/controllers"))
		{

			// create the directory
			mkdir(SYSTEM_PATH."/controllers");

		}

		if(!is_dir(SYSTEM_PATH."/models"))
		{

			// create the directory
			mkdir(SYSTEM_PATH."/models");

		}
		if(!is_dir(SYSTEM_PATH."/views"))
		{

			// create the directory
			mkdir(SYSTEM_PATH."/views");

		}

		if(!is_dir(SYSTEM_PATH."/views/layouts"))
		{

			// create the directory
			mkdir(SYSTEM_PATH."/views/layouts");

		}
	}

	private function _controller_base($normal,$underscores,$name)
	{
		$controller = "<?php\n/**";
		$controller .= "\n * The ".$normal." Controller";
		$controller .= "\n */";
		$controller .= "\n\n/**";
		$controller .= "\n * The ".$normal." Controller";
		$controller .= "\n * @category   Controllers";
		$controller .= "\n * @package    ".$_POST['application_name'];
		$controller .= "\n * @subpackage Controllers\n * @author     ".$_POST['name'];
		$controller .= "\n */";
		$controller .= "\n Class ".$name."Controller extends Controller";
		$controller .= "\n{";

		return $controller;
	}

	private function _controller_index($normal, $underscores, $name, $comments=TRUE)
	{
		$controller = "";
		if($comments)
		{
			$controller = "\n\t/**";
			$controller .= "\n\t * Get all the ".$normal."s";
			$controller .= "\n\t * @return array all the ".$normal."s";
			$controller .= "\n\t */\n\t";
		}
		$controller .= "public function index()";
		$controller .= "\n\t{";
		$controller .= "\n\n\t\t// load the model";
		$controller .= "\n\t\t".'$this->loadModel("'.$name.'"'.");";
		$controller .= "\n\n\t\t// only get this table";
		$controller .= "\n\t\t".'$this->'.$name."->options['recursive'] = 0;";
		$controller .= "\n\n\t\t// get all the ".$normal."s";
		$controller .= "\n\t\t$".$underscores."s = ".'$this->'.$name."->findAll();";
		$controller .= "\n\n\t\t//set the success";
		$controller .= "\n\t\t".'$this->view_data('."'success',".'$this->'.$name."->success);";
		$controller .= "\n\n\t\t// if the call was successful";
		$controller .= "\n\t\tif(".'$this->'.$name."->success)";
		$controller .= "\n\t\t{";
		$controller .= "\n\n\t\t\t// set the information for the view";
		$controller .= "\n\t\t\t".'$this->view_data("'.$underscores.'s",$'.$underscores."s);";
		$controller .= "\n\n\t\t\t// return the information";
		$controller .= "\n\t\t\treturn $".$underscores."s;";
		$controller .= "\n\n\t\t}";
		$controller .= "\n\t}";

		return $controller;
	}

	private function _controller_get($normal, $underscores, $name,$comments=TRUE)
	{
		$controller = "";
		if($comments)
		{
			$controller .= "\n\t/**";
			$controller .= "\n\t * Get one ".$normal;
			$controller .= "\n\t * @param  int the id of the ".$normal." to get";
			$controller .= "\n\t * @return one ".$normal;
			$controller .= "\n\t*/\n\t";
		}
		$controller .= "public function get(".'$id'.")";
		$controller .= "\n\t{";
		$controller .= "\n\t\tif(".'$id'.")";
		$controller .= "\n\t\t{";
		$controller .= "\n\n\t\t\t// load the model";
		$controller .= "\n\t\t\t".'$this->loadModel("'.$name.'"'.");";
		$controller .= "\n\n\t\t\t// only get this table";
		$controller .= "\n\t\t\t".'$this->'.$name."->options['recursive'] = 0;";
		$controller .= "\n\n\t\t\t// get all the ".$normal."s";
		$controller .= "\n\t\t\t$".$underscores." = ".'$this->'.$name."->findById(".'$id'.");";
		$controller .= "\n\n\t\t\t//set the success";
		$controller .= "\n\t\t\t".'$this->view_data('."'success',".'$this->'.$name."->success);";
		$controller .= "\n\n\t\t\t// if the call was successful";
		$controller .= "\n\t\t\tif(".'$this->'.$name."->success)";
		$controller .= "\n\t\t\t{";
		$controller .= "\n\n\t\t\t\t// set the information for the view";
		$controller .= "\n\t\t\t\t".'$this->view_data("'.$underscores.'",$'.$underscores."[0]);";
		$controller .= "\n\n\t\t\t\t// return the information";
		$controller .= "\n\t\t\t\treturn $".$underscores."[0];";
		$controller .= "\n\t\t\t}";
		$controller .= "\n\t\t\treturn false;";
		$controller .= "\n\t\t}";
		$controller .= "\n\n\t}";

		return $controller;
	}

	private function _controller_post($normal, $underscores, $name,$comments=TRUE)
	{
		$controller = "";
		if($comments)
		{
			$controller = "\n\t/**";
			$controller .= "\n\t * Create new ".$normal;
			$controller .= "\n\t * @param  array $".$underscores." all the information to save";
			$controller .= "\n\t * @return boolean if it was successfull";
			$controller .= "\n\t */";
			$controller .= "\n\t";
		}
		$controller .= "public function post($".$underscores."=NULL)";
		$controller .= "\n\t{";
		$controller .= "\n\t\t//if information was sent";
		$controller .= "\n\t\tif($".$underscores.")";
		$controller .= "\n\t\t{";
		$controller .= "\n\t\t\t// load the model";
		$controller .= "\n\t\t\t".'$this->loadModel("'.$name.'"'.");";
		$controller .= "\n\n\t\t\t// save the new ".$normal;
		$controller .= "\n\t\t\t".'$this->'.$name."->save($".$underscores.");";
		$controller .= "\n\n\t\t\t// set the success";
		$controller .= "\n\t\t\t".'$this->view_data("success",$this->'.$name."->success);";
		$controller .= "\n\n\t\t\t".'if(!$this->'.$name.'->success)';
		$controller .= "\n\t\t\t{";
		$controller .= "\n\n\t\t\t\t// set the errors because something went wrong";
		$controller .= "\n\t\t\t\t".'$this->view_data("errors",$this->'.$name.'->error);';
		$controller .= "\n\n\t\t\t\t// set the $normal so that you have the already inputed values";
		$controller .= "\n\t\t\t\t".'$this->view_data("'.$underscores.'",$'.$underscores.");";
		$controller .= "\n\t\t\t}";
		$controller .= "\n\n\t\t\t// return the success";
		$controller .= "\n\t\t\t".'return $this->'.$name."->success;";
		$controller .= "\n\t\t}";
		$controller .= "\n\t}";

		return $controller;
	}

	private function _controller_update($normal, $underscores, $name,$comments=TRUE)
	{
		$controller = "";
		if($comments)
		{
			$controller = "\n\t/**";
			$controller .= "\n\t * Update a ".$normal;
			$controller .= "\n\t * @param  array $".$underscores." all the information to update, including id";
			$controller .= "\n\t * @return boolean if it was successfull";
			$controller .= "\n\t */";
			$controller .= "\n\t";
		}
		$controller .= "public function update($".$underscores."_id=NULL,$".$underscores."=NULL)";
		$controller .= "\n\t{";
		$controller .= "\n\n\t\t// if information was sent";
		$controller .= "\n\t\tif($".$underscores.")";
		$controller .= "\n\t\t{";
		$controller .= "\n\n\t\t\t// if there is no id in the form set it from the url";
		$controller .= "\n\t\t\tif(!isset($".$underscores."['id'])$".$underscores."['id'] = $".$underscores."_id;";
		$controller .= "\n\n\t\t\t// load the model";
		$controller .= "\n\t\t\t".'$this->loadModel("'.$name.'"'.");";
		$controller .= "\n\n\t\t\t// save the new ".$normal;
		$controller .= "\n\t\t\t".'$this->'.$name."->save($".$underscores.");";
		$controller .= "\n\n\t\t\t// set the success";
		$controller .= "\n\t\t\t".'$this->view_data("success",$this->'.$name."->success);";
		$controller .= "\n\n\t\t\t// if the save was not successful";
		$controller .= "\n\t\t\t".'if(!$this->'.$name.'->success)';
		$controller .= "\n\t\t\t{";
		$controller .= "\n\t\t\t\t// set the errors";
		$controller .= "\n\t\t\t\t".'$this->view_data("errors",$this->'.$name.'->error);';
		$controller .= "\n\t\t\t}";
		$controller .= "\n\t\t}";
		$controller .= "\n\n\t\t// if there is an id";
		$controller .= "\n\t\tif($".$underscores."_id)";
		$controller .= "\n\t\t{";
		$controller .= "\n\t\t\t\n\t\t\t// get a ".$normal;
		$controller .= "\n\t\t\t".'$this->get($'.$underscores."_id);";
		$controller .= "\n\t\t\t\n\t\t}";
		$controller .= "\n\n\n\t}";

		return $controller;
	}

	private function _controller_delete($normal,$underscores,$name,$comments=TRUE)
	{
		$controller = "";
		if($comments)
		{
			$controller = "\n\t/**";
			$controller .= "\n\t * Delete a ".$normal;
			$controller .= "\n\t * @param  int $".$underscores."_id id of the ".$normal." to delete";
			$controller .= "\n\t * @return boolean if it was successfull";
			$controller .= "\n\t */";
			$controller .= "\n\t";
		}
		$controller .= "public function delete($".$underscores."_id=NULL)";
		$controller .= "\n\t{";
		$controller .= "\n\t\t// if there was an id sent";
		$controller .= "\n\t\tif($".$underscores."_id)";
		$controller .= "\n\t\t{";
		$controller .= "\n\n\t\t\t// load the model";
		$controller .= "\n\t\t\t".'$this->loadModel("'.$name.'"'.");";
		$controller .= "\n\n\t\t\t// save the new ".$normal;
		$controller .= "\n\t\t\t".'$this->'.$name."->delete($".$underscores."_id);";
		$controller .= "\n\n\t\t\t// set the success";
		$controller .= "\n\t\t\t".'$this->view_data("success",$this->'.$name."->success);";
		$controller .= "\n\n\t\t\t// set the success";
		$controller .= "\n\t\t\t".'$this->'.$name."->success;";
		$controller .= "\n\n\t\t\t//return to the page that called it";
		$controller .= "\n\t\t\t".'header("Location: ".$_SERVER["HTTP_REFERER"]);';
		$controller .= "\n\n\t\t}";
		$controller .= "\n\t}";

		return $controller;
	}

	private function _model_belongsTo($tables)
	{
		$model = "\n\tpublic ".'$belongsTo'." = array('";
		$model .= implode("','", $tables);
		$model .= "');\n";

		return $model;
	}

	private function _model_hasMany($tables)
	{
		$model = "\n\tpublic ".'$hasMany'." = array('";
		$model .= implode("','", $tables);
		$model .= "');\n";

		return $model;
	}

	private function _model_required($tables)
	{
		$model = "\n\tpublic ".'$required'." = array('";
		$model .= implode("','", $tables);
		$model .= "');\n";

		return $model;
	}

	private function _model_rules($cols)
	{
		$model = "\n\tpublic ".'$rules = '."array(";

		foreach($cols as $col)
		{
			$model .= "\n\t\t'".$col['name']."' => array(";

			if($col['name'] === 'id')
			{
				$model .= "'numeric',";
			}
			else
			{
				switch ($col['type']) {
					case 'int':
						$model .= "'numeric',";
						break;

					case 'varchar':
						$model .= "'alphaNumeric',";
						break;

					case 'timestamp':
						$model .= "'timestamp',";
						break;
					case 'text':
						$model .= " ";
						break;
					default:
						$model .= " ";
						break;
				}
			}

			if(isset($col['length']))
			{
				$model .= "'maxLength' =>".$col['length'].",";
			}

			$model = substr($model, 0,-1);
			$model .= "), ";
		}

		$model = substr($model, 0, -2);
		$model .= "\n\t\t);\n";

		return $model;
	}

	private function _view_layout()
	{
		$layout  = "<html>";
		$layout .= "\n\t<head>";
		$layout .= "\n\t\t<title>Scafolding Page</title>";
		$layout .= "\n\t\t<style type='text/css'>";
		$layout .= "\n\t\t\t.col";
		$layout .= "\n\t\t\t{";
		$layout .= "\n\t\t\t\tdisplay:table-cell;";
		$layout .= "\n\t\t\t\tborder:1px solid #000;";
		$layout .= "\n\t\t\t\tpadding:5px;";
		$layout .= "\n\t\t\t}";
		$layout .= "\n\t\t\t.table{";
		$layout .= "\n\t\t\t\tdisplay:table;";
		$layout .= "\n\t\t\t\twidth:100%;";
		$layout .= "\n\t\t\t\tborder:1px solid #000;";
		$layout .= "\n\t\t\t}";
		$layout .= "\n\t\t\t.row";
		$layout .= "\n\t\t\t{";
		$layout .= "\n\t\t\t\tdisplay:table-row;";
		$layout .= "\n\t\t\t}";
		$layout .= "\n\t\t</style>";
		$layout .= "\n\t</head>";
		$layout .= "\n\t<body>";
		$layout .= "\n\t\t<?php echo ".'$content_for_layout'."?>";
		$layout .= "\n\t</body>";
		$layout .= "\n</html>";

		return $layout;
	}

	private function _view_index($cols, $underscores)
	{

		$index_titles =  "";
		$index_row =  "";
		if(!empty($cols))
		{

			foreach($cols as $col)
			{

				$index_titles .= "\n\t\t<div class='col'>".$col['name']."</div>";
				$index_row .= "\n\t\t\t<div class='col'>\n\t\t\t\t<?php echo $".$underscores."['".$col['name']."'] ?>\n\t\t\t</div>";

			}
		}

		$index = "<div class='table'>";
		$index .= "\n\t<div class='row'>".$index_titles."\n\t</div>";
		$index .= "\n\t<?php foreach($".$underscores."s as ".'$'.$underscores."):?>\n\t\t<div class='row'>".$index_row."\n\t\t</div>\n\t<?php endforeach ?>";
		$index .= "\n</div>";

		return $index;
	}

	private function _view_get($cols,$underscores)
	{
		if(!empty($cols))
		{
			$get = "";

			foreach($cols as $col)
			{

				$get .= "<div class='row'>\n\t<div class='col'>".$col['name']."</div>\n\t<div class='col'><?php echo $".$underscores."['".$col['name']."'] ?></div>\n</div>\n";

			}

			return $get;

		}

		return "";
	}

	private function _view_post($underscores)
	{

		$post = "<?php ";
		$post .= "\n\t".'$params = isset($'.$underscores.')?$'.$underscores.':array();';
		$post .= "\n\t".'if(isset($errors))$params = array_merge($params, $errors);';
		$post .= "\n\tView::render('".$underscores."/_form',".'$params'.");";
		$post .= "\n?>";

		return $post;
	}

	private function _view_update($underscores)
	{
		$update ="<?php";
		$update .= "\n\t".'$params = isset($'.$underscores.')?$'.$underscores.':array();';
		$update .= "\n\t".'if(isset($errors))$params = array_merge($params, $errors);';
		$update .= "\n\tView::render('".$underscores."/_form',".'$params'.");\n?>";

		return $update;
	}

	private function _view_form($cols,$underscores)
	{
		if(!empty($cols))
		{
			$form = "<form method='POST' action='".'<?=$_SERVER["REQUEST_URI"] ?>'."'>\n";

			foreach($cols as $col)
			{

				if($col['name'] === 'id')
				{
					$form .= "\t".'<?php if(isset($id)):?>';
					$form .= "\n\t\t<?php if(isset(".'$fields) && isset($fields['."'".$col['name']."'])):?>";
					$form .= "\n\t\t\t<p class='error'><?php echo ".'$fields['."'".$col['name']."']?></p>";
					$form .= "\n\t\t<?php endif;?>";
					$form .= "\n\t\t<div>\n\t\t\t<label for='".$col['name']."'>".$col['name']."</label>";
					$form .= "\n\t\t\t<input type='text' id='".$col['name']."' name='".$col['name']."' size='".$col['length']."' value='<?php if(isset($".$col['name'].")) echo $".$col['name']."; ?>' />\n";
					$form .= "\t\t</div>";
					$form .= "\n\t<?php endif;?>";
				}
				else {
					$form .= "\n\t<?php if(isset(".'$fields) && isset($fields['."'".$col['name']."'])):?>";
					$form .= "\n\t\t<p class='error'><?php echo ".'$fields['."'".$col['name']."']?></p>";
					$form .= "\n\t<?php endif;?>";
					$form .= "\n\t<div>\n\t\t<label for='".$col['name']."'>".$col['name']."</label>";
					switch ($col['type']) {
						case 'int':
							$form .= "\n\t\t<input type='text' id='".$col['name']."' name='".$col['name']."' size='".$col['length']."' value='<?php if(isset($".$col['name'].")) echo $".$col['name']."; ?>' />\n";
							break;

						case 'varchar':
							$form .= "\n\t\t<input type='text' id='".$col['name']."' name='".$col['name']."' value='<?php if(isset($".$col['name'].")) echo $".$col['name']."; ?>' />\n";
							break;

						case 'timestamp':
							$form .= "\n\t\t<input type='text' id='".$col['name']."' name='".$col['name']."' value='<?php if(isset($".$col['name'].")) echo $".$col['name']."; ?>' />\n";
							break;
						case 'text':
							$form .="\n\t\t<textarea id='".$col['name']."' name='".$col['name']."'><?php if(isset($".$col['name'].")) echo $".$col['name']."; ?></textarea>\n";
							break;
						default:
							$form .= "\n\t\t<input type='text' id='".$col['name']."' name='".$col['name']."' value='<?php if(isset($".$col['name'].")) echo $".$col['name']."; ?>' />\n";
							break;
					}
					$form .= "\t</div>";
				}
			}
			$form .= "\n\t<input type='submit' value='save' />\n</form>\n";

			return $form;
		}

		return "";

	}
}