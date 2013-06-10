<div>

	<label for='<?= $name?>'><?= $label ?></label>

	<?php foreach ($description as $description_part): ?>

	<p><?= $description_part?></p>

	<?php endforeach; ?>

	<?php if(strpos($type, "string") !== false): ?>

	<input type='text' name='<?=$name?>' id='<?=$name?>' value=<?=$default?> />

	<?php elseif($type === "boolean"): ?>

	<input id='<?=$name?>' name='<?=$name?>'  value="true" type='radio' <?php if($default === "true") echo "checked" ?>/><label for='<?=$name?>'>True</label>
	<input name='<?=$name?>' value="false" id='<?=$name?>_false' type='radio' <?php if($default === "false") echo "checked" ?> /><label for='<?=$name?>_false'>False</label>

	<?php else: ?>

	<textarea cols="40" rows="10" name='<?=$name?>'><?=$default?></textarea>

	<?php endif; ?>
</div>
