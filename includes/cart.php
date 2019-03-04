<?php

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Calculates if minimum or maximum limits have been exceeded for the current user. Returns an array of errors that occurred if not eligible for checkout.
 *
 * @param null $wp_user
 *
 * @return array|false
 */
function rs_wcmm_validate_cart_value( $wp_user = null ) {
	if ( $wp_user === null ) $wp_user = wp_get_current_user();
	
	list( $min, $max ) = rs_wcmm_get_user_min_max( $wp_user );
	if ( $min === null && $max === null ) return;
	
	$total = (float) WC()->cart->get_total( 'edit' );
	
	$errors = array();
	
	$search_replace = array(
		'%total%' => wc_price( $total ),
		'%minimum%' => $min === null ? "" : wc_price( $min ),
		'%maximum%' => $max === null ? "" : wc_price( $max )
	);
	
	$s = array_keys($search_replace);
	$r = array_values($search_replace);
	
	if ( $total < $min ) {
		$message = get_option( 'rs_wcmm_min_value_message', false );
		if ( !$message ) $message = __('The cart total must be at least %minimum% to place an order', 'rs-wcmm');
		
		$message = str_replace( $s, $r, $message );
		
		$errors[] = $message;
	}
	
	if ( $total > $max ) {
		$message = get_option( 'rs_wcmm_max_value_message', false );
		if ( !$message ) $message = __( 'The cart total cannot exceed %maximum%, please remove items from your cart in order to continue.', 'rs-wcmm' );
		
		$message = str_replace( $s, $r, $message );
		
		$errors[] = $message;
	}
	
	return $errors ? $errors: false;
}

/**
 * Displays notices in WooCommerce if the minimum or maximum values are exceeded.
 *
 * Does not prevent checkout here -- see rs_wcmm_maybe_prevent_checkout.
 */
function rs_wcmm_display_woocommerce_notices() {
	$errors = rs_wcmm_validate_cart_value();
	
	if ( $errors ) {
		wc_clear_notices();
		
		foreach( $errors as $message ) {
			wc_add_notice( $message, 'error' );
		}
	}
}
add_action( 'woocommerce_check_cart_items', 'rs_wcmm_display_woocommerce_notices', 20 );

/**
 * Prevent access to checkout if the our validation returns an error.
 */
function rs_wcmm_maybe_prevent_checkout() {
	$errors = rs_wcmm_validate_cart_value();
	
	if ( !empty($errors) ) {
		wp_redirect( wc_get_cart_url() );
		exit;
	}
}
add_action( 'woocommerce_checkout_init', 'rs_wcmm_maybe_prevent_checkout' );