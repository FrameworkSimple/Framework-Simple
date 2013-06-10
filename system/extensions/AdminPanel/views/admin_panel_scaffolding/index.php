<style type="text/css">
	td {padding:10px;width:150px;overflow: hidden;border:1px solid #ccc;}
	.title{background-color:#ccc;}
</style>
<h1>Scafolding</h1>
<form action="<?= Asset::create_url("AdminPanelScaffolding","post");?>" method="POST" enctype="multipart/form-data">
	<div>
		<label for="name">Name:</label>
		<input type="text" name="name" id="name">
	</div>

	<div>
		<label for="app_name">Application Name</label>
		<input type="text" name="application_name" id="app_name">
	</div>
	<table cellspacing="0">

		<tr class="title">
			<td>table</td>
			<td>controller actions</td>
			<td>models</td>
			<td>views</td>
			<td></td>
		</tr>
		<tr>
			<td colspan="5" style="text-align:right;">
				<a href="#check" class="check">Check All</a>&nbsp;&nbsp;
				<a href="#check" class="uncheck">Uncheck All</a>
			</td>
		</tr>
		<?php View::render("admin_panel_scaffolding/_checklist",$tables,array("path_to_views"=>"/extensions/AdminPanel/views/")) ?>
	</table>
	<div>
		<input type="submit" value="Run">
	</div>

</form>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
<script type="text/javascript">

	$(".check").on('click',function(e)
	{
		e.preventDefault();

		var link = $(this);

		link.parent().parent().parent().find('input:checkbox').attr('checked','checked');

	});

	$(".uncheck").on('click',function(e)
	{
		e.preventDefault();

		var link = $(this);

		link.parent().parent().parent().find('input:checkbox').removeAttr('checked');
	})


</script>