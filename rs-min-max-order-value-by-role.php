<?php
/*
Plugin Name: WooCommerce - RS Min/max Order Value by Role
Version:     1.0.0
Plugin URI:  http://zingmap.com/
Description: Adds an option to set minimum and maximum order values for individual roles. Options are displayed under WooCommerce Settings > Products > General.
Author:      Radley Sustaire, ZingMap
Author URI:  mailto:radleysustaire@zingmap.com
*/

if ( !defined( 'ABSPATH' ) ) exit;

define( 'RS_WCMM_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );
define( 'RS_WCMM_PATH', dirname(__FILE__) );
define( 'RS_WCMM_VERSION', '1.1.0' );

add_action( 'plugins_loaded', 'rs_wcmm_init_plugin' );

// Initialize plugin: Load plugin files
function rs_wcmm_init_plugin() {
	if ( !function_exists('WC') ) {
		add_action( 'admin_notices', 'rs_wcmm_warn_no_wc' );
		return;
	}
	
	
	include_once( RS_WCMM_PATH . '/includes/settings.php' );
	include_once( RS_WCMM_PATH . '/includes/cart.php' );
}

// Display WC required warning on admin if WC is not activated
function rs_wcmm_warn_no_wc() {
	?>
	<div class="error">
		<p><strong>WooCommerce - RS Min/max Order Value by Role:</strong> This plugin requires WooCommerce in order to operate. Please install and activate WooCommerce, or disable this plugin.</p>
	</div>
	<?php
}