<?php

Class SettingsController extends Controller
{

	public function post($save_settings=NULL)
	{

		if($save_settings && !is_file(SYSTEM_PATH."/Settings.php"))
		{

			$settings = $this->_get_default();

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
				$value = $this->_get_value($value);
				$file .= "\n\t".str_replace("-", " ", $varable)." = ".trim($value).";";

				$index++;

			}
			file_put_contents(SYSTEM_PATH."/Settings.php", $file);

		}
		elseif($save_settings)
		{
			$settings_file = file(SYSTEM_PATH."/Settings.php");

			$pattern = "/";
			foreach (array_reverse($save_settings) as $varable => $value) {
				$varable = str_replace("$", '\$', $varable);
				$pattern .= str_replace("-", " ", $varable)."|";
			}
			$pattern = substr($pattern, 0, -1);
			$pattern .= "/";

			foreach($settings_file as &$line)
			{
				preg_match($pattern, $line,$matches);
				if(!empty($matches[0])) {

					$name = str_replace(" ", "-", $matches[0]);

					$value = $save_settings[$name];
					$value = $this->_get_value($value);

					$line = "\t".$matches[0]." = ".$value.";\n";
					unset($save_settings[$name]);
				}
			}
			foreach ($save_settings as $varable => $value) {

				$value = $this->_get_value($value);
				$settings_file .= "\n\n".str_replace("-", " ", $varable)." = ".$value.";";
			}

			file_put_contents(SYSTEM_PATH."/Settings.php", $settings_file);

			$this->_get_default();
		}
		else
		{
			$this->_get_default();
		}

	}

	private function _get_value($value)
	{
		if(strpos($value, "false") === false && strpos($value, "true") === false && strpos($value, "array") === false ) $value = "'".$value."'";
		$value = str_replace("\n", "", $value);

		return $value;
	}

	private function _get_default()
	{
		$file_name = is_file(SYSTEM_PATH."/Settings.php")?SYSTEM_PATH."/Settings.php":SYSTEM_PATH."/Settings-Template.php";

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

		$this->view_data("settings",$settings);

		return $settings;
	}

}