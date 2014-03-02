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
Class Extension_AdminPanel_Controller_AdminPanelScaffolding extends Controller
{
	static public $layout = "admin_panel";

	static public $allowed_actions = array('index','post');

	public function index()
	{

		// get the tables model
		$this->loadmodel("Tables");

		// get all the tables witht their create statements
		$tables = $this->Tables->getStatements();

		$this->viewData('tables',$tables);
	}

	public function post($info=NULL) {

		$model = Core::instantiate("Tables");

		$tables = $model->getStatements();

		$information = array();
		$information['has_many'] = array();
		$information['belongs_to'] = array();

		foreach($tables as $index=>$table) {
			$table['structure'] = str_replace("CREATE TABLE `", "",$table['structure']);
			$table_info = explode(") ENGINE", $table['structure'])[0];
			$table = array();
			$table['cols_info'] = explode("\n", $table_info);
			$information['tables'][$index]['name'] = Core::toCam(str_replace("` (", "", $table['cols_info'][0]));
			unset($table['cols_info'][0]);
			$information['tables'][$index]['cols'] = array();
			foreach($table['cols_info'] as $col) {
				if(strpos($col, "FOREIGN KEY")) {
					$key_info = explode("REFERENCES `", $col);
					$key_table = Core::toCam(explode("` ", $key_info[1])[0]);
					if(!isset($information['has_many'][$key_table])) $information['has_many'][$key_table] = array();
					if(!isset($information['belongs_to'][$information['tables'][$index]['name']])) $information['belongs_to'][$information['tables'][$index]['name']] = array();
					array_push($information['has_many'][$key_table],$information['tables'][$index]['name']);
					array_push($information['belongs_to'][$information['tables'][$index]['name']],Core::toCam($key_table));

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

		if(isset($info['layout']))
		{
			$layout = $this->_viewLayout();
			file_put_contents(SYSTEM_PATH."views/layouts/page.php", $layout);
		}

		foreach ($information['tables'] as $table)
		{

			// create the name with underscores
			$underscores = Core::toDb($table['name']);

			// if this table is not one that we want to build scaffolding stop the current iteration
			if(!isset($info[$underscores])) continue;

			// create the normal name for the database
			$normal = Core::toNorm($table['name']);

			// if we want to build the controller
			if(isset($info[$underscores]["controller"]))
			{

				// the path to the controller
				$controller_path = SYSTEM_PATH."controllers/".$table['name']."Controller.php";

				$controller_info = $info[$underscores]["controller"];

				// if the controller file doesn't exist
				if(!is_file($controller_path))
				{
					// set the basic top setup
					$controller = $this->_controllerBase($normal,$underscores,$table['name']);

					// if we want the index function build and add it
					if(isset($controller_info['index'])) $controller .= $this->_controllerIndex($normal,$underscores,$table['name']);

					// if we want the get function build and add it
					if(isset($controller_info['get'])) $controller .= $this->_controllerGet($normal,$underscores,$table['name']);

					// if we want the post function build and add it
					if(isset($controller_info['post'])) $controller .= $this->_controllerPost($normal,$underscores,$table['name']);

					// if we want the update function build and add it
					if(isset($controller_info['update'])) $controller .= $this->_controllerUpdate($normal,$underscores,$table['name']);

					// if we want the delete function build and add it
					if(isset($controller_info['delete'])) $controller .= $this->_controllerDelete($normal,$underscores,$table['name']);

					// close the class
					$controller .= "\n}";

				}
				else
				{


					$controller = file_get_contents($controller_path);
					$controller = preg_split("/(extends Controller\s+{\s+)/", $controller, NULL, PREG_SPLIT_DELIM_CAPTURE);
					$controller[2] = substr(trim($controller[2]),0,-1);
					$controller[3] = "\n}";

					$functions = preg_split("/((?=\/\*\*)[.\n\s\S]*?(?<=\*\/))|(public function )|(private function )/", $controller[2],NULL,PREG_SPLIT_DELIM_CAPTURE);

					foreach ($functions as $index=>$function) {

						if(isset($controller_info['index']) && strpos($function, "index(") === 0)
						{
							$controller_info['index'] = false;
							$functions[$index] = $this->_controllerIndex($normal,$underscores,$table['name'],false);
							$functions[$index] .= "\n\t";
						}

						if(isset($controller_info['get']) && strpos($function, "get(") === 0)
						{
							$controller_info['get'] = false;
							$functions[$index] = $this->_controllerGet($normal,$underscores,$table['name'],false);
							$functions[$index] .= "\n\t";
						}

						if(isset($controller_info['post']) && strpos($function, "post(") === 0)
						{
							$controller_info['post'] = false;
							$functions[$index] = $this->_controllerPost($normal,$underscores,$table['name'],false);
							$functions[$index] .= "\n\t";
						}

						if(isset($controller_info['update']) && strpos($function, "update(") === 0)
						{
							$controller_info['update'] = false;
							$functions[$index] = $this->_controllerUpdate($normal,$underscores,$table['name'],false);
							$functions[$index] .= "\n\t";
						}

						if(isset($controller_info['delete']) && strpos($function, "delete(") === 0)
						{
							$controller_info['delete'] = false;
							$functions[$index] = $this->_controllerDelete($normal,$underscores,$table['name'],false);
							$functions[$index] .= "\n\t";
						}

					}

					foreach($controller_info as $type=>$ran)
					{

						if($ran)
						{
							$method_name = "_controller_".$type;
							array_push($functions, $this->$method_name($normal,$underscores,$table['name']));
						}
					}

					$controller[2] = implode("", $functions);

					$controller = implode("", $controller);

				}

				// write the file
				file_put_contents($controller_path,$controller);

			}

			if(isset($info[$underscores]["model"]))
			{

				// the path to the model
				$model_path = SYSTEM_PATH."models/".$table['name'].".php";

				// create the model
				$model = "";

				// model information
				$model_information = $info[$underscores]["model"];

				// if the model file doesn't already exist
				if(!is_file($model_path))
				{
					$model = "<?php\nClass ".$table['name']." extends Model\n{\n";


					if(isset($information['belongs_to'][$table['name']]) && isset($model_information["belongs_to"]))
						$model .= $this->_modelBelongsTo($information['belongs_to'][$table['name']]);

					if(isset($information['has_many'][$table['name']]) && isset($model_information['has_many']))
						$model .= $this->_modelHasMany($information['has_many'][$table['name']]);

					if(!empty($table['required']) && isset($model_information['required']))
						$model .= $this->_modelRequired($table['required']);

					if(!empty($table['cols']) && isset($model_information['rules']))
						$model .= $this->_modelRules($table['cols']);

					$model .= "\n\n}";

				}
				else
				{
					$model = file_get_contents($model_path);

					$model = trim(substr(trim($model),0,-1));

					if(isset($information['belongs_to'][$table['name']]) && isset($model_information['belongs_to']))
					{
						$belongs_to = $this->_modelBelongsTo($information['belongs_to'][$table['name']]);
						if(strpos($model, '$belongs_to'))
							$model = preg_replace("/\n\t+(?=public \Sbelongs)([.\n\s\S]*?)(?<=;)\n/", $belongs_to, $model);
						else
							$model .= $belongs_to;
					}
					if(isset($information['has_many'][$table['name']]) && isset($model_information['has_many']))
					{
						$has_many = $this->_modelHasMany($information['has_many'][$table['name']]);
						if(strpos($model, '$has_many'))
							$model = preg_replace("/\n\t+(?=public \Shas_many)([.\n\s\S]*?)(?<=;)\n/", $has_many, $model);
						else
							$model .= $has_many;
					}
					if(!empty($table['required']) && isset($model_information['required']))
					{
						$required = $this->_modelRequired($table['required']);
						if(strpos($model, '$required'))
							$model = preg_replace("/(\n\t+?=public \Srequired)([.\n\s\S]*?)(?<=;)\n/", $required, $model);
						else
							$model .= $required;
					}
					if(!empty($table['cols']) && isset($model_information['rules']))
					{
						$rules = $this->_modelRules($table['cols']);
						if(strpos($model, '$rules'))
							$model = preg_replace("/\n\t+(?=public \Srules)([.\n\s\S]*?)(?<=;)\n/", $rules, $model);
						else
							$model .= $rules;
					}

					$model .= "}";

				}

				file_put_contents($model_path, $model);

			}

			if(isset($info[$underscores]['view']))
			{

				// the folder for the views
				$view_folder = SYSTEM_PATH."views/".$underscores;

				if(!is_dir($view_folder))
				{

					// create the directory
					mkdir($view_folder);

				}

				$view_info = $info[$underscores]['view'];

				if($view_info['index'])
				{

					$index = $this->_viewIndex($table['cols'],$underscores,$normal);

					file_put_contents($view_folder."/index.php", $index);

				}

				if($view_info['get'])
				{

					$get = $this->_viewGet($table['cols'],$underscores);

					file_put_contents($view_folder."/get.php", $get);

				}

				if($view_info['post'])
				{

					$post = $this->_viewPost($underscores);

					file_put_contents($view_folder."/post.php", $post);

				}

				if($view_info['update'])
				{

					$update = $this->_viewUpdate($underscores);

					file_put_contents($view_folder."/update.php", $update);

				}

				if($view_info['form'])
				{

					$form = $this->_viewForm($table['cols'],$underscores);

					file_put_contents($view_folder."/_form.php", $form);

				}

			}
		}

		Core::redirect("AdminPanelScaffolding","index");
	}

	private function _createFolders()
	{
		if(!is_dir(SYSTEM_PATH."controllers"))
		{

			// create the directory
			mkdir(SYSTEM_PATH."controllers");

		}

		if(!is_dir(SYSTEM_PATH."models"))
		{

			// create the directory
			mkdir(SYSTEM_PATH."models");

		}
		if(!is_dir(SYSTEM_PATH."views"))
		{

			// create the directory
			mkdir(SYSTEM_PATH."views");

		}

		if(!is_dir(SYSTEM_PATH."views/layouts"))
		{

			// create the directory
			mkdir(SYSTEM_PATH."views/layouts");

		}
	}

	private function _controllerBase($normal,$underscores,$name)
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

	private function _controllerIndex($normal, $underscores, $name, $comments=TRUE)
	{
		$controller = "";
		if($comments)
		{
			$controller = "\n\t/**";
			$controller .= "\n\t * Get all the ".$normal."s";
			$controller .= "\n\t * @return array all the ".$normal."s";
			$controller .= "\n\t */\n\tpublic function ";
		}
		$controller .= "index()";
		$controller .= "\n\t{";
		$controller .= "\n\n\t\t// set the title of the page";
		$controller .= "\n\t\t".'$this->layoutData("title","'.$normal.' Index");';
		$controller .= "\n\n\t\t// load the model";
		$controller .= "\n\t\t".'$this->loadmodel("'.$name.'"'.");";
		$controller .= "\n\n\t\t// only get this table";
		$controller .= "\n\t\t".'$this->'.$name."->options['recursive'] = 0;";
		$controller .= "\n\n\t\t// get all the ".$normal."s";
		$controller .= "\n\t\t$".$underscores."s = ".'$this->'.$name."->findall();";
		$controller .= "\n\n\t\t//set the success";
		$controller .= "\n\t\t".'$this->viewData('."'success',".'$this->'.$name."->success);";
		$controller .= "\n\n\t\t// if the call was unsuccessful";
		$controller .= "\n\t\tif(".'!$this->'.$name."->success)";
		$controller .= "\n\t\t{";
		$controller .= "\n\n\t\t\t// send blank array";
		$controller .= "\n\t\t\t$".$underscores."s = array();";
		$controller .= "\n\n\t\t}";
		$controller .= "\n\n\t\t// set the information for the view";
		$controller .= "\n\t\t".'$this->viewData("'.$underscores.'s",$'.$underscores."s);";
		$controller .= "\n\n\t\t// return the information";
		$controller .= "\n\t\treturn $".$underscores."s;";

		$controller .= "\n\t}";

		return $controller;
	}

	private function _controllerGet($normal, $underscores, $name,$comments=TRUE)
	{
		$controller = "";
		if($comments)
		{
			$controller .= "\n\t/**";
			$controller .= "\n\t * Get one ".$normal;
			$controller .= "\n\t * @param  int the id of the ".$normal." to get";
			$controller .= "\n\t * @return one ".$normal;
			$controller .= "\n\t*/\n\tpublic function ";
		}
		$controller .= "get(".'$id'.")";
		$controller .= "\n\t{";
		$controller .= "\n\n\t\t// set the title of the page";
		$controller .= "\n\t\t".'$this->layoutData("title","'.$normal.' Get");';
		$controller .= "\n\n\t\tif(".'$id'.")";
		$controller .= "\n\t\t{";
		$controller .= "\n\n\t\t\t// load the model";
		$controller .= "\n\t\t\t".'$this->loadmodel("'.$name.'"'.");";
		$controller .= "\n\n\t\t\t// only get this table";
		$controller .= "\n\t\t\t".'$this->'.$name."->options['recursive'] = 0;";
		$controller .= "\n\n\t\t\t// get all the ".$normal."s";
		$controller .= "\n\t\t\t$".$underscores." = ".'$this->'.$name."->findbyid(".'$id'.");";
		$controller .= "\n\n\t\t\t//set the success";
		$controller .= "\n\t\t\t".'$this->viewData('."'success',".'$this->'.$name."->success);";
		$controller .= "\n\n\t\t\t// if the call was successful";
		$controller .= "\n\t\t\tif(".'$this->'.$name."->success)";
		$controller .= "\n\t\t\t{";
		$controller .= "\n\n\t\t\t\t// set the information for the view";
		$controller .= "\n\t\t\t\t".'$this->viewData("'.$underscores.'",$'.$underscores."[0]);";
		$controller .= "\n\n\t\t\t\t// return the information";
		$controller .= "\n\t\t\t\treturn $".$underscores."[0];";
		$controller .= "\n\t\t\t}";
		$controller .= "\n\t\t\treturn false;";
		$controller .= "\n\t\t}";
		$controller .= "\n\n\t}";

		return $controller;
	}

	private function _controllerPost($normal, $underscores, $name,$comments=TRUE)
	{
		$controller = "";
		if($comments)
		{
			$controller = "\n\t/**";
			$controller .= "\n\t * Create new ".$normal;
			$controller .= "\n\t * @param  array $".$underscores." all the information to save";
			$controller .= "\n\t * @return boolean if it was successfull";
			$controller .= "\n\t */";
			$controller .= "\n\tpublic function ";
		}
		$controller .= "post($".$underscores."=NULL)";
		$controller .= "\n\t{";
		$controller .= "\n\n\t\t// set the title of the page";
		$controller .= "\n\t\t".'$this->layoutData("title","'.$normal.' Post");';
		$controller .= "\n\n\t\t//if information was sent";
		$controller .= "\n\t\tif($".$underscores.")";
		$controller .= "\n\t\t{";
		$controller .= "\n\t\t\t// load the model";
		$controller .= "\n\t\t\t".'$this->loadmodel("'.$name.'"'.");";
		$controller .= "\n\n\t\t\t// save the new ".$normal;
		$controller .= "\n\t\t\t$".$underscores.'_id = $this->'.$name."->save($".$underscores.");";
		$controller .= "\n\n\t\t\t// set the success";
		$controller .= "\n\t\t\t".'$this->viewData("success",$this->'.$name."->success);";
		$controller .= "\n\n\t\t\t".'if($this->'.$name.'->success)';
		$controller .= "\n\t\t\t{";
		$controller .= "\n\n\t\t\t\t// set the ".$underscores." id if the save was successful";
		$controller .= "\n\t\t\t\t".'$this->viewData("'.$underscores.'_id",$'.$underscores.'_id);';
		$controller .= "\n\n\t\t\t\t// return the ".$name." id";
		$controller .= "\n\t\t\t\t".'return $'.$underscores.'_id;';
		$controller .= "\n\n\t\t\t}";
		$controller .= "\n\t\t\telse";
		$controller .= "\n\t\t\t{";
		$controller .= "\n\n\t\t\t\t// set the errors because something went wrong";
		$controller .= "\n\t\t\t\t".'$this->viewData("errors",$this->'.$name.'->error);';
		$controller .= "\n\n\t\t\t\t// set the $normal so that you have the already inputed values";
		$controller .= "\n\t\t\t\t".'$this->viewData("'.$underscores.'",$'.$underscores.");";
		$controller .= "\n\n\t\t\t\t// return the success";
		$controller .= "\n\t\t\t\t".'return $this->'.$name."->success;";
		$controller .= "\n\t\t\t}";
		$controller .= "\n\t\t}";
		$controller .= "\n\t}";

		return $controller;
	}

	private function _controllerUpdate($normal, $underscores, $name,$comments=TRUE)
	{
		$controller = "";
		if($comments)
		{
			$controller = "\n\t/**";
			$controller .= "\n\t * Update a ".$normal;
			$controller .= "\n\t * @param  array $".$underscores." all the information to update, including id";
			$controller .= "\n\t * @return boolean if it was successfull";
			$controller .= "\n\t */";
			$controller .= "\n\tpublic function ";
		}
		$controller .= "update($".$underscores."_id=NULL,$".$underscores."=NULL)";
		$controller .= "\n\t{";
		$controller .= "\n\n\t\t// set the title of the page";
		$controller .= "\n\t\t".'$this->layoutData("title","'.$normal.' Update");';
		$controller .= "\n\n\t\t// if information was sent";
		$controller .= "\n\t\tif($".$underscores.")";
		$controller .= "\n\t\t{";
		$controller .= "\n\n\t\t\t// if there is no id in the form set it from the url";
		$controller .= "\n\t\t\tif(!isset($".$underscores."['id'])) $".$underscores."['id'] = $".$underscores."_id;";
		$controller .= "\n\n\t\t\t// load the model";
		$controller .= "\n\t\t\t".'$this->loadmodel("'.$name.'"'.");";
		$controller .= "\n\n\t\t\t// save the new ".$normal;
		$controller .= "\n\t\t\t".'$this->'.$name."->save($".$underscores.");";
		$controller .= "\n\n\t\t\t// set the success";
		$controller .= "\n\t\t\t".'$this->viewData("success",$this->'.$name."->success);";
		$controller .= "\n\n\t\t\t// if the save was not successful";
		$controller .= "\n\t\t\t".'if(!$this->'.$name.'->success)';
		$controller .= "\n\t\t\t{";
		$controller .= "\n\t\t\t\t// set the errors";
		$controller .= "\n\t\t\t\t".'$this->viewData("errors",$this->'.$name.'->error);';
		$controller .= "\n\t\t\t}";
		$controller .= "\n\t\t}";
		$controller .= "\n\n\t\t// if there is an id";
		$controller .= "\n\t\tif($".$underscores."_id)";
		$controller .= "\n\t\t{";
		$controller .= "\n\t\t\t\n\t\t\t// get a ".$normal;
		$controller .= "\n\t\t\t".'$this->get($'.$underscores."_id);";
		$controller .= "\n\t\t\t\n\t\t}";
		$controller .= "\n\n\t\t// return the success message";
		$controller .= "\n\t\t".'return $this->'.$name.'->success;';
		$controller .= "\n\n\n\t}";

		return $controller;
	}

	private function _controllerDelete($normal,$underscores,$name,$comments=TRUE)
	{
		$controller = "";
		if($comments)
		{
			$controller = "\n\t/**";
			$controller .= "\n\t * Delete a ".$normal;
			$controller .= "\n\t * @param  int $".$underscores."_id id of the ".$normal." to delete";
			$controller .= "\n\t * @return boolean if it was successfull";
			$controller .= "\n\t */";
			$controller .= "\n\tpublic function ";
		}
		$controller .= "delete($".$underscores."_id=NULL)";
		$controller .= "\n\t{";
		$controller .= "\n\t\t// if there was an id sent";
		$controller .= "\n\t\tif($".$underscores."_id)";
		$controller .= "\n\t\t{";
		$controller .= "\n\n\t\t\t// load the model";
		$controller .= "\n\t\t\t".'$this->loadmodel("'.$name.'"'.");";
		$controller .= "\n\n\t\t\t// save the new ".$normal;
		$controller .= "\n\t\t\t".'$this->'.$name."->delete($".$underscores."_id);";
		$controller .= "\n\n\t\t\t// set the success";
		$controller .= "\n\t\t\t".'$this->viewData("success",$this->'.$name."->success);";
		$controller .= "\n\n\t\t\t// set the success";
		$controller .= "\n\t\t\t".'$this->'.$name."->success;";
		$controller .= "\n\n\t\t\t//return to the page that called it if there is one";
		$controller .= "\n\t\t\t".'if(isset($_SERVER["HTTP_REFERER"])) header("Location: ".$_SERVER["HTTP_REFERER"]);';
		$controller .= "\n\n\t\t\t//else return the success";
		$controller .= "\n\t\t\t else return ".'$this->'.$name.'->success;';
		$controller .= "\n\n\t\t}";
		$controller .= "\n\t}";

		return $controller;
	}

	private function _modelBelongsTo($tables)
	{
		$model = "\tpublic ".'$belongs_to'." = array('";
		$model .= implode("','", $tables);
		$model .= "');\n\n";

		return $model;
	}

	private function _modelHasMany($tables)
	{
		$model = "\tpublic ".'$has_many'." = array('";
		$model .= implode("','", $tables);
		$model .= "');\n\n";

		return $model;
	}

	private function _modelRequired($tables)
	{
		$model = "\tpublic ".'$required'." = array('";
		$model .= implode("','", $tables);
		$model .= "');\n\n";

		return $model;
	}

	private function _modelRules($cols)
	{
		$model = "\tpublic ".'$rules = '."array(";

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
		$model .= "\n\t\t);\n\n";

		return $model;
	}

	private function _viewLayout()
	{
		$layout  = "<html>";
		$layout .= "\n\t<head>";
		$layout .= "\n\t\t<title><?php echo $".'title'.".' - ".$_POST['application_name']."' ?></title>";
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

	private function _viewIndex($cols, $underscores,$normal)
	{

		$index_titles =  "";
		$index_rows =  "";
		if(!empty($cols))
		{

			foreach($cols as $col)
			{

				$index_titles .= "\n\t\t<div class='col'>".$col['name']."</div>";
				$index_rows .= "\n\t\t\t<div class='col'>\n\t\t\t\t<?php echo $".$underscores."['".$col['name']."'] ?>\n\t\t\t</div>";

			}
		}
		$index = "<p>".'<a href="<?php echo Asset::createUrl("'.$normal.'","post")?>">Add New</a>'."</p>";
		$index .= "<div class='table'>";
		$index .= "\n\t<div class='row'>".$index_titles."\n\t</div>";
		$index .= "\n\t<?php if(isset($".$underscores."s)): foreach($".$underscores."s as ".'$'.$underscores."):?>";
		$index .= "\n\t\t<div class='row'>";
		$index .= $index_rows;
		$index .= "\n\t\t\t<div class='col'>";
		$index .= "\n\t\t\t"."<a href='<?php echo Asset::createUrl('" . $normal . "','get',array($" . $underscores . "['id']))?>'>View</a>";
		$index .= "\n\t\t\t"."<a href='<?php echo Asset::createUrl('" . $normal . "','update',array($" . $underscores . "['id']))?>'>Edit</a>";
		$index .= "\n\t\t\t"."<a href='<?php echo Asset::createUrl('" . $normal . "','delete',array($" . $underscores . "['id']))?>'>Delete</a>";
		$index .= "\n\t\t\t</div>";
		$index .= "\n\t\t</div>";
		$index .= "\n\t<?php endforeach;?>";
		$index .= "\n</div>";
		$index .= "\n\t<?php else: ?>";
		$index .= "\n</div>";
		$index .= "\n\t\t<p>No Results Found</p>";
		$index .= "\n\t<?php endif;?>";

		return $index;
	}

	private function _viewGet($cols,$underscores)
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

	private function _viewPost($underscores)
	{

		$post = "<?php ";
		$post .= "\n\t".'$params = isset($'.$underscores.')?$'.$underscores.':array();';
		$post .= "\n\t".'if(isset($errors))$params = array_merge($params, $errors);';
		$post .= "\n\tView::render('".$underscores."/_form',".'$params'.");";
		$post .= "\n?>";

		return $post;
	}

	private function _viewUpdate($underscores)
	{
		$update ="<?php";
		$update .= "\n\t".'$params = isset($'.$underscores.')?$'.$underscores.':array();';
		$update .= "\n\t".'if(isset($errors))$params = array_merge($params, $errors);';
		$update .= "\n\tView::render('".$underscores."/_form',".'$params'.");\n?>";

		return $update;
	}

	private function _viewForm($cols,$underscores)
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