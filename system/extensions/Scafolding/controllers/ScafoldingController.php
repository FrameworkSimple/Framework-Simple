<?php
/**
 * The Scafolding Controller
 */

/**
 * The Russian Doll Caching Extension controller
 * @category Extensions
 * @package  Extensions
 * @subpackage Scafolding
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
Class ScafoldingController extends Controller {

	public static $layout = false;

	//public static $path_to_views = "/extensions/Scafolding/views/";
	/**
	 * The Default Upload View
	 */
	public function index()
	{



	}

	public function post() {

		$model = Core::instantiate("Scafolding");

		$tables = $model->get_statements();

		$information = array();
		$information['hasMany'] = array();
		$information['belongsTo'] = array();

		foreach($tables as $index=>$table) {
			$table_info = explode(") ENGINE", $table)[0];
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

		$layout = "<html>\n\t<head>\n\t\t<title>Scafolding Page</title>\n\t<style type='text/css'>.col{display:table-cell;border:1px solid #000;padding:5px;} .table{display:table;width:100%;border:1px solid #000;} .row{display:table-row;}</style>\n\t</head>\n\t<body>\n\t\t<?php echo ".'$content_for_layout'."?>\n\t</body>\n</html>";
		file_put_contents(SYSTEM_PATH."/views/layouts/scafolding.php", $layout);
		foreach ($information['tables'] as $table)
		{
			$normal = Core::to_norm($table['name']);
			$underscores = Core::to_db($table['name']);
			$controller = "<?php\n/**\n * The ".$normal." Controller\n */\n\n/**\n * The ".$normal." Controller\n * @category   Controllers\n * @package    ".$_POST['application_name']."\n * @subpackage Controllers\n * @author     ".$_POST['name']."\n */\n Class ".$table['name']."Controller extends Controller\n{";
			$controller .= "\n\t/**\n\t * Get all the ".$normal."s\n\t * @return array all the ".$normal."s\n\t */\n\tpublic function index()\n\t{\n\n\t\t// load the model\n\t\t".'$this->loadModel("'.$table['name'].'"'.");\n\n\t\t// only get this table\n\t\t".'$this->'.$table['name']."->options['recursive'] = 0;\n\n\t\t// get all the ".$normal."s\n\t\t$".$underscores."s = ".'$this->'.$table['name']."->findAll();\n\n\t\t//set the success\n\t\t".'$this->view_data('."'success',".'$this->'.$table['name']."->success);\n\n\t\t// if the call was successful\n\t\tif(".'$this->'.$table['name']."->success)\n\t\t{\n\n\t\t\t// set the information for the view\n\t\t\t".'$this->view_data("'.$underscores.'s",$'.$underscores."s);\n\n\t\t\t// return the information\n\t\t\treturn $".$underscores."s;\n\n\t\t}\n\t}";
			$controller .= "\n\t/**\n\t * Get one ".$normal."\n\t * @param  int the id of the ".$normal." to get\n\t * @return one ".$normal."\n\t*/\n\tpublic function get(".'$id'.")\n\t{\n\t\tif(".'$id'.")\n\t\t{\n\n\t\t\t// load the model\n\t\t\t".'$this->loadModel("'.$table['name'].'"'.");\n\n\t\t\t// only get this table\n\t\t\t".'$this->'.$table['name']."->options['recursive'] = 0;\n\n\t\t\t// get all the ".$normal."s\n\t\t\t$".$underscores." = ".'$this->'.$table['name']."->findById(".'$id'.");\n\n\t\t\t//set the success\n\t\t\t".'$this->view_data('."'success',".'$this->'.$table['name']."->success);\n\n\t\t\t// if the call was successful\n\t\t\tif(".'$this->'.$table['name']."->success)\n\t\t\t{\n\n\t\t\t\t// set the information for the view\n\t\t\t\t".'$this->view_data("'.$underscores.'",$'.$underscores.");\n\n\t\t\t\t// return the information\n\t\t\t\treturn $".$underscores.";\n\t\t\t}\n\t\t}\n\n\t}";
			$controller .= "\n\t/**\n\t * Create new ".$normal."\n\t * @param  array $".$underscores." all the information to save\n\t * @return boolean if it was successfull\n\t */\n\tpublic function post($".$underscores."=NULL)\n\t{\n\t\t//if information was sent\n\t\tif($".$underscores.")\n\t\t{\n\t\t\t// load the model\n\t\t\t".'$this->loadModel("'.$table['name'].'"'.");\n\n\t\t\t// save the new ".$normal."\n\t\t\t".'$this->'.$table['name']."->save($".$underscores.");\n\n\t\t\t// set the success\n\t\t\t".'$this->view_data("success",$this->'.$table['name']."->success);\n\n\t\t\t// return the success\n\t\t\t".'$this->'.$table['name']."->success;\n\t\t}\n\t}";
			$controller .= "\n\t/**\n\t * Update a ".$normal."\n\t * @param  array $".$underscores." all the information to update, including id\n\t * @return boolean if it was successfull\n\t */\n\tpublic function update($".$underscores."_id=NULL,$".$underscores."=NULL)\n\t{\n\n\t\t// if information was sent\n\t\tif($".$underscores.")\n\t\t{\n\t\t\t// load the model\n\t\t\t".'$this->loadModel("'.$table['name'].'"'.");\n\n\t\t\t// save the new ".$normal."\n\t\t\t".'$this->'.$table['name']."->save($".$underscores.");\n\n\t\t\t// set the success\n\t\t\t".'$this->view_data("success",$this->'.$table['name']."->success);\n\n\t\t\t// return the success\n\t\t\t".'$this->'.$table['name']."->success;\n\t\t}\n\n\t\t// if there is an id\n\t\tif($".$underscores."_id)\n\t\t{\n\t\t\t\n\t\t\t// get a ".$normal."\n\t\t\t".'$this->get($'.$underscores."_id);\n\t\t\t\n\t\t}\n\n\n\t}";
			$controller .= "\n\t/**\n\t * Delete a ".$normal."\n\t * @param  int $".$underscores."_id id of the ".$normal." to delete\n\t * @return boolean if it was successfull\n\t */\n\tpublic function delete($".$underscores."_id=NULL)\n\t{\n\t\t// if there was an id sent\n\t\tif($".$underscores."_id)\n\t\t{\n\n\t\t\t// load the model\n\t\t\t".'$this->loadModel("'.$table['name'].'"'.");\n\n\t\t\t// save the new ".$normal."\n\t\t\t".'$this->'.$table['name']."->delete($".$underscores."_id);\n\n\t\t\t// set the success\n\t\t\t".'$this->view_data("success",$this->'.$table['name']."->success);\n\n\t\t\t// return the success\n\t\t\t".'$this->'.$table['name']."->success;\n\n\t\t}\n\t}";
			$controller .= "\n}";
			file_put_contents(SYSTEM_PATH."/controllers/".$table['name']."Controller.php", $controller);

			$model = "<?php\nClass ".$table['name']." extends Model\n{\n";

			if(isset($information['belongsTo'][$table['name']]))
			{
				$model .= "\n\tpublic ".'$belongsTo'." = array('";
				$model .= implode("','", $information['belongsTo'][$table['name']]);
				$model .= "');\n";
			}
			if(isset($information['hasMany'][$table['name']]))
			{
				$model .= "\n\tpublic ".'$hasMany'." = array('";
				$model .= implode("','", $information['hasMany'][$table['name']]);
				$model .= "');\n";
			}
			if(!empty($table['required']))
			{
				$model .= "\n\tpublic ".'$required'." = array('";
				$model .= implode("','", $table['required']);
				$model .= "');\n";
			}

			if(!empty($table['cols']))
			{
				$model .= "\n\tpublic ".'$rules = '."array(";
				$form = '<?php $params = array(); if(isset($id)) $params[0] = $id; ?>'."\n<form method='POST' action='<?= Asset::create_url('".$table['name']."',".'$action'.",".'$params'.") ?>'>\n";
				foreach($table['cols'] as $col)
				{
					$model .= "\n\t\t'".$col['name']."' => array(";
					$form .= "\n\t\t<div>\n\t\t<label for='".$col['name']."'>".$col['name']."</label>";
					switch ($col['type']) {
						case 'int':
							$model .= "'numeric',";
							$form .= "\n\t\t<input type='text' id='".$col['name']."' name='".$col['name']."' size='".$col['length']."' value='<?php if(isset($".$col['name'].")) echo $".$col['name']."; ?>' />\n";
							break;

						case 'varchar':
							$model .= "'alphaNumeric',";
							$form .= "\n\t\t<input type='text' id='".$col['name']."' name='".$col['name']."' value='<?php if(isset($".$col['name'].")) echo $".$col['name']."; ?>' />\n";
							break;

						case 'timestamp':
							$model .= "'timestamp',";
							$form .= "\n\t\t<input type='text' id='".$col['name']."' name='".$col['name']."' value='<?php if(isset($".$col['name'].")) echo $".$col['name']."; ?>' />\n";
							break;
						case 'text':
							$model .= " ";
							$form .="\n\t\t<textarea id='".$col['name']."' name='".$col['name']."'><?php if(isset($".$col['name'].")) echo $".$col['name']."; ?></textarea>\n";
							break;
						default:
							$model .= " ";
							$form .= "\n\t\t<input type='text' id='".$col['name']."' name='".$col['name']."' value='<?php if(isset($".$col['name'].")) echo $".$col['name']."; ?>' />\n";
							break;
					}

					$form .= "\n\t\t</div>";


					if(isset($col['length']))
					{
						$model .= "'maxLength' =>".$col['length'].",";
					}

					$model = substr($model, 0,-1);
					$model .= "), ";

				}
				$model = substr($model, 0, -2);
				$model .= "\n\t\t);\n";
				$form .= "\n\t<input type='submit' value='save' />\n</form>\n";
			}

			$model .= "\n\n}";


			file_put_contents(SYSTEM_PATH."/models/".$table['name'].".php", $model);

			if(!is_dir(SYSTEM_PATH."/views/".$underscores))
			{

				// create the directory
				mkdir(SYSTEM_PATH."/views/".$underscores);

			}


			file_put_contents(SYSTEM_PATH."/views/".$underscores."/_form.php", $form);

			$index = "<div class='table'>\n\t<div class='row'>\n\t\t<?php foreach(array_keys($".$underscores."s[0]) as ".'$key'."):?>\n\t\t\t<div class='col'>".'<?= $key ?>'."</div>\n\t\t<?php endforeach?>\n\t</div>\n\t<?php foreach($".$underscores."s as ".'$row'."):?>\n\t\t<div class='row'>\n\t\t<?php foreach(".'$row'." as ".'$col'."):?>\n\t\t\t<div class='col'>".'<?= $col ?>'."</div>\n\t\t<?php endforeach ?>\n\t\t</div>\n\t<?php endforeach; ?>\n</div>";
			file_put_contents(SYSTEM_PATH."/views/".$underscores."/index.php", $index);

			$get = "<?php foreach($".$underscores."[0] as ".'$key=>$col'."):?>\n\t\t<div class='row'><div class='col'><?=".'$key'." ?></div><div class='col'><?=".'$col'."?></div></div>\n\t<?php endforeach;?>";
			file_put_contents(SYSTEM_PATH."/views/".$underscores."/get.php", $get);

			$post = "<?php View::render('".$underscores."/_form',array('action'=>'post')); ?>";
			file_put_contents(SYSTEM_PATH."/views/".$underscores."/post.php", $post);

			$update ="<?php\n\t$".$underscores."[0]['action'] = 'update';\n\tView::render('action_type/_form',$".$underscores."[0]);\n?>";
			file_put_contents(SYSTEM_PATH."/views/".$underscores."/update.php", $update);

			file_put_contents(SYSTEM_PATH."/views/".$underscores."/delete.php", "<h1>Hello World</h1>");
		}
	}

}