<h2>Checkout</h2>

<form method="POST" action=<?=Asset::create_url('order','send')?>>

	<div>
		<label for="first_name_billing">First Name</label>
		<input type="text" id="first_name_billing" name="first_name_billing" />
	</div>
	<div>
		<label for="last_name_billing">Last Name</label>
		<input type="text" id="last_name_billing" name="last_name_billing" />
	</div>

	<div>
		<label for="address_billing">Address</label>
		<input type="text" id="address_billing" name="address_billing" />
	</div>

	<div>
		<label for="city_billing">City</label>
		<input type="text" id="city_billing" name="city_billing" />
	</div>

	<div>
		<label for="zip_billing">Zip</label>
		<input type="text" id="zip_billing" name="city_billing" />
	</div>

	<div>
		<label for="state_billing">State</label>
		<input type="text" id="state_billing" name="state_billing" />
	</div>

	<div>
		<input type="checkbox" id="same_as" name="same_as" value="true" />
		<label for="same_as">Billing address is the same as Shipping Address</label>
	</div>

	<div>
		<label for="first_name_shipping">First Name</label>
		<input type="text" id="first_name_shipping" name="first_name_shipping" />
	</div>
	<div>
		<label for="last_name_shipping">Last Name</label>
		<input type="text" id="last_name_shipping" name="last_name_shipping" />
	</div>

	<div>
		<label for="address_shipping">Address</label>
		<input type="text" id="address_shipping" name="address_shipping" />
	</div>

	<div>
		<label for="city_shipping">City</label>
		<input type="text" id="city_shipping" name="city_shipping" />
	</div>

	<div>
		<label for="zip_shipping">Zip</label>
		<input type="text" id="zip_shipping" name="zip_shipping" />
	</div>

	<div>
		<label for="state_shipping">State</label>
		<input type="text" id="state_shipping" name="state_shipping" />
	</div>

	<div>
		<label for="notes">Order Notes</label>
		<textarea rows="5" cols="40" name="notes" id="notes"></textarea>
	</div>

	<h3>Payment</h3>

	<div>
		<input type="radio" name="credit" id="credit" />
		<lable for="credit">Credit Card</lable>
	</div>

	<div>
		<label for="card_number">Card Number</label>
		<input type="text" size="20" id="card_number" name="card_number" />
	</div>

	<div>
		<label for="cvc">CVC</label>
		<input type="text" size="4" id="cvc" name="cvc" />
	</div>

	<div>
		<label>Expiration (MM/YYYY)</label>
		<input type="text" size="2" id="expiration_month" name="expiration_month" />
		<span> / </span>
		<input type="text" size="4" id="expiration_year" name="expiration_year"/>
	</div>

	<div>
		<input type="radio" name="check_cash" id="check_cash" />
		<label for="check_cash">Check/Cash</label>
	</div>

	<input type="submit" value="Send Order" />
</form>