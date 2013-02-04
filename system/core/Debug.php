<style type='text/css'>
	#debuger {
		background: #ddd;
		border: 1px solid #000;
		width: 95%;
		margin: 10px auto;
	}
	#debuger h2 {
		font-size: 24px;
		background: #739274;
		border: 1px solid #000;
		padding: 5px;
	}
	#debuger p {
		font-size: 16px;
		border: 1px solid #000;
		overflow: hidden;
	}
	#debuger p span {
		background:#fff;
		border-right: 1px solid #000;
		display: block;
		float: left;
		padding: 0 5px;
		height: 30px;
		margin-right: 5px;
		text-align: center;
	}
</style>
<div id='debuger'>
<?php foreach(self::$debug as $title=>$info):?>

	<h2><?= $title?></h2>

	<?php foreach ($info as $num => $para): ?>

		<?php if(is_array($para)) $para = implode(",", $para);?>

		<p><span><?= $num?></span><?= $para?></p>

	<?php endforeach;?>

<?php endforeach;?>

	<h2>Time</h2>
	<p><?= round((microtime(true) - START_TIME), 4)?> Seconds</p>
</div>