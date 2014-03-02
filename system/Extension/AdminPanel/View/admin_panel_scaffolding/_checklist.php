<tr>
	<td><?php echo $name ?></td>
	<td>
		<div>
			<input class="controller" type="checkbox" checked name="<?php echo $name ?>[controller][index]" value="true" id="<?php echo $name ?>[controller][index]" />
			<label for="<?php echo $name ?>[controller][index]">index</label>
		</div>
		<div>
			<input class="controller" type="checkbox" checked name="<?php echo $name ?>[controller][get]" value="true" id="<?php echo $name ?>[controller][get]" />
			<label for="<?php echo $name ?>[controller][get]">get</label>
		</div>
		<div>
			<input class="controller" type="checkbox" checked name="<?php echo $name ?>[controller][post]" value="true" id="<?php echo $name ?>[controller][post]" />
			<label for="<?php echo $name ?>[controller][post]">post</label>
		</div>
		<div>
			<input class="controller" type="checkbox" checked name="<?php echo $name ?>[controller][update]" value="true" id="<?php echo $name ?>[controller][update]" />
			<label for="<?php echo $name ?>[controller][update]">update</label>
		</div>
		<div>
			<input class="controller" type="checkbox" checked name="<?php echo $name ?>[controller][delete]" value="true" id="<?php echo $name ?>[controller][delete]" />
			<label for="<?php echo $name ?>[controller][delete]">delete</label>
		</div>
	</td>
	<td>
		<div>
			<input class="model" type="checkbox" checked name="<?php echo $name ?>[model][has_many]" value="true" id="<?php echo $name ?>[model][has_many]" />
			<label for="<?php echo $name ?>[model][has_many]">has_many</label>
		</div>
		<div>
			<input class="model" type="checkbox" checked name="<?php echo $name ?>[model][belongs_to]" value="true" id="<?php echo $name ?>[model][belongs_to]" />
			<label for="<?php echo $name ?>[model][belongs_to]">belongs_to</label>
		</div>
		<div>
			<input class="model" type="checkbox" checked name="<?php echo $name ?>[model][required]" value="true" id="<?php echo $name ?>[model][required]" />
			<label for="<?php echo $name ?>[model][required]">required</label>
		</div>
		<div>
			<input class="model" type="checkbox" checked name="<?php echo $name ?>[model][rules]" value="true" id="<?php echo $name ?>[model][rules]" />
			<label for="<?php echo $name ?>[model][rules]">rules</label>
		</div>
	</td>
	<td>
		<div>
			<input class="view" type="checkbox" checked name="<?php echo $name ?>[view][index]" value="true" id="<?php echo $name ?>[view][index]" />
			<label for="<?php echo $name ?>[view][index]">index</label>
		</div>
		<div>
			<input class="view" type="checkbox" checked name="<?php echo $name ?>[view][get]" value="true" id="<?php echo $name ?>[view][get]" />
			<label for="<?php echo $name ?>[view][get]">get</label>
		</div>
		<div>
			<input class="view" type="checkbox" checked name="<?php echo $name ?>[view][post]" value="true" id="<?php echo $name ?>[view][post]" />
			<label for="<?php echo $name ?>[view][post]">post</label>
		</div>
		<div>
			<input class="view" type="checkbox" checked name="<?php echo $name ?>[view][update]" value="true" id="<?php echo $name ?>[view][update]" />
			<label for="<?php echo $name ?>[view][update]">update</label>
		</div>
		<div>
			<input class="view" type="checkbox" checked name="<?php echo $name ?>[view][form]" value="true" id="<?php echo $name ?>[view][form]" />
			<label for="<?php echo $name ?>[view][form]">_form</label>
		</div>
	</td>

	<td>
		<div>
			<a href="" class="check">Check</a>
		</div>
		<div>
			<a href="" class="uncheck">Uncheck</a>
		</div>
	</td>

</tr>
