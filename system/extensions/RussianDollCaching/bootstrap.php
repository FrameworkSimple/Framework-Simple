<?php
	Core::addClasses("RussianDollCaching",array(
		"Caching"     =>"controllers/Caching.php",
		"RDCSettings" =>"settings.php"
		)
	);

	// check that the cache is a directory and that we can write to it
	if(!is_dir("../RDCache") || !is_writable("../RDCache")) {

		// if we can't make the directory
		if(!mkdir("../RDCache") || !is_writable("../RDCache")){

			// trigger an error
			trigger_error("Make sure RDCache folder exists and is writable");

		}

	}

	Hooks::register("before_action",array("Caching","check_cache"));
	Hooks::register("before_render",array("Caching","check_cache"));
	Hooks::register("after_render",array("Caching","create"));
?>