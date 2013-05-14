<?php
/**
 * Holds all the validation methods
 */

/**
 * This is used before saving to check if the information being saved is in valid format.
 * @category   Core
 * @package    Core
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
class Validation {

	/**
	 * tableName: string
	 *
	 * the name of the table in db format
	 * @var string
	 */
	private $tableName;

	/**
	 * name: string
	 *
	 * the name of the table in model formation
	 * @var string
	 */
	private $name;

	/**
	 * data
	 *
	 * the data to be validated
	 * @var array
	 */
	private $data;

	/**
	 * db: PDO
	 *
	 * the database PDO
	 * @var PDO
	 */
	public $db;

	/**
	 * errors: array
	 *
	 * all the errors we found
	 * @var array
	 */
	public $errors;

	/**
	 * validate: array
	 *
	 * the rules to validate by
	 * @var array
	 */
	public $validate;

	/**
	 * required: array
	 *
	 * the array of required fields
	 * @var array
	 */
	public $required;

	/**
	 * Validate the information
	 * @param  string $tableName the name of the table in model formation
	 * @param  array $data      the datate to validate
	 * @param  array $required  the required fields
	 * @param  array $rules     the rulles to valiate on
	 * @return array/boolean    true if no errors and errors array if errors
	 */
	public function validate($tableName,$data,$required,$rules,$new=true) {
		$this->tableName = Core::to_db($tableName);
		$this->name = $tableName;
		$this->data = $data;
		$this->errors = array();
		$this->required = $required;
		$this->validate = $rules;
		$this->new = $new;
		if(!empty($this->required) && $new) {
			foreach($this->required as $col) {
				if(!isset($this->data[$col]) || (empty($this->data[$col]) || !self::_check($this->data[$col],'/[^\s]+/m',$col))) {
					unset($this->data[$col]);
					$this->errors[$col] = Core::to_norm($col)." is required";
				}
			}
		}
		if(!empty($this->validate)) {
			foreach($this->data as $col=>$value) {
				if(isset($this->validate[$col]) && !empty($this->data[$col])) {
					if(gettype($this->validate[$col])=="string") {
						$method = "_".$this->validate[$col];

						if(!$this->$method($value,$col)){
							break;
						};
					}elseif($this->validate[$col] == array_values($this->validate[$col])) {
						foreach ($this->validate[$col] as $val) {
							$method = "_".$val;
							if(!$this->$method($value,$col)){
								break;
							};
						}
					}else {
						foreach($this->validate[$col] as $key=>$val) {
							if(gettype($key) === "integer")
							{
								$method = "_".$val;
								if(!$this->$method($value,$col)){
									break;
								};
							}
							else
							{
								$method = "_".$key;
								if(!$this->$method($value,$col,$val)){
									break;
								};
							}

						}
					}
				}
				else if(empty($this->data[$col]) && in_array($col, $this->required))
				{

					$this->_createError($col,"can not be empty");
				}

			}
		}
		if(!empty($this->errors)) {
			return $this->errors;
		}
		return true;
	}

	/**
	 * check against a regular expression
	 * @param  string $check       the string to check
	 * @param  string $regex       the regular expression to check against
	 * @param  string $col         the column name
	 * @param  string $errorString the error string to use
	 * @return boolean              if the check was successful
	 */
	private function _check($check, $regex, $col,$errorString=NULL) {
		$bool =  preg_match($regex, $check);
		if(!$bool && $col) {
			if($errorString) {
				$this->_createError($col,$errorString);
			}else {
				$this->_createError($col,"has an error");
			}
			return $bool;
		}
		return $bool;
	}

	/**
	 * create an error for the errors array
	 * @param  string  $col         the name of the column
	 * @param  string  $errorString the error string to use
	 * @param  boolean $bool        if there needs to be an array
	 * @return boolean               if a error was added
	 */
	private function _createError($col,$errorString,$bool=FALSE) {
		if(!$bool) {
			$this->errors[$col] = Core::to_norm($col)." ".$errorString;
			unset($this->data[$col]);
			return false;
		}
		return true;
	}

	/**
	 * Checks if the value matches a regular expression
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("regex"=> "/^$/"));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string, and the regular expression
	 * @return boolean       if the data was valid
	 */
	private function _regex($val,$col,$value) {
		$errorString = isset($value["error"])?$value["error"]:"did not meet requirement";
		$regex = isset($value[0])?$value[0]:$value;
		return $this->_check($val,$regex,$col,$errorString);
	}

	/**
	 * Checks if the value only includes Letters and Numbers
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("alphaNumeric"));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string
	 * @return boolean       if the data was valid
	 */
	private function _alphaNumeric($val,$col,$value=NULL) {
		$errorString = isset($value["error"])?$value["error"]:"can only be letters and numbers";
		$regex = '/^[a-zA-Z0-9\s\p{P}]+$/mu';
		return $this->_check($val,$regex,$col,$errorString);
	}

	/**
	 * Checks if the value is between two numbers
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("between"=>array(1,5)));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string, and the numbers to check betweent
	 * @return boolean       if the data was valid
	 */
	private function _between($val,$col,$value) {
		$errorString = isset($value["error"])?$value["error"]:"is test short";
		$min = $value[0];
		$max = $value[1];
		$length = mb_strlen($val);
		$bool = ($length >= $min && $length <= $max);
		return $this->_createError($col,$errorString,$bool);
	}

	/**
	 * Checks if the value is a boolean, possible booleans are 0,1,"0","1",true,false
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("boolean"));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string
	 * @return boolean       if the data was valid
	 */
	private function _boolean($val,$col,$value = NULL) {
		$errorString = isset($value["error"])?$value["error"]:"is not a boolean";
		$booleanList = array(0, 1, '0', '1', true, false);
		return $this->_createError($col,$errorString,in_array($val, $booleanList, true));
	}

	/**
	 * Checks if the value is a valid credit card number
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("cc"));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string
	 * @return boolean       if the data was valid
	 */
	private function _cc($val,$col,$value=NULL) {
		$errorString = isset($value["error"])?$value["error"]:"is not a valid credit card number";
		$val = str_replace(array('-', ' '), '', $val);
		if (mb_strlen($val) < 13) {
			return false;
		}
		$cards = array(
			'all' => array(
				'amex'		=> '/^3[4|7]\\d{13}$/',
				'bankcard'	=> '/^56(10\\d\\d|022[1-5])\\d{10}$/',
				'diners'	=> '/^(?:3(0[0-5]|[68]\\d)\\d{11})|(?:5[1-5]\\d{14})$/',
				'disc'		=> '/^(?:6011|650\\d)\\d{12}$/',
				'electron'	=> '/^(?:417500|4917\\d{2}|4913\\d{2})\\d{10}$/',
				'enroute'	=> '/^2(?:014|149)\\d{11}$/',
				'jcb'		=> '/^(3\\d{4}|2100|1800)\\d{11}$/',
				'maestro'	=> '/^(?:5020|6\\d{3})\\d{12}$/',
				'mc'		=> '/^5[1-5]\\d{14}$/',
				'solo'		=> '/^(6334[5-9][0-9]|6767[0-9]{2})\\d{10}(\\d{2,3})?$/',
				'switch'	=> '/^(?:49(03(0[2-9]|3[5-9])|11(0[1-2]|7[4-9]|8[1-2])|36[0-9]{2})\\d{10}(\\d{2,3})?)|(?:564182\\d{10}(\\d{2,3})?)|(6(3(33[0-4][0-9])|759[0-9]{2})\\d{10}(\\d{2,3})?)$/',
				'visa'		=> '/^4\\d{12}(\\d{3})?$/',
				'voyager'	=> '/^8699[0-9]{11}$/'
			),
			'fast' => '/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{12}|3(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$/'
		);
		foreach ($cards['all'] as $value) {
			$regex = $value;
			if ($this->_check($val, $regex)) {
				$sum = 0;
				$length = strlen($val);

				for ($position = 1 - ($length % 2); $position < $length; $position += 2) {
					$sum += $val[$position];
				}
				for ($position = ($length % 2); $position < $length; $position += 2) {
					$number = $val[$position] * 2;
					$sum += ($number < 10) ? $number : $number - 9;
				}
				return $this->_createError($col,$errorString,($sum % 10 == 0));
			}
		}
	}

	/**
	 * Checks if the value is a valid date, must pass either a format or regex to check by formats
	 * 	"dmy" e.g. 27-12-2006 or 27-12-06 (separators can be a space, period, dash, forward slash)
	 *  "mdy" e.g. 12-27-2006 or 12-27-06 (separators can be a space, period, dash, forward slash)
	 *  "ymd" e.g. 2006-12-27 or 06-12-27 (separators can be a space, period, dash, forward slash)
	 *  "dMy" e.g. 27 December 2006 or 27 Dec 2006
	 *  "Mdy" e.g. December 27, 2006 or Dec 27, 2006 (comma is optional)
	 *  "My" e.g. (December 2006 or Dec 2006)
	 *  "my" e.g. 12/2006 or 12/06 (separators can be a space, period, dash, forward slash)
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("date"=>array("format"=> "ymd")));
	 * - or -
	 * $validate = array("fieldName"=>array("date"=>array("regex"=> "/^$/")));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string, and the format
	 * @return boolean       if the data was valid
	 */
	private function _date($val,$col,$value) {
		$errorString = isset($value["error"])?$value["error"]:"is not a valid date";
		$regex = isset($value["regex"])?$value["regex"]:NULL;
		$format = isset($value["format"])?$value["format"]:NULL;

		if (!is_null($regex)) {
			return self::_check($check, $regex);
		}

		$regex['dmy'] = '%^(?:(?:31(\\/|-|\\.|\\x20)(?:0?[13578]|1[02]))\\1|(?:(?:29|30)(\\/|-|\\.|\\x20)(?:0?[1,3-9]|1[0-2])\\2))(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$|^(?:29(\\/|-|\\.|\\x20)0?2\\3(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\\d|2[0-8])(\\/|-|\\.|\\x20)(?:(?:0?[1-9])|(?:1[0-2]))\\4(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$%';
		$regex['mdy'] = '%^(?:(?:(?:0?[13578]|1[02])(\\/|-|\\.|\\x20)31)\\1|(?:(?:0?[13-9]|1[0-2])(\\/|-|\\.|\\x20)(?:29|30)\\2))(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$|^(?:0?2(\\/|-|\\.|\\x20)29\\3(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:(?:0?[1-9])|(?:1[0-2]))(\\/|-|\\.|\\x20)(?:0?[1-9]|1\\d|2[0-8])\\4(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$%';
		$regex['ymd'] = '%^(?:(?:(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(\\/|-|\\.|\\x20)(?:0?2\\1(?:29)))|(?:(?:(?:1[6-9]|[2-9]\\d)?\\d{2})(\\/|-|\\.|\\x20)(?:(?:(?:0?[13578]|1[02])\\2(?:31))|(?:(?:0?[1,3-9]|1[0-2])\\2(29|30))|(?:(?:0?[1-9])|(?:1[0-2]))\\2(?:0?[1-9]|1\\d|2[0-8]))))$%';
		$regex['dMy'] = '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ (((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))|(0?[1-9])|1\\d|2[0-8])\\ (Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\ ((1[6-9]|[2-9]\\d)\\d{2})$/';
		$regex['Mdy'] = '/^(?:(((Jan(uary)?|Ma(r(ch)?|y)|Jul(y)?|Aug(ust)?|Oct(ober)?|Dec(ember)?)\\ 31)|((Jan(uary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep)(tember)?|(Nov|Dec)(ember)?)\\ (0?[1-9]|([12]\\d)|30))|(Feb(ruary)?\\ (0?[1-9]|1\\d|2[0-8]|(29(?=,?\\ ((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))))\\,?\\ ((1[6-9]|[2-9]\\d)\\d{2}))$/';
		$regex['My'] = '%^(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)[ /]((1[6-9]|[2-9]\\d)\\d{2})$%';
		$regex['my'] = '%^(((0[123456789]|10|11|12)([- /.])(([1][9][0-9][0-9])|([2][0-9][0-9][0-9]))))$%';

		$format = (is_array($format)) ? array_values($format) : array($format);
		foreach ($format as $key) {
			if ($this->_check($val, $regex[$key],$col,$errorString) === true) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Checks if the value is a decimal, pass places value to check the number of places that the number is to
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("decimal"=>2));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string, and the number of places to check against
	 * @return boolean       if the data was valid
	 */
	private function _decimal($val,$col,$value=NULL) {
		$errorString = isset($value["error"])?$value["error"]:"must be a decimal";
		$places = isset($value[0])?$value[0]:$value;

		if (is_null($places)) {
			$regex = '/^[-+]?[0-9]*\\.{1}[0-9]+(?:[eE][-+]?[0-9]+)?$/';
		} else {
			$regex = '/^[-+]?[0-9]*\\.{1}[0-9]{' . $places . '}$/';
		}
		return $this->_check($val,$regex,$col,$errorString);
	}

	/**
	 * Checks if the value is equal to the passed parameter, can be any simple data type
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("equalTo"=>"this a string"));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string and the string to check against
	 * @return boolean       if the data was valid
	 */
	private function _equalTo($val,$col,$value) {
		$errorString = isset($value["error"])?$value["error"]:"does not match";
		return $this->_createError($col,$errorString,($val === $value));
	}

	/**
	 * Checks if value is a valid email address
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("email"));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string
	 * @return boolean       if the data was valid
	 */
	private function _email($val,$col,$value=NULL){
		$errorString = isset($value["error"])?$value["error"]:"not a valid email address";

		$hostname = '(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)';
		$regex = '/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@' . $hostname . '$/i';
		return $this->_check($val,$regex,$col,$errorString);
	}

	/**
	 * Checks to see if the value is an acceptable file extension, pass the file extensions that are valid
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("extension"=>array(".png", ".jpg", ".gif")));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string, and the extensions to check against
	 * @return boolean       if the data was valid
	 */
	private function _extension($val,$col,$value= array('gif', 'jpeg', 'png', 'jpg')){
		if(isset($value["error"])) {
			$errorString = $value["error"];
			unset($value["error"]);
		}else {
			$errorString = "is not a valid file format";
		}
		$extensions = $value;

		$pathSegments = explode('.', $val);
		$extension = strtolower(array_pop($pathSegments));
		foreach ($extensions as $value) {
			if ($extension == strtolower($value)) {
				return true;
			}
		}
		return $this->_createError($col,$errorString);
	}
	/**
	 * Checks to see if the valid is in a list, pass the list of items to check against
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("inList"=>array("value1", "value2", 3)));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string, and the list to check against
	 * @return boolean       if the data was valid
	 */
	private function _inlist($val,$col,$value=NULL) {
		if(isset($value["error"])) {
			$errorString = $value["error"];
			unset($value["error"]);
		}else {
			$errorString = "is not a valid file format";
		}
		$list = $value;
		return $this->_createError($col,$errorString,in_array($val, $list));
	}
	/**
	 * Checks if value is a valid IP address in either ipv4 or ipv6
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("ip"=> "ipv4"));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string
	 * @return boolean       if the data was valid
	 */
	private function _ip($val,$col,$value="both") {
		if(gettype($value)=="array") {
			$type = strtolower($value[0]);
			$errorString = isset($value["error"])?$value["error"]:"is not a valid ip";
		}else {
			$type = strtolower($value);
			$errorString = "is not a valid ip";
		}
		$flags = array();
		if ($type === 'ipv4' || $type === 'both') {
			$flags[] = FILTER_FLAG_IPV4;
		}
		if ($type === 'ipv6' || $type === 'both') {
			$flags[] = FILTER_FLAG_IPV6;
		}
		$bool = (boolean)filter_var($check, FILTER_VALIDATE_IP, array('flags' => $flags));
		return $this->_createError($col,$errorString,$bool);
	}

	/**
	 * Checks if the string is atleast a certain number of characters long
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("minLength"=>2));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string, and the minimum length
	 * @return boolean       if the data was valid
	 */
	private function _minlength($val,$col,$value) {
		$errorString = isset($value["error"])?$value["error"]:"is too short";
		$min = isset($value[0])?$value["error"]:$value;

		$bool = mb_strlen($val) >= $min;
		return $this->_createError($col,$errorString,$bool);
	}

	/**
	 * Check if the string is less then a cetain number of characters long
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("maxLength"=>20));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string, and the maximum length
	 * @return boolean       if the data was valid
	 */
	private function _maxlength($val,$col,$value) {
		$errorString = isset($value["error"])?$value["error"]:"is too long";
		$min = isset($value[0])?$value["error"]:$value;
		$bool = mb_strlen($val) <= $min;
		return $this->_createError($col,$errorString,$bool);
	}

	/**
	 * Check if the string is valid currency amount, pass the side the symbol is positioned, (default left)
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("money");
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string
	 * @return boolean       if the data was valid
	 */
	private function _money($val,$col,$value="left") {
		$errorString = isset($value["error"])?$value["error"]:"is not a valid amount";
		$symbolPosition = strtolower(isset($value[0])?$value[0]:$value);
		$money = '(?!0,?\d)(?:\d{1,3}(?:([, .])\d{3})?(?:\1\d{3})*|(?:\d+))((?!\1)[,.]\d{2})?';
		if ($symbolPosition == 'left') {
			$regex = '/^(?!\x{00a2})\p{Sc}?' . $money . '$/u';
		} else {
			$regex = '/^' . $money . '(?<!\x{00a2})\p{Sc}?$/u';

		}
		return $this->_check($val,$regex,$col,$errorString);

	}

	/**
	 * $validate = array("fieldName"=>array("numberic"));
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("numberic"));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string
	 * @return boolean       if the data was valid
	 */
	private function _numeric($val,$col,$value=NULL) {
		$errorString = isset($value["error"])?$value["error"]:"is not a number";
		$bool = is_numeric($val);
		return $this->_createError($col,$errorString,$bool);
	}

	/**
	 * Check if the value is a valid phone number
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("phone"));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string
	 * @return boolean       if the data was valid
	 */
	private function _phone($val,$col,$value="us") {
		$errorString = isset($value["error"])?$value["error"]:"is not a number";
		$country = gettype($value)=="array"?$value[0]:$value;
		switch ($country) {
			case "all":
				$regex = "/^(((\(\d{3}\)|\d{3})( |-|\.))|(\(\d{3}\)|\d{3}))?\d{3}( |-|\.)?\d{4}(( |-|\.)?([Ee]xt|[Xx])[.]?( |-|\.)?\d{4})?$/";
			case 'us':
				$regex = "/^(((\(\d{3}\)|\d{3})( |-|\.))|(\(\d{3}\)|\d{3}))?\d{3}( |-|\.)?\d{4}(( |-|\.)?([Ee]xt|[Xx])[.]?( |-|\.)?\d{4})?$/";
			case 'can':
				// includes all NANPA members.
				// see http://en.wikipedia.org/wiki/North_American_Numbering_Plan#List_of_NANPA_countries_and_territories
				$regex  = '/^(?:\+?1)?[-. ]?\\(?[2-9][0-8][0-9]\\)?[-. ]?[2-9][0-9]{2}[-. ]?[0-9]{4}$/';
			break;
		}
		return $this->_check($val,$regex,$col,$errorString);
	}
	/**
	 * Checks the value to see is a valid zip code
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("postal"));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string
	 * @return boolean       if the data was valid
	 */
	private function _postal($val,$col,$value="us") {
		$errorString = isset($value["error"])?$value["error"]:"is not a number";
		$country = isset($value[0])?$value[0]:$value;
		switch ($country) {
			case 'uk':
				$regex  = '/\\A\\b[A-Z]{1,2}[0-9][A-Z0-9]? [0-9][ABD-HJLNP-UW-Z]{2}\\b\\z/i';
				break;
			case 'ca':
				$regex  = '/\\A\\b[ABCEGHJKLMNPRSTVXY][0-9][A-Z] [0-9][A-Z][0-9]\\b\\z/i';
				break;
			case 'it':
			case 'de':
				$regex  = '/^[0-9]{5}$/i';
				break;
			case 'be':
				$regex  = '/^[1-9]{1}[0-9]{3}$/i';
				break;
			case 'us':
				$regex  = '/\\A\\b[0-9]{5}(?:-[0-9]{4})?\\b\\z/i';
				break;
		}
		return $this->_check($val,$regex,$col,$errorString);
	}

	/**
	 * Checks the value to see if a value is inside a range
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("range"=>array(2,4)));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string, and the values to check between
	 * @return boolean       if the data was valid
	 */
	private function _range($val,$col,$value=NULL) {
		$errorString = isset($value["error"])?$value["error"]:"is not a number in the range";
		$lower = $value[0];
		$upper = $value[1];
		if (!is_numeric($check)) {
			$this->_createError($col,$errorString);
		}
		if (isset($lower) && isset($upper)) {
			return $this->_createError($col,$errorString,($check > $lower && $check < $upper));
		}
		return $this->_createError($col,$errorString,is_finite($check));

	}

	/**
	 * Checks if the value is a valid social security number
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("ssn"));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string, and the values to check between
	 * @return boolean       if the data was valid
	 */
	private function _ssn($val,$col,$value="us") {
		$errorString = isset($value["error"])?$value["error"]:"is not a social security number";
		$contry = isset($value[0])?$value[0]:$value;
		switch ($country) {
			case 'dk':
				$regex  = '/\\A\\b[0-9]{6}-[0-9]{4}\\b\\z/i';
				break;
			case 'nl':
				$regex  = '/\\A\\b[0-9]{9}\\b\\z/i';
				break;
			case 'us':
				$regex  = '/\\A\\b[0-9]{3}-[0-9]{2}-[0-9]{4}\\b\\z/i';
				break;
		}
		return $this->_check($val,$regex,$col,$errorString);

	}


	/**
	 * Checks if the value is a valid time
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("time"));
	 *
	 * Matches	2009-04-20 14:34:32 | 2010-03-09 12:59:00 | 1020-03-09 23:59:00
	 * Non-Matches	text | 2009-13-00 00:00:00 | 2009-12-20 23:60:00
	 *
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string
	 * @return boolean       if the data was valid
	 */
	private function _time($val,$col,$value=NULL) {
		$errorString = isset($value["error"])?$value["error"]:"is not a valid time";
		$regex = '^[1-9]{1}[0-9]{3}-(0[1-9]{1}|1[0-2]{1})-([0-2]{1}[1-9]{1}|3[0-1]{1}) ([0-1]{1}[0-9]{1}|2[0-3]{1}):[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}$';
		return $this->_check($val,$regex,$col,$errorString);
	}

	/**
	 * Checks if the value is a valid timestamp
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("timestamp"));
	 *
	 * Matches	1:01 AM | 23:52:01 | 03.24.36 AM
	 * Non-Matches	19:31 AM | 9:9 PM | 25:60:61
	 *
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string
	 * @return boolean       if the data was valid
	 */
	private function _timestamp($val,$col,$value=NULL) {
		$errorString = isset($value["error"])?$value["error"]:"is not a valid timestamp";
		$regex = '^((([0]?[1-9]|1[0-2])(:|\.)[0-5][0-9]((:|\.)[0-5][0-9])?( )?(AM|am|aM|Am|PM|pm|pM|Pm))|(([0]?[0-9]|1[0-9]|2[0-3])(:|\.)[0-5][0-9]((:|\.)[0-5][0-9])?))$';
		return $this->_check($val,$regex,$col,$errorString);
	}


	/**
	 * Checks if the value is a valid uuid
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("uuid"));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string
	 * @return boolean       if the data was valid
	 */
	private function _uuid($val,$col,$value=NULL) {
		$errorString = isset($value["error"])?$value["error"]:"is not a valid uuid";
		$regex = '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i';
		return $this->_check($val,$col,$col,$errorString);
	}

	/**
	 * Checks if value is a valid url
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("url"));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string
	 * @return boolean       if the data was valid
	 */
	private function _url($val,$col,$value=NULL) {
		$errorString = isset($value["error"])?$value["error"]:"is not a valid url";
		$regex = "/^http\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?$/";
		return $this->_check($val,$col,$col,$errorString);
	}

	/**
	 * Checks to see if the value is unique in the database
	 *
	 * use this check:
	 * $validate = array("fieldName"=>array("unique"));
	 * @param  object $val   the value to check
	 * @param  string $col   the column(field) name
	 * @param  array $value  holds the error string
	 * @return boolean       if the data was valid
	 */
	private function _unique($val,$col,$value=NULL) {
		if(!empty($this->required) && $this->new) {
			$errorString = isset($value["error"])?$value["error"]:"already exists";

			$stmt = "SELECT $col from $this->tableName WHERE $col=:$col";
			$stmt = $this -> db -> prepare($stmt);
			if($stmt -> execute(array($col=>$val))) {
				$query = $stmt -> fetchAll();

				return $this->_createError($col,$errorString, empty($query));
			}
		}
		return true;
	}

}