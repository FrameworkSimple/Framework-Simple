<h2>Store Settings</h2>

<form action="<?=Asset::create_url('store','post')?>" method="POST">

	<div>
		<label for="logo">Logo Upload</label>
		<input type="file" name="logo" id="logo" />
	</div>
	<div>
		<label for="subdomain">Subdomain</label>
		<input type="text" name="subdomain" id="subdomain" />
	</div>
	<h5>Shipping Address</h5>
	<div>
		<label for="address_line1">Address Line 1</label>
		<input type="text" name="address_line1" id="address_line1" />
	</div>
	<div>
		<label for="address_line2">Address Line 2</label>
		<input type="text" name="address_line2" id="address_line2" />
	</div>
	<div>
		<label for="city">City</label>
		<input type="text" name="city" id="city" />
	</div>
	<div>
		<label for="state">State</label>
		<input type="text" name="state" id="state" />
	</div>
	<div>
		<label for="zip">Zip</label>
		<input type="text" name="zip" id="zip" />
	</div>
	<h5>Select Products</h5>
	<div>
		<input type="checkbox" id="product_line" name="product_line" />
		<label for="product_line">Product Line 1</label>

		<div>
			<input type="checkbox" id="product_2" name="product_2" />
			<label for="product_2">Product 2</label>
		</div>
		<div>
			<input type="checkbox" id="product_3" name="product_3" />
			<label for="product_3">Product 3</label>
		</div>
		<div>
			<input type="checkbox" id="product_4" name="product_4" />
			<label for="product_4">Product 4</label>
		</div>
		<div>
			<input type="checkbox" id="product_5" name="product_5" />
			<label for="product_5">Product 5</label>
		</div>
	</div>
</form>