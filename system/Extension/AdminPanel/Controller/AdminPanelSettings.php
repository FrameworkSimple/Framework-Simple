<?php
/**
	 * The Admin Panel Settings Controller
	 */

	/**
	 * The Admin Panel Extension Settings controller
	 * @category Extensions
	 * @package  Extensions
	 * @subpackage AdminPanel
	 * @author     Rachel Higley <me@rachelhigley.com>
	 * @copyright  2013 Framework Simple
	 * @license    http://www.opensource.org/licenses/mit-license.php MIT
	 * @link       http://rachelhigley.com/framework
	 */
Class Extension_AdminPanel_Controller_AdminPanelSettings extends Controller
{

	static public $layout = "admin_panel";

	static public $allowed_actions = array('index');

	public function index($save_settings=NULL)
	{

		if($save_settings && !is_file(SYSTEM_PATH."Settings.php"))
		{

			$settings = $this->_getDefault();

			$file = "<?php \n\t/**\n\t * @ignore\n\t */";

			$index = 0;
			foreach ($save_settings as $varable => $value)
			{

				$file .= "\n\n\t/**";
				$file .= "\n\t * ".$settings[$index]['label'].": ".$settings[$index]['type'];
				$file .= "\n\t *";
				foreach ($settings[$index]['description'] as $description) {
					$file .= "\n\t * ".trim($description);
				}
				$file .= "\n\t */";
				$value = $this->_getValue($value);
				$file .= "\n\t".str_replace("-", " ", $varable)." = ".trim($value).";";

				$index++;

			}
			file_put_contents(SYSTEM_PATH."Settings.php", $file);

		}
		elseif($save_settings)
		{

			$file_name = is_file(SYSTEM_PATH."Settings.php")?SYSTEM_PATH."Settings.php":SYSTEM_PATH."Settings-Template.php";
			$settings_file = file_get_contents($file_name);

			$settings_file = preg_split("/(\/\*\*)|(\*\/)/", $settings_file);

			$new_file = "<?php\n\t/**\n\t * @ignore\n\t */";

			$settings_file = array_slice($settings_file, 3);

			foreach($settings_file as $setting)
			{
				if(strpos($setting, "*"))
				{

					$new_file .= "\n\n\t/**".$setting."*/";

				}
				else
				{
					$pieces = preg_split("( = )", $setting);
					$name = trim(str_replace(" ", "-", $pieces[0]));

					$pieces[1] = $save_settings[$name];

					$new_file .= $pieces[0]." = ".$this->_getValue($pieces[1]).";";
				}


			}

			file_put_contents(SYSTEM_PATH."Settings.php", $new_file);

			$this->_getDefault();
		}
		else
		{
			$this->_getDefault();
		}

	}

	private function _getValue($value)
	{
		if(strpos($value, "false") === false && strpos($value, "true") === false && strpos($value, "array") === false ) $value = "'".$value."'";
		$value = str_replace("\n", "", $value);

		return $value;
	}

	private function _getDefault()
	{
		$file_name = is_file(SYSTEM_PATH."Settings.php")?SYSTEM_PATH."Settings.php":SYSTEM_PATH."Settings-Template.php";

		$settings_file = file_get_contents($file_name);

		$settings_file = preg_split("/(\/\*\*)|(\*\/)/", $settings_file);

		$settings_file = array_slice($settings_file, 3);

		$new = true;

		$settings = array();
		$index = -1;

		foreach ($settings_file as &$settings_string) {

			$settings_string = trim($settings_string);

			if($new)
			{
				$new = false;
				$index++;
				$settings_info = explode("*", $settings_string);
				unset($settings_info[0]);
				unset($settings_info[2]);

				$info = explode(": ",trim($settings_info[1]));
				$settings[$index]['label'] = $info[0];
				$settings[$index]['type'] = $info[1];
				$settings_info = array_splice($settings_info, 1);
				$settings[$index]['description'] = $settings_info;
			}
			else
			{
				$new = true;
				$info =  preg_split("/( = )|(;)/", $settings_string);
				$settings[$index]['name'] = str_replace(" ", "-", $info[0]);
				$settings[$index]['default'] = $info[1];
			}


		}

		$this->viewData("settings",$settings);

		return $settings;
	}

}