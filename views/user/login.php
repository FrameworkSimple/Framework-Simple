<html>
<head>
	<title>Login</title>
</head>
<body>
	<h1>Login</h1>
	<form method="POST" action="<?=Asset::create_url('admin','index')?>">
		<div>
			<label for="email">Email</label>
			<input type="text" name="email" id="email" />
		</div>
		<div>
			<label for="password">Password</label>
			<input type="text" name="password" id="password" />
		</div>
		<div>
			<input type="submit" value="Login" />
		</div>
	</form>
</body>
</html>