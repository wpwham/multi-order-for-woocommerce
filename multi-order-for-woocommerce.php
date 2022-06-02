<?php
/*
Plugin Name: Multi Order for WooCommerce
Plugin URI: https://wpwham.com/products/multi-order-for-woocommerce/
Description: Split your orders in suborders
Version: 1.4.4
Author: WP Wham
Author URI: https://wpwham.com/
Text Domain: multi-order-for-woocommerce
Domain Path: /languages
WC requires at least: 3.0
WC tested up to: 5.2
Copyright: © 2018-2021 WP Wham. All rights reserved.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

// Check if WooCommerce is active
$plugin = 'woocommerce/woocommerce.php';
if (
	! in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) &&
	! ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
) {
	return;
}

if ( 'multi-order-for-woocommerce.php' === basename( __FILE__ ) ) {
	// Check if Pro is active, if so then return
	$plugin = 'multi-order-for-woocommerce-pro/multi-order-for-woocommerce-pro.php';
	if (
		in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) ||
		( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		return;
	}
}

require_once( __DIR__ . '/classes/wordpress/class-alg-mowc-wp-plugin.php' );
require_once( __DIR__ . '/classes/multiorder/class-alg-mowc-core.php' );
require_once( __DIR__ . '/classes/multiorder/class-alg-mowc-order-manager.php' );
require_once( __DIR__ . '/classes/multiorder/admin_settings/class-alg-mowc-admin-settings.php' );
require_once( __DIR__ . '/classes/multiorder/meta_boxes/class-alg-mowc-multiorder-cmb.php' );
require_once( __DIR__ . '/classes/multiorder/class-alg-mowc-order-actions.php' );
require_once( __DIR__ . '/classes/multiorder/class-alg-mowc-order-columns.php' );
require_once( __DIR__ . '/classes/multiorder/class-alg-mowc-order-item.php' );
require_once( __DIR__ . '/classes/multiorder/class-alg-mowc-order-item-metas.php' );
require_once( __DIR__ . '/classes/multiorder/class-alg-mowc-order-metas.php' );
require_once( __DIR__ . '/classes/multiorder/taxonomies/class-alg-mowc-payment-status.php' );
require_once( __DIR__ . '/classes/multiorder/class-alg-mowc-orders-search.php' );
require_once( __DIR__ . '/classes/multiorder/class-alg-mowc-orders-view.php' );
require_once( __DIR__ . '/classes/multiorder/meta_boxes/class-alg-mowc-payment-status-cmb.php' );
require_once( __DIR__ . '/classes/multiorder/admin_settings/class-alg-mowc-settings-section.php' );
require_once( __DIR__ . '/classes/multiorder/admin_settings/class-alg-mowc-settings-general.php' );
require_once( __DIR__ . '/classes/multiorder/class-alg-mowc-wc-report.php' );
require_once( __DIR__ . '/vendor/webdevstudios/cmb2/init.php' );
require_once( __DIR__ . '/vendor/mustardBees/cmb-field-select2/cmb-field-select2.php' );

register_activation_hook( __FILE__, array( 'Alg_MOWC_Core', 'on_plugin_activation' ) );

if ( ! function_exists( 'alg_multiorder_for_wc' ) ) {
	/**
	 * Returns the main instance of Alg_MOWC_Core to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  Alg_MOWC_Core
	 */
	function alg_multiorder_for_wc() {
		
		require_once( __DIR__ . '/classes/multiorder/class-alg-mowc-order-bkg-process.php' );
		require_once( __DIR__ . '/classes/multiorder/class-alg-mowc-order-pay-status-bkg-process.php' );
		
		$multiorder         = Alg_MOWC_Core::get_instance();
		$payment_status_tax = new Alg_MOWC_Order_Payment_Status();
		$multiorder->set_args( array(
			'plugin_file_path' => __FILE__,
			'action_links'     => array(
				array(
					'url'  => admin_url( 'admin.php?page=wc-settings&tab=alg_mowc' ),
					'text' => __( 'Settings', 'woocommerce' ),
				),
				/*array(
					'url'  => admin_url( "edit-tags.php?taxonomy={$payment_status_tax->id}" ),
					'text' => __( 'Payment status', 'multi-order-for-woocommerce' ),
				),*/
			),
			'translation'      => array(
				'text_domain' => 'multi-order-for-woocommerce',
			),
		) );

		return $multiorder;
	}
}

// Starts the plugin
add_action( 'plugins_loaded', 'alg_mowc_start_plugin' );
if ( ! function_exists( 'alg_mowc_start_plugin' ) ) {
	/**
	 * Starts the plugin
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_mowc_start_plugin() {
		// Initializes the plugin
		$multiorder = alg_multiorder_for_wc();
		$multiorder->init();
	}
}
