<h2>Review Your Order</h2>

<form method="POST" action=<?=Asset::create_url('order','checkout')?>>
	<ul>
		<li><div>Name</div><div>QTY</div><div>Price</div></li>
		<li><div>Product Name</div><div><input type="text" size="4" value="1" /></div><div>$9.99</div></li>
		<li><div>Product Name</div><div><input type="text" size="4" value="2" /></div><div>$9.99</div></li>
		<li><div>Product Name</div><div><input type="text" size="4" value ="1" /></div><div>$9.99</div></li>
		<li><div>Total: </div><div>$36.96</div></li>
	</ul>

	<input type="submit" value="proceed to checkout" />

</form>