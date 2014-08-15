<?php
/**
 * The Mandrill Controller
 */

/**
 * The Mandrill Extension controller
 * @category Extensions
 * @package  Extensions
 * @subpackage RussianDollCaching
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
Class Extension_Mandrill_Controller_Mandrill extends Extension_Mandrill_Core_Mandrill {

    public function __construct(){
      parent::__construct(MANDRILL_API_KEY);
    }

    public function sendEmail($email, $template, $vars=array())
    {
      $message = array(
        "to"=>array(
          array(
            "email"=>$email
          )
        ),
        "global_merge_vars"=>$vars
      );
      return $this->messages->sendTemplate($template, array(), $message);
    }


}