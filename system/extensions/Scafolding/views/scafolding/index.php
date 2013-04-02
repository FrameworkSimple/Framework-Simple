<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Scafolding</title>
</head>
<body>
	<h1>Scafolding</h1>
	<form action="<?= Asset::create_url("Scafolding","post");?>" method="POST" enctype="multipart/form-data">

		<label for="name">Name:</label>
		<input type="text" name="name" id="name">

		<label for="app_name">Application Name</label>
		<input type="text" name="application_name" id="app_name">

		<label for="sql">Upload Your Database</label>
		<input type="file" name="sql" id="sql">

		<input type="submit" value="Upload">

	</form>
</body>
</html>