<?php
/*
	REFERENCES : https://www.businessbloomer.com/
*/

/* Added Exsternal Assets */
add_action( 'wp_enqueue_scripts', 'add_font_awesome_css', 1001 );
function add_font_awesome_css(){
	wp_register_style( 'Font_Awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.min.css' );
	wp_enqueue_style('Font_Awesome');
}

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
	foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {         
		$product = $values['data'];
		if ( $product->is_on_sale() ) {
		   $regular_price = $product->get_regular_price(); // call the function get_reguler_price to collect regular_price
		   $sale_price = $product->get_sale_price(); // call the function get_sale_price to collect sale_price
		   $discount = ( $regular_price - $sale_price ) * $values['quantity']; // calculate discount
		   $discount_total += $discount;
		}
	}
	
	if ( $discount_total > 0 ) {
		echo '<tr><th>You Saved</th><td data-title="You Saved">' . wc_price( $discount_total + WC()->cart->get_discount_total() ) .'</td></tr>';
	}
}

/* SHOW ERROR MESSAGES */
add_filter( 'woocommerce_form_field', 'error_checkout_fields_in_label_error', 10, 4 );
function error_checkout_fields_in_label_error( $field, $key, $args, $value ) {
   if ( strpos( $field, '</label>' ) !== false && $args['required'] ) {
      $error = '<span class="error" style="display:none">';
      $error .= sprintf( __( '%s is a required field.', 'woocommerce' ), $args['label'] );
      $error .= '</span>';
      $field = substr_replace( $field, $error, strpos( $field, '</label>' ), 0);
   }
   return $field;
}

add_filter( 'woocommerce_package_rates', 'woocommerce_package_rates' );
function woocommerce_package_rates( $rates ) {
    $user_id = get_current_user_id();
    if ( ! wc_memberships_is_user_active_member( $user_id, 'silver' ) ) { return $rates; }
    $discount_amount = 30; // 30%

    foreach($rates as $key => $rate ) {
        $rates[$key]->cost = $rates[$key]->cost - ( $rates[$key]->cost * ( $discount_amount/100 ) );
    }

    return $rates;
}


/* CALCULATE FEE ANSI >> SAFRIL ----- SCENARIO 1 (GROUP BY CATEGORIES) */
add_action('woocommerce_cart_calculate_fees', function($cart) {
	$start_date = '2022-03-18';
	$end_date = '2022-03-31';
	$categories = array(490);
	$user_role = 'b2bking_role_31388';
	$max_order = 2;
	$shipping_costs = WC()->cart->get_shipping_total();
	
	if(date("Y-m-d") <= $end_date) {
		$ct_qty = 0;
		foreach ( wc()->cart->get_cart() as $cart_item_key => $cart_item ) {
			// check existing categories
			if(count(array_intersect($cart_item['data']->get_category_ids(), $categories)) === 1){
				$ct_qty = $ct_qty + $cart_item["quantity"];
			}
		}
		$user_info = get_userdata(get_current_user_id());
		if(in_array($user_role, $user_info->roles, true)) {
			$isAlreadyOrder = gIsOrderExists($user_info->id, $start_date, $end_date);
			if(!$asAlreadyOrder && ($ct_qty <= $max_order)){

				$cart->add_fee('Shipping Discount', -10000);
			}
		}
	}
});

/* CALCULATE FEE ANSI >> SAFRIL ----- SCENARIO 2 (ONLY THIS PRODUCT CATEGORY) */
add_action('woocommerce_cart_calculate_fees', function($cart) {
	$start_date = '2022-03-18';
	$end_date = '2022-03-31';
	$categories = array(490);
	$user_role = 'b2bking_role_31388';
	$max_order = 2;
	$shipping_costs = WC()->cart->get_shipping_total();
	
	if(date("Y-m-d") <= $end_date) {
		$ct_qty = 0;
		foreach ( wc()->cart->get_cart() as $cart_item_key => $cart_item ) {
			// check existing categories
			if(count(array_intersect($cart_item['data']->get_category_ids(), $categories)) === 1){
				$ct_qty = $ct_qty + $cart_item["quantity"];
			}else{
				if (isset($cart_item['product_id'])) {
						wc()->cart->remove_cart_item($cart_item_key);
				}
			}
		}
		$user_info = get_userdata(get_current_user_id());
		if(in_array($user_role, $user_info->roles, true)) {
			$isAlreadyOrder = gIsOrderExists($user_info->id, $start_date, $end_date);
			if(!$asAlreadyOrder && ($ct_qty <= $max_order)){
				$cart->add_fee('Shipping Discount', -10000);
			}
		}
	}
});