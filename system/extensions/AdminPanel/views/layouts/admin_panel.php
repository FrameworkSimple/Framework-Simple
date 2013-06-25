<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Admin Panel</title>
	<style type="text/css">
	.flash {display:block;border: 1px solid #fff; padding:10px 5px; margin: 10px 0; font-weight: bold}
	.flash.false {background-color:MistyRose;border-color:Red; color:IndianRed;}
	.flash.true {background-color:MintCream;border-color:Green;color:Green;}
	.button {padding:5px;font-weight: bold; color:LightSlateGray; background-color:AliceBlue;border:1px solid LightSlateGray;}
	table {width:100%;margin:10px;}
	</style>
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