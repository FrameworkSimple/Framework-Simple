<style type="text/css">
	input,label
	{
		display: block;
	}
</style>
<p>Set your username and password in the settings file of this extension. Then <a href="<?php echo Asset::createUrl("AdminPanel","setup")?>">run the setup</a> to create that authorization.</p>
<h2>Login</h2>
<form action="<?php echo Asset::createUrl("AdminPanelUser","login")?>" method="POST">
	<label for="username">Username</label>
	<input type="text" name="username" id="username">
	<label for="password">Password</label>
	<input type="password" name="password" id="password">
	<input type="submit" value="Login">
</form>