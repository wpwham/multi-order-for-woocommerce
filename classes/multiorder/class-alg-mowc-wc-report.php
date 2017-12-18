<?php
/**
 * Multi order for WooCommerce - WooCommerce Report
 *
 * @version 1.0.4
 * @since   1.0.4
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_WC_Report' ) ) {

	class Alg_MOWC_WC_Report {

		/**
		 * Constructor
		 *
		 * @version 1.0.4
		 * @since   1.0.4
		 */
		function __construct() {
			add_filter( 'woocommerce_admin_report_data', array( $this, 'fix_report_data' ) );
			add_filter( 'woocommerce_reports_get_order_report_data_args', array(
				$this,
				'add_multiorder_infs_to_report_data_args'
			) );
		}

		/**
		 * Adds multiorder infs to report data args
		 *
		 * @version 1.0.4
		 * @since   1.0.4
		 */
		public function add_multiorder_infs_to_report_data_args( $args ) {
			$args['data']['_cart_discount'] = array(
				'type'     => 'meta',
				'function' => '',
				'name'     => 'cart_discount',
			);

			return $args;
		}

		/**
		 * Fix report data
		 *
		 * @version 1.0.4
		 * @since   1.0.4
		 */
		public function fix_report_data( $data ) {

			foreach ( $data->orders as $key => $order ) {
				if($order->total_sales==0){
					$data->orders[ $key ]->total_sales = $order->cart_discount;
				}
			}

			$data->total_sales = wc_format_decimal( array_sum( wp_list_pluck( $data->orders, 'total_sales' ) ) - $data->total_refunds, 2 );
			$data->net_sales   = wc_format_decimal( $data->total_sales - $data->total_shipping - max( 0, $data->total_tax ) - max( 0, $data->total_shipping_tax ), 2 );

			return $data;
		}


	}
}



