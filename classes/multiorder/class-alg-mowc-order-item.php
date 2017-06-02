<?php
/**
 * Multi order for WooCommerce - Order Item meta
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Order_Item' ) ) {

	class Alg_MOWC_Order_Item {

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			add_filter( 'woocommerce_order_item_get_formatted_meta_data', array(
				$this,
				'format_order_item_meta_data',
			), 10, 2 );
		}

		/**
		 * Displays the suborder item meta on order page
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param               $formatted_meta
		 * @param WC_Order_Item $order_item
		 *
		 * @return mixed
		 */
		public function format_order_item_meta_data( $formatted_meta, WC_Order_Item $order_item ) {
			if ( empty( $formatted_meta ) ) {
				return $formatted_meta;
			}
			reset( $formatted_meta );
			$key = key( $formatted_meta );
			if ( $formatted_meta[ $key ]->key == Alg_MOWC_Order_Item_Metas::SUB_ORDER ) {
				$order                                 = wc_get_order( (int) $formatted_meta[ $key ]->value );
				if($order){
					$formatted_meta[ $key ]->display_key   = __( 'Suborder', 'multi-order-for-woocommerce' );
					$order_number                          = apply_filters( 'woocommerce_order_number', $order->get_id(), $order );
					$formatted_meta[ $key ]->display_value = '<a href="' . admin_url( 'post.php?post=' . absint( $order->get_id() ) . '&action=edit' ) . '">#' . $order_number . '</a>';
				}

			}
			return $formatted_meta;
		}


	}
}