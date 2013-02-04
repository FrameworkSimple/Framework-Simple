<?php

	include "Settings.php";

	Core::add_classes("RussianDollCaching",array(
		"Caching"     =>"controllers/Caching.php",
		)
	);



	// check that the cache is a directory and that we can write to it
	if(!is_dir(CACHE_PATH) || !is_writable(CACHE_PATH)) {

		// if we can't make the directory
		if(!mkdir(CACHE_PATH) || !is_writable(CACHE_PATH)){

			// trigger an error
			trigger_error("Make sure RDCache folder exists and is writable");

		}

	}
	Hooks::register("before_action",array("Caching","check_cache"));
?>