<style type="text/css">
	td {padding:10px;width:150px;overflow: hidden;border:1px solid #ccc;}
	.title{background-color:#ccc;}
</style>
<h1>Scafolding</h1>
<form action="<?= Asset::createUrl("AdminPanelScaffolding","post");?>" method="POST" enctype="multipart/form-data">
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
			<td>controller actions <a href="" class="toggle" data-type="check" data-class="controller">Check</a> <a href="" class="toggle" data-type="uncheck" data-class="controller">Uncheck</a></td>
			<td>models <a href="" class="toggle"  data-type="check" data-class="model">Check</a> <a href="" class="toggle" data-type="uncheck" data-class="model">Uncheck</a></td>
			<td>views <a href="" class="toggle" data-type="check" data-class="view">Check</a> <a href="" class="toggle" data-type="uncheck" data-class="view">Uncheck</a></td>
			<td></td>
		</tr>
		<tr>
			<td>Global</td>
			<td></td>
			<td></td>
			<td>
				<input type="checkbox" checked name="layout" value="true" id="layout" />
				<label for="layout">layout</label>
			</td>
			<td>
				<a href="#check" class="check">Check All</a><br />
				<a href="#check" class="uncheck">Uncheck All</a>
			</td>
		</tr>
		<?php
			foreach($tables as $table)
			{
				View::render("admin_panel_scaffolding/_checklist",$table,array("path_to_views"=>"/extensions/AdminPanel/views/"));
			}
		?>
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
	});

	$(".toggle").on('click',function(e)
	{
		var link = $(this);
		var link_class = link.data('class');
		var type = link.data('type');

		if(type === "check")
		{
			$("."+link_class).attr('checked','checked');
		}
		else if(type === "uncheck")
		{
			$("."+link_class).removeAttr('checked');
		}

		e.preventDefault()
	})


</script>