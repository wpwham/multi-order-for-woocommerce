<?php
/**
 * Multi order for WooCommerce - Core Class
 *
 * @version 1.0.4
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Core' ) ) {

	class Alg_MOWC_Core extends Alg_MOWC_WP_Plugin {
		
		/**
		 * Order Metabox instance.
		 *
		 * @var Alg_MOWC_Multiorder_CMB
		 */
		public $order_metabox = null;
		
		/**
		 * Order Manager instance.
		 *
		 * @var Alg_MOWC_Order_Manager
		 */
		public $order_manager = null;
		
		/**
		 * Order Columns instance.
		 *
		 * @var Alg_MOWC_Order_Columns
		 */
		public $order_columns = null;
		
		/**
		 * Orders View instance.
		 *
		 * @var Alg_MOWC_Orders_View
		 */
		public $orders_view = null;
		
		/**
		 * Orders Search instance.
		 *
		 * @var Alg_MOWC_Orders_Search
		 */
		public $orders_search = null;
		
		/**
		 * Order Item instance.
		 *
		 * @var Alg_MOWC_Order_Item
		 */
		public $order_item = null;
		
		/**
		 * Order Actions instance.
		 *
		 * @var Alg_MOWC_Order_Actions
		 */
		public $order_actions = null;
		
		/**
		 * WC Report instance.
		 *
		 * @var Alg_MOWC_WC_Report
		 */
		public $wc_report = null;

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
		 * @version 1.0.4
		 * @since   1.0.0
		 */
		public function setup_plugin() {
			$this->order_metabox = new Alg_MOWC_Multiorder_CMB();
			$this->order_manager = new Alg_MOWC_Order_Manager();
			$this->order_columns = new Alg_MOWC_Order_Columns();
			$this->orders_view = new Alg_MOWC_Orders_View();
			$this->orders_search = new Alg_MOWC_Orders_Search();
			$this->order_item = new Alg_MOWC_Order_Item();
			$this->order_actions = new Alg_MOWC_Order_Actions();
			$this->wc_report = new Alg_MOWC_WC_Report();
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
