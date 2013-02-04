<?php

	include "Settings.php";

	Core::add_classes("SoftDeletion",array(
		"SoftDeletion"     =>"controllers/SoftDeletion.php",
		)
	);

	Hooks::register("before_delete",array("SoftDeletion","delete"));
	Hooks::register("before_find",array("SoftDeletion","find"));
?>