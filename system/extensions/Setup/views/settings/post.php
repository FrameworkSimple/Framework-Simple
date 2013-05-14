<style type="text/css">
label {font-weight: bold;}
div {margin: 20px 0;}
p {margin: 5px 10px;}
input,textarea {margin-left: 10px;}

</style>
<form method="POST" action="<?= Asset::create_url('settings','post')?>">
	<?php View::render("settings/_form",$settings,array("path_to_views"=>"/extensions/Setup/views/")) ?>
<div>
	<input type="submit" value="save" />
</div>

</form>