<?php
/**
 * The Admin Panel User Controller
 */

/**
 * The Admin Panel Extension User controller
 * @category Extensions
 * @package  Extensions
 * @subpackage AdminPanel
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
Class Extension_AdminPanel_Controller_AdminPanelUser extends Controller {

	static public $layout = "admin_panel";

	static public $allowed_actions = array('login','logout');

	public function login($admin_user=NULL)
	{

		if($admin_user)
		{
			$data = json_decode( file_get_contents(ADMIN_DB) );

			// if there are users
			if($data->users)
			{


				// loop through the users
				foreach ($data->users as &$user)
				{

					// if the username exists
					if($user->username === $admin_user['username'] && $user->password === Core::encrypt($admin_user['password']) )
					{

						Session::set("AdminUser",true);

						Session::set("AdminUserName",$user->username);

						Core::redirect("AdminPanel","index");

					}

				}


			}

		}

	}

	public function logout()
	{
		Session::set("AdminUser",false);

		Core::redirect("AdminPanel","index");
	}
}
?>