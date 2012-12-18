<?php
class Validation {
	private $tableName;
	private $name;
	private $data;
	public $db;
	public $errors;
	public $validate;
	public $required;

	public function validate($tableName,$data,$required,$rules) {
		$this->tableName = Core::toDB($tableName);
		$this->name = $tableName;
		$this->data = $data;
		$this->errors = array();
		$this->required = $required;
		$this->validate = $rules;
		if(!empty($this->required)) {	
			foreach($this->required as $col) {
				if(!isset($this->data[$col]) || (empty($this->data[$col]) || !self::_check($this->data[$col],'/[^\s]+/m',$col))) {
					unset($this->data[$col]);
					array_push($this->errors,array(
						"name"=>$col,
						"string"=>Core::toNorm($col)." is required"
						)
					);
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
							$method = "_".$key;
							if(!$this->$method($value,$col,$val)){
								break;
							};
						}
					}
				}
				
			}
		}
		if(!empty($this->errors)) {
			return $this->errors;
		}
		return true;
	}
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
	private function _createError($col,$errorString,$bool=FALSE) {
		if(!$bool) {
			array_push($this->errors, array(
					"name"=>$col,
					"string"=>Core::toNorm($col)." ".$errorString
				));
			unset($this->data[$col]);
			return false;
		}
		return true;
	}
	
	// "field" => array("regex"=>array("//","error"=>"this is an error"));
	private function _regex($val,$col,$value) {
		$errorString = isset($value["error"])?$value["error"]:"did not meet requirement";
		$regex = isset($value[0])?$value[0]:$value;
		return $this->_check($val,$regex,$col,$errorString);
	}

	// "field" => array('alphaNumeric');
	private function _alphaNumeric($val,$col,$value=NULL) {
		$errorString = isset($value["error"])?$value["error"]:"can only be letters and numbers";
		$regex = '/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]+$/mu';
		return $this->_check($val,$regex,$col,$errorString);
	}

	// "field" => array('between'=>array(1,2));
	private function _between($val,$col,$value) {
		$errorString = isset($value["error"])?$value["error"]:"is test short";
		$min = $value[0];
		$max = $value[1];
		$length = mb_strlen($val);
		$bool = ($length >= $min && $length <= $max);
		return $this->_createError($col,$errorString,$bool);
	}

	//"field"=>array("boolean")
	private function _boolean($val,$col,$value = NULL) {
		$errorString = isset($value["error"])?$value["error"]:"is not a boolean";
		$booleanList = array(0, 1, '0', '1', true, false);
		return $this->_createError($col,$errorString,in_array($val, $booleanList, true));
	}

	// "field" => array("cc");
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

	//"field"=> array("format"=>"ymd","regex"=>"//");
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
	
	// "field"=>array("decimal"=>{places});
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

	// "field"=>array("equalTo"=>{compare});
	private function _equalTo($val,$col,$value) {
		$errorString = isset($value["error"])?$value["error"]:"does not match";
		$compareTo = isset($value[0])?$value[0]:$value;
		return $this->_createError($col,$errorString,($check === $compareTo));
	}

	// "field"=>array("email")
	private function _email($val,$col,$value=NULL){
		$errorString = isset($value["error"])?$value["error"]:"not a valid email address";

		$hostname = '(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)';
		$regex = '/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@' . $hostname . '$/i';
		return $this->_check($val,$regex,$col,$errorString);
	}

	// "field"=>array("extension"=>array(".fdx",".celtx"));
	private function _extension($val,$col,$value= array('gif', 'jpeg', 'png', 'jpg')) {
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
	// "field"=>array("inlist"=>array({value},{value}))
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
	// "field"=>array("ip"=>"ipv4")
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

	// "field"=>array("minlength"=>1);
	private function _minlength($val,$col,$value) {
		$errorString = isset($value["error"])?$value["error"]:"is too short";
		$min = isset($value[0])?$value["error"]:$value;

		$bool = mb_strlen($val) >= $min;
		return $this->_createError($col,$errorString,$bool);
	}

	// "field"=>array("maxlength"=>1,);
	private function _maxlength($val,$col,$value) {
		$errorString = isset($value["error"])?$value["error"]:"is too long";
		$min = isset($value[0])?$value["error"]:$value;

		$bool = mb_strlen($val) <= $min;
		return $this->_createError($col,$errorString,$bool);
	}

	//"field"=>array("money"=>"right")
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

	// "field"=>array("numeric");
	private function _numeric($val,$col,$value=NULL) {
		$errorString = isset($value["error"])?$value["error"]:"is not a number";
		$bool = is_numeric($val);
		return $this->_createError($col,$errorString,$bool);
	}

	// "field"=>array("phone");
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
	// "field"=>array("postal");
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

	// "field"=>array("range"=>array({lower},{upper}));
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

	//"field"=>array("ssn");
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


	//"field"=>array("time");
	private function _time($val,$col,$value=NULL) {
		$errorString = isset($value["error"])?$value["error"]:"is not a valid time";
		$regex = '%^((0?[1-9]|1[012])(:[0-5]\d){0,2} ?([AP]M|[ap]m))$|^([01]\d|2[0-3])(:[0-5]\d){0,2}$%';
		return $this->_check($val,$regex,$col,$errorString);
	}

	// "field"=>array(uuid)
	private function _uuid($val,$col,$value=NULL) {
		$errorString = isset($value["error"])?$value["error"]:"is not a valid uuid";
		$regex = '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i';
		return $this->_check($val,$col,$col,$errorString);
	}

	// "field"=>array("url");
	private function _url($val,$col,$value=NULL) {
		$errorString = isset($value["error"])?$value["error"]:"is not a valid url";
		$regex = "/^http\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?$/";
		return $this->_check($val,$col,$col,$errorString);
	}
	
	// "field"=>array("unique");
	private function _unique($val,$col,$value=NULL) {
		if(!empty($this->required)) {
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