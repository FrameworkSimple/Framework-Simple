<h2>Migrations</h2>
<p>Database statements should be seperated by two returns</p>
<form method="POST">
	<div>
		<textarea name="migrations" cols="80" rows="20"><?php if(!empty($migrations)) View::render("admin_panel_migrations/_migration",$migrations,array("path_to_views"=>"/Extension/AdminPanel/View/")); ?></textarea>
	</div>
	<div>
		<input type="submit" value="Create and Run Migrations" name="run" class="button" />
		<input type="submit" value="Create Migrations" class="button" />
	</div>
</form>

