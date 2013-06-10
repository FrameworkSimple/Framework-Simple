<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Admin Panel</title>
</head>
<body>
	<h1>Admin Panel</h1>
	<nav>
		<?php if(Session::get('AdminUser')): ?>
			<a href="<?php echo Asset::create_url('AdminPanelSettings','index') ?>">Settings Setup</a>
			<a href="<?php echo Asset::create_url('AdminPanelScaffolding','index') ?>">Scafolding</a>
			<a href="<?php echo Asset::create_url('AdminPanelMigrations','index') ?>">Migrations</a>
			<a href="<?php echo Asset::create_url('AdminPanelUser','logout')?>">Logout</a>
		<?php endif;?>
	</nav>

	<?php echo $content_for_layout ?>
</body>
</html>