<h2>Submit Orders to Distrubtion</h2>

<h3>Outstanding Orders</h3>

<form method="post" action=<?=Asset::create_url('order','admin_send')?>>
	<ul>
		<li><div><input type="checkbox" />Select All</div><div>Date</div><div>Status</div><div>Name</div><div>Total</div></li>
		<li><div><input type="checkbox" /></div><div>11/07/12</div><div>Sent to Distribution</div><div>John Smith</div><div>$119.99</div><div><a href="#">View Order</a></div></li>
	</ul>
	<input type="submit" value="Send Orders" />
</form>