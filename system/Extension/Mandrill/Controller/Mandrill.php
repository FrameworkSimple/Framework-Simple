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
Class Extension_Mandrill_Controller_Mandrill extends Extension_Mandrill_Core_MandrillAPI  {

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

    public function sendMessage($to, $subject, $message, $vars=array(), $css='')
    {
      $encode = Asset::encode(array($message));

      $new_body = Core::instantiate('Emogrifier');
      $new_body->setHtml($encode[0]);
      $new_body->setCss($css);
      $new_body = $new_body->emogrify();
      // var_dump($new_body);

      $message = array(
        'html'=>$new_body,
        'subject'=>$subject,
        'from_email'=>'messages@huddlefish.com',
        'from_name'=>'HuddleFish',
        "to"=>$to,
        "global_merge_vars"=>$vars,
        "headers"=>array("Content-Type"=>"text/html; charset=UTF-8")
      );
      return $this->messages->send($message);
    }


}