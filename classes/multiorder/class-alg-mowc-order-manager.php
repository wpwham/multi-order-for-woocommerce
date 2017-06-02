<?php
/**
 * Multi order for WooCommerce - Order manager
 *
 * Creates, deletes suborders and sync them with their parent orders
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

			// Detects "create suborders" button click
			add_action( 'save_post', array( $this, 'create_suborders_call' ) );

			// Changes suborder status when parent order changes status
			add_action( 'woocommerce_order_status_changed', array(
				$this,
				'sync_suborders_status_from_parent_call',
			), 10, 3 );

			// Call the function that deducts suborder from main order
			add_action( 'woocommerce_order_status_changed', array( $this, 'deduct_suborder_from_order_call' ), 10, 3 );

			// Recalculates main order price
			add_action( 'recalculate_main_order_price_event', array( $this, 'recalculate_main_order' ), 10, 1 );

			// Deletes suborder if correspondent item id is removed from main order
			add_action( 'woocommerce_before_delete_order_item', array(
				$this,
				'remove_suborder_on_main_order_item_removal',
			) );
		}

		/**
		 * Deletes suborder if correspondent item id is removed from main order
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $item_id
		 */
		public function remove_suborder_on_main_order_item_removal( $item_id ) {
			$suborder_id = wc_get_order_item_meta( $item_id, Alg_MOWC_Order_Item_Metas::SUB_ORDER, true );
			if ( $suborder_id ) {
				wp_delete_post( $suborder_id, true );
			}
		}

		/**
		 * Recalculate order total price
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $order_id
		 */
		public function recalculate_main_order( $order_id ) {
			$order = wc_get_order( $order_id );
			//$order->calculate_taxes();
			$order->calculate_totals();
		}

		/**
		 * Deducts suborder from main order
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param          $order_id
		 * @param          $transition_from
		 * @param          $transition_to
		 * @param WC_Order $order
		 */
		public function deduct_suborder_from_order( $order_id, $transition_from, $transition_to ) {
			// Get parent order item id
			$parent_order_item = get_post_meta( $order_id, Alg_MOWC_Order_Metas::PARENT_ORDER_ITEM, true );

			// Remove price from main order
			wc_update_order_item_meta( $parent_order_item, '_line_total', 0 );
			wc_update_order_item_meta( $parent_order_item, '_line_tax', 0 );

			// Update _line_tax_data
			$line_tax_data = wc_get_order_item_meta( $parent_order_item, '_line_tax_data', true );
			foreach ( $line_tax_data['total'] as $data_key => $data_value ) {
				$line_tax_data['total'][ $data_key ] = 0;
			}
			wc_update_order_item_meta( $parent_order_item, '_line_tax_data', $line_tax_data );

			// Create event to recalculate main order
			$main_order_id = get_post_meta( $order_id, Alg_MOWC_Order_Metas::PARENT_ORDER, true );
			wp_schedule_single_event( time() + 1, 'recalculate_main_order_price_event', array( $main_order_id ) );
		}

		/**
		 * Call the function that deducts suborder from main order
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param          $order_id
		 * @param          $transition_from
		 * @param          $transition_to
		 * @param WC_Order $order
		 */
		public function deduct_suborder_from_order_call( $order_id, $transition_from, $transition_to ) {
			if ( ! filter_var( get_post_meta( $order_id, Alg_MOWC_Order_Metas::IS_SUB_ORDER, true ), FILTER_VALIDATE_BOOLEAN ) ) {
				return;
			}

			// Check deduct status
			$deduct_status = get_option( Alg_MOWC_Settings_General::OPTION_SUBORDERS_SUBTRACTION_STATUS, true );
			if ( ! in_array( 'wc-' . $transition_to, $deduct_status ) ) {
				return;
			}

			$this->deduct_suborder_from_order( $order_id, $transition_from, $transition_to );
		}

		/**
		 * Changes suborder when parent order changes status
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function sync_suborders_status_from_parent_call( $order_id, $transition_from, $transition_to ) {
			if ( filter_var( get_post_meta( $order_id, Alg_MOWC_Order_Metas::IS_SUB_ORDER, true ), FILTER_VALIDATE_BOOLEAN ) ) {
				return;
			}

			$suborders = get_post_meta( $order_id, Alg_MOWC_Order_Metas::SUB_ORDERS );

			if ( ! is_array( $suborders ) || count( $suborders ) < 1 ) {
				return;
			}

			if ( ! filter_var( get_option( Alg_MOWC_Settings_General::OPTION_SUBORDERS_CHANGE_ON_ORDER_STATUS_CHANGE ), FILTER_VALIDATE_BOOLEAN ) ) {
				return;
			}

			$this->sync_suborders_status_from_parent( $order_id, $suborders, $transition_from, $transition_to );
		}

		/**
		 * Saves suborders status from parent order
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $parent_order_id
		 */
		public function sync_suborders_status_from_parent( $parent_order_id, $suborders, $transition_from, $transition_to ) {
			foreach ( $suborders as $suborder_id ) {
				wp_update_post( array(
					'ID'          => $suborder_id,
					'post_status' => 'wc-' . $transition_to,
				) );

				do_action( 'woocommerce_order_status_changed', $suborder_id, $transition_from, $transition_to );

			}
		}

		/**
		 * Detects "create suborders" button click
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function create_suborders_call( $post_id ) {
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
		 * Adds line item in suborder
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $main_order_item
		 * @param $item_id
		 * @param $suborder_id
		 */
		public function add_line_item_in_suborder( $main_order_item, $item_id, $suborder_id ) {
			$item_name        = $main_order_item['name'];
			$item_type        = $main_order_item->get_type();
			$suborder_item_id = wc_add_order_item( $suborder_id, array(
				'order_item_name' => $item_name,
				'order_item_type' => $item_type,
			) );

			// Clone order item metas
			$this->clone_order_itemmetas( $item_id, $suborder_item_id );
		}

		/**
		 * Adds fees in suborder
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $fees
		 * @param $suborder_id
		 * @param $main_order
		 *
		 * @return float|int
		 */
		public function add_fees_in_suborder( $fees, $suborder_id, $main_order ) {
			$fee_value_count = 0;
			/* @var WC_Order_Item_Fee $fee */
			foreach ( $fees as $fee ) {
				$item_name           = $fee->get_name();
				$item_type           = $fee->get_type();
				$suborder_new_fee_id = wc_add_order_item( $suborder_id, array(
					'order_item_name' => $item_name,
					'order_item_type' => $item_type,
				) );
				$this->clone_order_itemmetas( $fee->get_id(), $suborder_new_fee_id );
				$fee_value       = $fee->get_total() / $main_order->get_item_count();
				$fee_value_count += $fee_value;
				wc_update_order_item_meta( $suborder_new_fee_id, '_line_total', $fee_value );
				wc_update_order_item_meta( $suborder_new_fee_id, '_line_tax', 0 );
				wc_update_order_item_meta( $suborder_new_fee_id, '_line_tax_data', 0 );
			}
			return $fee_value_count;
		}

		/**
		 * Adds taxes in suborder
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $taxes
		 * @param $suborder_id
		 */
		public function add_taxes_in_suborder( $taxes, $suborder_id, $main_order_item ) {
			/* @var WC_Order_Item_Tax $tax */
			foreach ( $taxes as $tax ) {
				$item_name           = $tax->get_name();
				$item_type           = $tax->get_type();
				$suborder_new_tax_id = wc_add_order_item( $suborder_id, array(
					'order_item_name' => $item_name,
					'order_item_type' => $item_type,
				) );
				$this->clone_order_itemmetas( $tax->get_id(), $suborder_new_tax_id );
				wc_update_order_item_meta( $suborder_new_tax_id, 'tax_amount', $main_order_item->get_total_tax() );
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

			// Just create suborders if there is more than 1 item in order
			if ( $main_order->get_item_count() <= 1 ) {
				return;
			}

			// Delete previous suborders
			if ( $args['delete_prev_suborders'] ) {
				$this->delete_previous_suborders( $main_order_id );
			}

			// Gets fees and taxes
			$fees  = $main_order->get_fees();
			$taxes = $main_order->get_taxes();

			// Counter for creating fake suborders ids
			$order_counter = 1;

			/* @var WC_Order_Item_Product $main_order_item */
			foreach ( $main_order->get_items() as $item_id => $main_order_item ) {
				$fee_value = 0;

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
						Alg_MOWC_Order_Metas::SUB_ORDER_SUB_ID  => $order_counter,
						Alg_MOWC_Order_Metas::SUB_ORDER_FAKE_ID => $main_order->get_order_number() . '-' . $order_counter,
						Alg_MOWC_Order_Metas::PARENT_ORDER_ITEM => $item_id,
					),
				);

				// Create sub order
				$suborder_id = wp_insert_post( $order_data, true );

				// Delete previous association with suborder id
				wc_delete_order_item_meta( $item_id, Alg_MOWC_Order_Item_Metas::SUB_ORDER );

				// Clone order post metas into suborder
				$exclude_post_metas = apply_filters( 'alg_mowc_exclude_cloned_order_postmetas', array(
					Alg_MOWC_Order_Metas::SUB_ORDERS,
					'_wcj_order_number',
				) );
				$this->clone_order_postmetas( $main_order_metadata, $suborder_id, $exclude_post_metas );

				// Adds line item in suborder
				$this->add_line_item_in_suborder( $main_order_item, $item_id, $suborder_id );

				// Adds fees in suborder
				$fee_value = $this->add_fees_in_suborder( $fees, $suborder_id, $main_order );

				// Adds taxes in suborder
				$this->add_taxes_in_suborder( $taxes, $suborder_id, $main_order_item );

				// Updates suborder price
				update_post_meta( $suborder_id, '_order_total', $main_order_item->get_total_tax() + $main_order_item->get_total() + $fee_value );
				update_post_meta( $suborder_id, '_order_tax', $main_order_item->get_total_tax() );

				// Updates main order meta regarding suborder
				add_post_meta( $main_order_id, Alg_MOWC_Order_Metas::SUB_ORDERS, $suborder_id, false );

				// Associate main order item with suborder id
				wc_add_order_item_meta( $item_id, Alg_MOWC_Order_Item_Metas::SUB_ORDER, $suborder_id, true );

				$order_counter ++;
			}
		}

	}
}