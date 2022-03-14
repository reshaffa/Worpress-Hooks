<?php
/*
	REFERENCES : https://www.businessbloomer.com/
*/

/* CHECKOUT PAGE FEATURE WOOCOMMERCE */
/* ADD THUMBNAIL IMAGE SIZE (50 x 50) TO CHECKOUT PAGE */
add_filter('woocommerce_cart_item_name','product_image_review_order_checkout', 9999, 3); // 3 parameters on function
function product_image_review_order_checkout($name, $cart_item, $cart_item_key){
	/*
		$name : product name (string)
		$cart_item : all data and attribute on cart per item (array)
		$cart_item_key : key hash of item
	*/
	if(! is_checkout()) return $name;
	$product = $cart_item['data'];
	// added image align position in left
	$thumbnail = $product->get_image(
								array('50','50'), 
								array('class' => 'alignleft')
	);
	return $thumbnail.$name;
}


/* MOVING LABEL INSIDE FIELDS */
add_filter('woocommerce_checkout_fields','move_label_inside_fields', 9999);
function move_label_inside_fields($fields){
	foreach ( $fields as $section => $section_fields ) {
		foreach ( $section_fields as $section_field => $section_field_settings ) {
		   $fields[$section][$section_field]['placeholder'] = $fields[$section][$section_field]['label'];
		   $fields[$section][$section_field]['label'] = '';
		}
	 }
	return $fields;
}

/* ADDED TOTAL DISCOUNT IN CART PAGE */
add_action('woocommerce_cart_totals_after_order_total','show_total_discounts_cart_checkout', 9999);
add_action('woocommerce_review_order_after_order_total','show_total_discounts_cart_checkout', 9999);

function show_total_discounts_cart_checkout(){
	$discount_total = 0;
	// WC() ->cart->get_cart() is an fetch all data in cart.
	foreach(WC()->cart->get_cart() as $key => $values){
		$product = $values['data'];
		$regular_price = $product->get_regular_price(); // get regular price
		$sale_price = $product->get_sale_price(); // get sale price
		$discount = ($reguler_price-$sale_price) * $values['quantity'];
		$discount_total += $discount;
	}
	
	if ( $discount_total > 0 ) {
		echo '<tr><th>You Saved</th><td data-title="You Saved">' . wc_price( $discount_total + WC()->cart->get_discount_total() ) .'</td></tr>';
	}
}

?>