<?php
/**
 * Multi order for WooCommerce - Core Class
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Core' ) ) {

	class Alg_MOWC_Core extends Alg_MOWC_WP_Plugin {

		/**
		 * Initializes the plugin.
		 *
		 * Should be called after the set_args() method
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param array $args
		 */
		public function init() {
			parent::init();

			// Init admin part
			if ( is_admin() ) {
				$this->init_admin_settings();
			}

			if ( filter_var( get_option( Alg_MOWC_Settings_General::OPTION_ENABLE_PLUGIN ), FILTER_VALIDATE_BOOLEAN ) ) {
				$this->setup_plugin();
			}
		}

		/**
		 * Initializes admin settings
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function init_admin_settings() {
			new Alg_MOWC_Admin_Settings();
		}

		/**
		 * Setups the plugin
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function setup_plugin() {
			new Alg_MOWC_Multiorder_CMB();
			new Alg_MOWC_Order_Manager();
			new Alg_MOWC_Order_Columns();
			new Alg_MOWC_Orders_View();
			new Alg_MOWC_Orders_Search();
			new Alg_MOWC_Order_Item();
			new Alg_MOWC_Order_Actions();
		}

		/**
		 * Called when plugin is enabled
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		public static function on_plugin_activation() {
			parent::on_plugin_activation();

			Alg_MOWC_Order_Manager::set_sort_order_meta();
			$payment_status = new Alg_MOWC_Order_Payment_Status();
			$payment_status->set_args();
			$payment_status->register();
			$payment_status->create_initial_status();
		}

	}
}