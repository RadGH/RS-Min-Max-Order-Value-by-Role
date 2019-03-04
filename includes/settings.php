<?php

if ( !defined( 'ABSPATH' ) ) exit;

function rs_wcmm_get_user_min_max( $wp_user = null ) {
	if ( $wp_user === null ) $wp_user = wp_get_current_user();
	
	$managed_roles = rs_wcmm_get_managed_roles();
	
	$minimums = array();
	$maximums = array();
	
	// Loop through roles, check if the user has those roles, if they do add the min/max settings to the arrays above.
	if ( $managed_roles ) foreach( $managed_roles as $role_key => $role_name ) {
		if ( !user_can( $wp_user, $role_key ) ) continue;
		
		$min = get_option( 'rs_wcmm_role-' . $role_key . '-min-value', '' );
		if ( $min !== "" ) {
			$minimums[] = (float) $min;
		}
		
		$max = get_option( 'rs_wcmm_role-' . $role_key . '-max-value', '' );
		if ( $max !== "" ) {
			$maximums[] = (float) $max;
		}
	}
	
	// If role prices are not specified, use defaults
	if ( count($minimums) === 0 ) {
		$min = get_option( 'rs_wcmm_default-min-value', '' );
		
		if ( $min !== "" ) {
			$minimums[] = (float) $min;
		}
	}
	
	if ( count($maximums) === 0 ) {
		$max = get_option( 'rs_wcmm_default-max-value', '' );
		
		if ( $max !== "" ) {
			$maximums[] = (float) $max;
		}
	}
	
	// Return the lowest minimum and highest maximum
	$min = $minimums ? min( $minimums ) : null;
	$max = $maximums ? max( $maximums ) : null;
	
	return array( $min, $max );
}

/**
 * Returns an array of roles managed by the plugin.
 *
 * @return array
 */
function rs_wcmm_get_managed_roles() {
	$all_roles = wp_roles()->roles;
	
	$managed_roles = array();
	
	if ( $all_roles ) foreach( $all_roles as $role_key => $role ) {
		if ( !is_array($role) ) continue;
		
		// Allow exempting specific roles from the system
		if ( apply_filters( 'rs_wcmm/disable_role', false, $role_key, $role ) ) continue;
		
		// Same as above but hook name is specific to the role. Example: add_filter( 'rs_wcmm/disable_role/administrator', '__return_true' );
		if ( apply_filters( 'rs_wcmm/disable_role/' . $role_key, false, $role ) ) continue;
		
		$managed_roles[ $role_key ] = $role['name'];
	}
	
	return $managed_roles;
}

/**
 * Returns an array with the fields added to the dashboard.
 *
 * @return array
 */
function rs_wcmm_settings() {
	$new_settings = array();
	
	$new_settings[] = array(
		'name' => __( 'Min/Max Order Values', 'rs-wcmm' ),
		'type' => 'title',
		'desc' => __( '<p>If a user\'s cart total is below the minimum, or above the maximum order value specified, they will receive an error and be unable to complete their purchase.</p><p>If the user\'s role has no value entered, they will use the "Default Minimum/Maximum" instead. If a user has multiple roles, lowest minimum and highest maximum that is available will be used (not counting the default values).</p>', 'rs-wcmm' ),
		'id' => 'rs_wcmm_settings'
	);
	
	$new_settings[] = array(
		'name' 		=> __( 'Minimum Error Message', 'rs-wcmm' ),
		'desc' 		=> __( 'Error message displayed on cart screen if minimum value is not met. Use %total% to display the current cart total (with dollar sign) and %minimum% to display the minimum limit.', 'rs-wcmm' ),
		'id' 		=> 'rs_wcmm_min_value_message',
		'type'      => 'textarea',
		'placeholder' => __( 'Your cart total must be at least %minimum% to place an order.', 'rs-wcmm' ),
		'default' => __( 'Your cart total must be at least %minimum% to place an order.', 'rs-wcmm' ),
		'desc_tip'  => true
	);
	
	$new_settings[] = array(
		'name' 		=> __( 'Maximum Error Message', 'rs-wcmm' ),
		'desc' 		=> __( 'The minimum allowed quantity of items in an order. Use %total% to display the current cart total (with dollar sign) and %maximum% to display the maximum limit.', 'rs-wcmm' ),
		'id' 		=> 'rs_wcmm_max_value_message',
		'type'      => 'textarea',
		'placeholder' => __( 'Your cart total must not exceed %maximum%. Please remove items from your cart in order to continue.', 'rs-wcmm' ),
		'default' => __( 'Your cart total must not exceed %maximum%. Please remove items from your cart in order to continue.', 'rs-wcmm' ),
		'desc_tip'  => true
	);
	
	// Display default min/max if no other applicable role has a value
	$role_name = 'Default';
	
	$new_settings[] = array(
		'name' 		=> sprintf( __( '%s Minimum', 'rs-wcmm' ), $role_name ),
		'desc' 		=> null,
		'id' 		=> 'rs_wcmm_default-min-value',
		'type' 		=> 'number',
		'placeholder' => '',
	);
	
	$new_settings[] = array(
		'name' 		=> sprintf( __( '%s Maximum', 'rs-wcmm' ), $role_name ),
		'desc' 		=> null,
		'id' 		=> 'rs_wcmm_default-max-value',
		'type' 		=> 'number',
		'placeholder' => '',
	);
	
	$new_settings[] = array(
		'name' 		=> sprintf( __( '%s Maximum', 'rs-wcmm' ), $role_name ),
		'desc' 		=> null,
		'id' 		=> 'rs_wcmm_default-max-value',
		'type' 		=> 'message',
		'placeholder' => '',
	);
	
	// Display min/max values for each managed role.
	$roles = rs_wcmm_get_managed_roles();
	
	if ( $roles ) foreach( $roles as $role_key => $role_name ) {
		$new_settings[] = array(
			'name' 		=> sprintf( __( '%s Minimum', 'rs-wcmm' ), $role_name ),
			'desc' 		=> null,
			'id' 		=> 'rs_wcmm_role-' . $role_key . '-min-value',
			'type' 		=> 'number',
			'placeholder' => '',
		);
		
		$new_settings[] = array(
			'name' 		=> sprintf( __( '%s Maximum', 'rs-wcmm' ), $role_name ),
			'desc' 		=> null,
			'id' 		=> 'rs_wcmm_role-' . $role_key . '-max-value',
			'type' 		=> 'number',
			'placeholder' => '',
		);
	}
	
	$new_settings[] = array(
		'type' => 'sectionend',
		'id' => 'minmax_quantity_options'
	);
	
	$new_settings = apply_filters( 'rs_wcmm/settings', $new_settings );
	
	return $new_settings;
}

/**
 * Register our settings with WooCommerce. WooCommerce will also save these values for us.
 *
 * @param $settings
 *
 * @return array
 */
function rs_wcmm_register_settings( $settings ) {
	$new_settings = rs_wcmm_settings();
	
	if ( $new_settings ) {
		$settings = array_merge( $settings, $new_settings );
	}
	
	return $settings;
}
add_filter( 'woocommerce_products_general_settings', 'rs_wcmm_register_settings', 60 );