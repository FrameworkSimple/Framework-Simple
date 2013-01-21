<?php

	include "Settings.php";

	Core::addClasses("SoftDeletion",array(
		"SoftDeletion"     =>"controllers/SoftDeletion.php",
		)
	);

	Hooks::register("before_delete",array("SoftDeletion","delete"));
	Hooks::register("before_find",array("SoftDeletion","find"));
?>