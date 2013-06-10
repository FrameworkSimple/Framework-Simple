<?php
	/**
	 * The Admin Panel Controller
	 */

	/**
	 * The Admin Panel Extension controller
	 * @category Extensions
	 * @package  Extensions
	 * @subpackage AdminPanel
	 * @author     Rachel Higley <me@rachelhigley.com>
	 * @copyright  2013 Framework Simple
	 * @license    http://www.opensource.org/licenses/mit-license.php MIT
	 * @link       http://rachelhigley.com/framework
	 */
	Class AdminPanelController extends Controller {

		static public $layout = "admin_panel";

		public function index()
		{

			if(!Session::get("AdminUser"))
			{

				Core::redirect("AdminPanelUser","login");

			}

		}

		public function setup()
		{

			// if the database doesn't exist
			if(!is_file( dirname( dirname( __FILE__ ) )."/models/db.json" ) )
			{
				// setup the user
				$data = array(
					"users" => array(
						array(
							"id"=>1,
							"username"=>ADMIN_USERNAME,
							"password"=>Core::encrypt(ADMIN_PASSWORD)
						)
					)
				);

				// get the tables model
				$this->loadModel("Tables");

				// get all the tables witht their create statements
				$data['tables'] = $this->Tables->get_statements();

			}
			// if the database does exist
			else
			{

				// get the database
				$data = json_decode( file_get_contents(ADMIN_DB) );

				// if there are users
				if($data->users)
				{

					$user_exists = false;

					$id = 1;

					// loop through the users
					foreach ($data->users as &$user)
					{

						// if the username exists
						if($user->username === ADMIN_USERNAME)
						{

							$id = $user->id;

							// set the username
							$user->password = Core::encrypt(ADMIN_PASSWORD);

							// the user exists
							$user_exists = true;

						}

					}

					if(!$user_exists)
					{

						array_push($data->users, array(
							"id"=>$id + 1,
							"username"=>ADMIN_USERNAME,
							"password"=>Core::encrypt(ADMIN_PASSWORD)
						));

					}

				}

			}

			file_put_contents( ADMIN_DB, json_encode($data));

		}

	}