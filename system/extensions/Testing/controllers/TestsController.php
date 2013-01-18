<?php

Class TestsController  extends Controller {

	public function run()
	{
		\Enhance\Core::discoverTests('../tests', false);

		\Enhance\Core::runTests();
	}
}