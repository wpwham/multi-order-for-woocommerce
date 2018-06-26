<?php
/**
 * Multi order for WooCommerce - Order Background process
 *
 * @version 1.0.8
 * @since   1.0.1
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Order_Bkg_Process' ) ) {

	class Alg_MOWC_Order_Bkg_Process extends WP_Background_Process {

		/**
		 * @var string
		 */
		protected $action = 'alg_mowc_recalculate_order';

		/**
		 * Handle
		 *
		 * Pass each queue item to the task handler, while remaining
		 * within server memory and time limit constraints.
		 *
		 * @version 1.0.1
		 * @since   1.0.1
		 */
		protected function task( $item ) {
			$order = wc_get_order( $item );
			$order->calculate_totals();
			$orders_queue = get_option( 'alg_mowc_orders_queue', array() );
			$index        = array_search( $item, $orders_queue );
			if ( $index !== false ) {
				unset( $orders_queue[ $index ] );
				update_option( 'alg_mowc_orders_queue', $orders_queue );
			}
			return false;
		}

		/**
		 * Complete
		 *
		 * Override if applicable, but ensure that the below actions are
		 * performed, or, call parent::complete().
		 *
		 * @version 1.0.1
		 * @since   1.0.1
		 */
		protected function complete() {
			parent::complete();
			delete_option( 'alg_mowc_orders_queue' );
		}

	}
}