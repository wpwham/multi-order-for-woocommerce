<?php
/**
 * Multi order for WooCommerce - Order Payment status Background process
 *
 * @version 1.0.8
 * @since   1.0.1
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Order_Pay_Status_Bkg_Process' ) ) {

	class Alg_MOWC_Order_Pay_Status_Bkg_Process extends WP_Background_Process {

		/**
		 * @var string
		 */
		protected $action = 'alg_mowc_order_pay_status';

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

			//set_main_order_payment_status
			$order_manager = new Alg_MOWC_Order_Manager();
			$order_manager->set_main_order_payment_status( $item );

			$orders_queue = get_option( 'alg_mowc_pos_queue', array() );
			$index        = array_search( $item, $orders_queue );
			if ( $index !== false ) {
				unset( $orders_queue[ $index ] );
				update_option( 'alg_mowc_pos_queue', $orders_queue );
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
			delete_option( 'alg_mowc_pos_queue' );
		}

	}
}