<html>
<head>
	<title>Admin</title>
	<?= Asset::css(array("style"));?>
</head>
<body>
	<header>
		<h1>Store Manager</h1>
		<nav>
			<a href="<?=Asset::create_url('user','logout')?>">Log Out</a>
			<a href="<?=Asset::create_url('user','settings')?>">Update Settings</a>
		</nav>
	</header>
	<div id="sidebar">
		<?php if(Authorization::user('type') === 'Admin') {
			View::render('../views/components/admin_nav');
		}else {
			View::render('../views/components/rep_nav');
		}?>
	</div>
	<?= $content_for_layout ?>
</body>
</html>