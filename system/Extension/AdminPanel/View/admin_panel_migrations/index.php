<h2>Migrations</h2>
<table>
	<tr>
		<td>Time</td>
		<td>User</td>
		<td>Statements</td>
	</tr>
	<?php if($run['bool']):?><div class="flash false"><?php echo $run['msg']?> <a href="<?php echo Asset::createUrl('AdminPanelMigrations','run')?>">Run These Migrations</a></div>
	<?php else:?><div class="flash true"><?php echo $run['msg']; ?></div>
		<?php if($current['bool']):?>
			<div class="flash false"><?php echo $current['msg']?> <a href="<?php echo Asset::createUrl('AdminPanelMigrations','create')?>">Create Migration Based on Differences</a></div>
		<?php else: ?>
			<div class="flash true"><?php echo $current['msg'] ?></div>
		<?php endif;?>
	<?php endif;?>
	<a href="<?php echo Asset::createUrl('AdminPanelMigrations','create')?>" class="button">Create Custom Migration</a>

	<?php if(!empty($migrations)) View::render("admin_panel_migrations/_row",$migrations,array("path_to_views"=>"/Extension/AdminPanel/View/")); ?>
</table>