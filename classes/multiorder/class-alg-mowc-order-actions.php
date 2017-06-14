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

			// Change main order pay button label
			add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'change_main_order_pay_button_label' ), 10, 2 );

			// Hides / Enables pay button on my "account > orders" on actions column
			//add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'hide_pay_button_on_my_orders_page' ),10,2 );
		}

		/**
		 * Change main order pay button label
		 *
		 * @version  1.0.0
		 * @since    1.0.0
		 *
		 * @param $actions
		 *
		 * @return mixed
		 */
		public function change_main_order_pay_button_label( $actions, WC_Order $order ) {
			$pay_button_label = sanitize_text_field( get_option( Alg_MOWC_Settings_General::OPTION_PAY_BUTTON_LABEL ) );
			if ( ! empty( $pay_button_label ) ) {
				if ( $suborders = get_post_meta( $order->get_id(), Alg_MOWC_Order_Metas::SUB_ORDERS ) ) {
					if ( is_array( $suborders ) && count( $suborders ) > 1 ) {
						if ( isset( $actions['pay'] ) ) {
							$actions['pay']['name'] = esc_html( $pay_button_label );
						}
					}
				}
			}
			return $actions;
		}

		/**
		 * Hides / Enables pay button on my "account > orders" on actions column
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @param $actions
		 *
		 * @return mixed
		 */
		/*public function hide_pay_button_on_my_orders_page( $actions, WC_Order $order ) {
			if ( $order->get_total() <= 0 ) {
				if ( isset( $actions['pay'] ) ) {
					unset( $actions['pay'] );
				}
			}
			return $actions;
		}*/

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