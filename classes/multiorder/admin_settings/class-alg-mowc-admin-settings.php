<?php
/**
 * Multi order for WooCommerce - Admin settings
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Alg_MOWC_Admin_Settings' ) ) {

	class Alg_MOWC_Admin_Settings {

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			new Alg_MOWC_Settings_General();
		}
		
		/**
		 * Add settings tab to WooCommerce settings.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function add_woocommerce_settings_tab( $settings ) {
			$settings[] = new Alg_MOWC_Settings_Page();

			return $settings;
		}

		/**
		 * Enqueue admin scripts
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function enqueue_admin_scripts( $hook ) {
			if ( $hook != 'woocommerce_page_wc-settings' || ! isset( $_GET['tab'] ) || $_GET['tab'] != 'alg_mowc' ) {
				return;
			}

			?>
            <style>
                /* Fixes select2 inputs*/
                .woocommerce table.form-table .select2-container {
                    vertical-align: middle !important;
                }
            </style>
			<?php
		}
	}
}