<?php
/**
 * Multi order for WooCommerce - Order manager
 *
 * Creates and deletes suborders
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Order_Manager' ) ) {

	class Alg_MOWC_Order_Manager {

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			add_action( 'save_post', array( $this, 'on_create_suborders_button_click' ) );
		}

		/**
		 * Detects "create suborders" button click
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function on_create_suborders_button_click( $post_id ) {
			$post = get_post( $post_id );
			if ( $post->post_type != 'shop_order' ) {
				return;
			}

			if ( ! isset( $_POST['alg_mpwc_cmb_create_suborders'] ) ) {
				return;
			}

			if ( filter_var( get_post_meta( $post_id, Alg_MOWC_Order_Metas::IS_SUB_ORDER, true ), FILTER_VALIDATE_BOOLEAN ) ) {
				return;
			}

			$this->create_suborders( $post_id );
		}

		/**
		 * Clones order postmetas
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $main_order_metadata
		 * @param $suborder_id
		 */
		public function clone_order_postmetas( $main_order_metadata, $suborder_id, $exclude = array() ) {
			foreach ( $main_order_metadata as $index => $meta_value ) {
				foreach ( $meta_value as $value ) {
					if ( ! in_array( $index, $exclude ) ) {
						add_post_meta( $suborder_id, $index, $value );
					}
				}
			}
		}

		/**
		 * Clones order item metas
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $order_item_id
		 * @param $suborder_item_id
		 */
		public function clone_order_itemmetas( $order_item_id, $suborder_item_id ) {
			$order_item_metas = wc_get_order_item_meta( $order_item_id, '' );
			foreach ( $order_item_metas as $index => $meta_value ) {
				foreach ( $meta_value as $value ) {
					wc_add_order_item_meta( $suborder_item_id, $index, $value );
				}
			}
		}

		/**
		 * Deletes previous suborders
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $main_order_id
		 */
		public function delete_previous_suborders( $main_order_id ) {
			$prev_suborders = get_post_meta( $main_order_id, Alg_MOWC_Order_Metas::SUB_ORDERS );
			if ( is_array( $prev_suborders ) && count( $prev_suborders ) > 0 ) {
				foreach ( $prev_suborders as $prev_suborder_id ) {
					wp_delete_post( $prev_suborder_id, true );
				}
				delete_post_meta( $main_order_id, Alg_MOWC_Order_Metas::SUB_ORDERS );
			}
		}

		/**
		 * Creates suborders from a main order
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $main_order_id
		 */
		public function create_suborders( $main_order_id, $args = array() ) {
			$args = wp_parse_args( $args, array(
				'delete_prev_suborders' => true,
			) );

			$main_order_post     = get_post( $main_order_id );
			$main_order          = new WC_Order( $main_order_id );
			$main_order_metadata = get_metadata( 'post', $main_order_id );
			$currentUser         = wp_get_current_user();

			// Delete previous suborders
			if ( $args['delete_prev_suborders'] ) {
				$this->delete_previous_suborders( $main_order_id );
			}

			$order_counter = 1;

			/* @var WC_Order_Item_Product $main_order_item */
			foreach ( $main_order->get_items() as $item_id => $main_order_item ) {
				$order_data = array(
					'post_type'     => 'shop_order',
					'post_title'    => $main_order_post->post_title,
					'post_status'   => get_post_status( $main_order_id ),
					'ping_status'   => 'closed',
					'post_author'   => $currentUser->ID,
					'post_password' => $main_order_post->post_password,
					'meta_input'    => array(
						Alg_MOWC_Order_Metas::IS_SUB_ORDER      => true,
						Alg_MOWC_Order_Metas::PARENT_ORDER      => $main_order_id,
						Alg_MOWC_Order_Metas::SUB_ORDER_FAKE_ID => $main_order_id . '-' . $order_counter,
					),
				);

				// Create sub order
				$suborder_id = wp_insert_post( $order_data, true );

				// Clone order post metas into suborder
				$exclude_post_metas = apply_filters( 'alg_mowc_exclude_cloned_order_postmetas', array( Alg_MOWC_Order_Metas::SUB_ORDERS ) );
				$this->clone_order_postmetas( $main_order_metadata, $suborder_id, $exclude_post_metas );

				// Updates suborder price
				update_post_meta( $suborder_id, '_order_total', $main_order_item->get_total() );

				// Add item in suborder
				$item_name        = $main_order_item['name'];
				$item_type        = $main_order_item->get_type();
				$suborder_item_id = wc_add_order_item( $suborder_id, array(
					'order_item_name' => $item_name,
					'order_item_type' => $item_type,
				) );

				// Clone order item metas
				$this->clone_order_itemmetas( $item_id, $suborder_item_id );

				// Updates main order meta regarding suborder
				add_post_meta( $main_order_id, Alg_MOWC_Order_Metas::SUB_ORDERS, $suborder_id, false );

				$order_counter ++;
			}
		}

	}
}