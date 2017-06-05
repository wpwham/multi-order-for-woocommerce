<?php
/**
 * Multi order for WooCommerce - Order actions
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Order_Actions' ) ) {

	class Alg_MOWC_Order_Actions {

		function __construct() {

			// Hides / Enables cancel button on my "account > orders" on actions column
			add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'hide_cancel_button_on_my_orders_page' ) );
		}

		/**
		 * Hides / Enables cancel button on my "account > orders" on actions column
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $actions
		 *
		 * @return mixed
		 */
		public function hide_cancel_button_on_my_orders_page( $actions ) {
			$hide_cancel_btn = filter_var( get_option( Alg_MOWC_Settings_General::OPTION_DISABLE_CANCEL_BUTTON ), FILTER_VALIDATE_BOOLEAN );
			if ( ! $hide_cancel_btn ) {
				return $actions;
			}

			if ( isset( $actions['cancel'] ) ) {
				unset( $actions['cancel'] );
			}
			return $actions;
		}
	}
}