<?php
  /**
   * Initialize the JWT extension
   * @category Extensions
   * @package  Extensions
   * @subpackage Stripe
   * @author     Rachel Higley <me@rachelhigley.com>
   * @copyright  2013 Framework Simple
   * @license    http://www.opensource.org/licenses/mit-license.php MIT
   * @link       http://rachelhigley.com/framework
   */

  Core::addNamespace('Extension_JWT_Core');
  Core::addNamespace('Extension_JWT_Controller');


  Hook::register("beforeAction",array("JWTUser","before_action"));
?>