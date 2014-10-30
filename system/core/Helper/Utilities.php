<?php
/**
 * Handles string manipulations and other helping functions
 */

/**
 * Handles string manipulations and other helping functions
 *
 * @category   Helpers
 * @package    Core
 * @subpackage Helpers
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
class Core_Helper_Utilities {

  /**
   * encrypt sensitive data using this function
   * @api
   * @param  string $value string you want to encrypt
   * @return string        The encrypted string
   */
  public static function encrypt($value)
  {

    if(SALT == "1a2b3c4d5e6f7g8h9i10j11k12l13m14n15o16p") {

      echo "Please change the salt in your settings to a unique set of characters";

    }else {

      return md5($value.SALT);
    }
  }


  /**
   * split on caps, add underscores and then convert it to lowercase
   * @api
   * @param  string $string the string to convert
   * @return string         the converted string
   */
  public static function toDb($string){

    $string = preg_replace('/\B([A-Z])/', '_$1', $string);
      return strtolower($string);
  }


  /**
   * replace underscores with spaces and capitalize first letter
   * @api
   * @param  string $string the string to convert
   * @return string         the converted string
   */
  public static function toNorm($string)
  {
    $string = str_replace("_", " ", $string);
    return strtolower($string);
  }

 /**
 * replace underscores with spaces and capitalize first letter
 * @api
 * @param  string $string the string to convert
 * @return string         the converted string
 */
  public static function toUnder($string)
  {
    $string = str_replace(" ", "_", $string);
    return strtolower($string);
  }


  /**
   * find the underscores and convert the following letter to and uppercase
   * @api
   * @param  string $string the string to convert
   * @return string         the converted string
   */
  public static function toCam($string)
  {
    $func = create_function('$c', 'return strtoupper($c[1]);');
      $string = preg_replace_callback('/_([a-z])/', $func, $string);
    return ucfirst($string);
  }




  public static function randomString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
  {
    $str = '';
    $count = strlen($charset);
    while ($length--) {
      $str .= $charset[mt_rand(0, $count-1)];
    }
    return $str;
  }

  public static function encode($s) {
    return str_replace(array('+', '/'), array(',', '-'), base64_encode($s));
  }

  public static function decode($s) {
    return base64_decode(str_replace(array(',', '-'), array('+', '/'), $s));
  }
}