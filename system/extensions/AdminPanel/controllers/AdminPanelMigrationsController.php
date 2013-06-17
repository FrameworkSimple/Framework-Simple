<?php
/**
	 * The Admin Panel Migrations Controller
	 */

	/**
	 * The Admin Panel Extension Migrations controller
	 * @category Extensions
	 * @package  Extensions
	 * @subpackage AdminPanel
	 * @author     Rachel Higley <me@rachelhigley.com>
	 * @copyright  2013 Framework Simple
	 * @license    http://www.opensource.org/licenses/mit-license.php MIT
	 * @link       http://rachelhigley.com/framework
	 */
Class AdminPanelMigrationsController extends Controller
{
	static public $layout = "admin_panel";

	public function index()
	{

		// the tables that were saved
		$data = json_decode( file_get_contents(ADMIN_DB), true );

		foreach($data['users'] as $user)
		{
			if($user['username'] === Session::get("AdminUserName"))
			{
				if($user['migrations'] !== count($data['migrations']))
				{
					$this->view_data("run",array("bool"=>true,"msg"=>"You have new migrations to run"));
				}
				else
				{
					$this->view_data("run",array("bool"=>false,"msg"=>"You have no new migrations to run"));
					$this->_get_diff($data);
				}
			}
		}

		$this->view_data("migrations",$data['migrations']);

	}

	public function create($data=NULL)
	{
		// the tables that were saved
		$db = json_decode( file_get_contents(ADMIN_DB), true );

		if(!$data)
		{

			// get the difference of current database and
			$migration = $this->_get_diff($db);

			// set the view data
			$this->view_data("migrations",$migration);

		}
		if($data)
		{

			// get ride of any empty space
			$migrations = trim($data['migrations']);

			// if there is a migration lets add it
			if(!empty($migrations))
			{
				$migration = preg_split("/\r\n\r\n/", $migrations);

				$migration = array(
					"user" => Session::get("AdminUserName"),
					"time" => date("Y-m-d h:i:s"),
					"alter" => $migration
				);

				$index = array_push($db['migrations'],$migration);

				foreach($db['users'] as &$user)
				{

					if($user['username'] === Session::get("AdminUserName"))
					{
						$user['migrations'] = $index;
					}
				}

				$this->_run_migrations(array($migration));

				// get the tables model
				$this->loadModel("Tables");

				// get all the tables witht their create statements
				$db['tables'] = $this->Tables->get_statements();


				file_put_contents( ADMIN_DB, json_encode($db));
			}

			Core::redirect("AdminPanelMigrations","index");

		}

	}

	public function run()
	{

		// the tables that were saved
		$db = json_decode( file_get_contents(ADMIN_DB), true );

		foreach($db['users'] as $index=>&$user)
		{
			if($user['username'] === Session::get("AdminUserName"))
			{
				$user_index = $index;
				$current_migration = $user['migrations'];
				break;
			}
		}


		$migrations = $db['migrations'];
		$migrations = array_splice($migrations,$current_migration);

		$this->_run_migrations($migrations);

		// set the uses migrations to the lenght of the migrations
		$db['users'][$user_index]['migrations'] = count($db['migrations']);

		file_put_contents( ADMIN_DB, json_encode($db));

		Core::redirect("AdminPanelMigrations","index");


	}
	private function _get_diff($data)
	{
		// get the tables model
		$this->loadModel("Tables");

		// get the current tables
		$tables = $this->Tables->get_statements();

		$compare = Core::instantiate("dbStructUpdater");

		$migration = array();

		foreach($tables as $table_name=>$table_info)
		{

			if(isset($data['tables'][$table_name]['structure']))
				$diff = $compare->getUpdates($data['tables'][$table_name]['structure'],$table_info['structure']);
			else
				$diff = array($table_info['structure']);

			if(!empty($diff))
			{
				$migration = array_merge($migration,$diff);
			}

		}

		if(empty($migration)) $this->view_data("current",array("bool"=>false,"msg"=>"You haven't made any changes to your database"));
		else $this->view_data("current",array("bool"=>true,"msg"=>"You have made changes to your database"));

		return $migration;

	}

	private function _run_migrations($migrations)
	{
		$this->loadModel("Tables");

		foreach ($migrations as $migration)
		{

			foreach($migration['alter'] as $alteration)
			{
				$this->Tables->run_migration($alteration);
			}

		}
	}

}