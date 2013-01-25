<?php

Class TestsController  extends Controller {

	public function run()
	{
		\Enhance\Core::discoverTests(SYSTEM_PATH."/extensions/Testing/tests", false);

		\Enhance\Core::runTests();
	}
}