<?php
/**
 * Multi order for WooCommerce - Order manager
 *
 * Creates, deletes suborders and sync them with their parent orders
 *
 * @version 1.4.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 * @author  WP Wham
 */

if ( ! class_exists( 'Alg_MOWC_Order_Manager' ) ) {

	class Alg_MOWC_Order_Manager {
		public static $is_creating_suborder=false;

		/**
		 * Constructor
		 *
		 * @version 1.1.0
		 * @since   1.0.0
		 */
		function __construct() {

			// Detects "create suborders" button click
			add_action( 'woocommerce_process_shop_order_meta', array( $this, 'create_suborders_call_on_btn_click' ) );

			// Changes suborder status when parent order changes status
			//add_action( 'woocommerce_order_status_changed', array( $this, 'sync_suborders_status_from_parent_call' ), 10, 3 );

			// Copies status from suborders to main order when they are all the same
			//add_action( 'woocommerce_order_status_changed', array( $this, 'copy_status_from_suborders_to_main_order' ), 10, 3 );

			// Sets order payment status
			//add_action( 'woocommerce_order_status_changed', array( $this, 'set_order_payment_status' ), 11, 3 );

			// Call the function that deducts / undeducts suborder from main order
			//add_action( 'woocommerce_order_status_changed', array( $this, 'deduct_undeduct_suborder_on_status_change' ), 10, 3 );
			//add_action( 'woocommerce_order_status_changed', array( $this, 'update_remaining_on_status_change' ), 10, 3 );

			// Deletes suborder post if correspondent item id is removed from main order
			add_action( 'woocommerce_before_delete_order_item', array( $this, 'remove_suborder_post_on_main_order_item_removal' ) );

			// Deletes suborder item from main order in case suborder post is removed
			add_action( 'before_delete_post', array( $this, 'remove_suborder_item_on_suborder_post_removal'), 10 );
			if ( Alg_MOWC_Core::is_hpos_enabled() ) {
				add_action( 'woocommerce_before_delete_order', array( $this, 'remove_suborder_item_on_suborder_post_removal' ), 10 );
			}

			// Create suborders automatically on new order item creation
			add_action( 'woocommerce_new_order_item', array( $this, 'create_suborders_call_on_new_order_item' ), 999, 3 );

			// Create suborders call automatically on new order creation
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'create_suborders_call_on_new_order' ) );
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'set_main_order_initial_status' ) );

			// Create suborders call automatically on new order creation (blocks)
			add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'create_suborders_call_on_new_order' ) );
			add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'set_main_order_initial_status' ) );

			// Updates suborder when the main order item gets updated
			//add_action( 'woocommerce_update_order_item', array( $this, 'update_suborder_on_main_order_update' ), 10, 3 );

			// Updates main order item when the suborder gets updated
			//add_action( 'woocommerce_update_order_item', array( $this, 'update_main_order_item_on_suborder_update' ), 10, 3 );

			// Config Emails
			add_action( 'woocommerce_email', array( $this, 'setup_emails' ) );
		}

		/**
		 * Config emails
		 *
		 * @version 1.1.1
		 * @since   1.0.0
		 *
		 * @param WC_Emails $emails_class
		 */
		public function setup_emails( WC_Emails $emails_class ) {

			foreach ( $emails_class->get_emails() as $email ) {
				$id = $email->id;
				//if ( ! in_array( $id, array( 'new_order' ) ) ) {
					add_filter( "woocommerce_email_recipient_{$id}", array( $this, 'remove_new_suborders_emails' ), 10, 2 );
				//}
			}
		}

		/**
		 * Remove recently created suborder emails
		 *
		 * @version 1.1.1
		 * @since   1.0.0
		 *
		 * @param          $recipient
		 * @param WC_Order $order
		 *
		 * @return string
		 */
		function remove_new_suborders_emails( $recipient, $order ) {
			if (
				$order == null ||
				//! self::$is_creating_suborder ||
				( current_filter() == 'woocommerce_email_recipient_new_order' && ! filter_var( $order->get_meta( Alg_MOWC_Order_Metas::IS_SUB_ORDER ), FILTER_VALIDATE_BOOLEAN ) )
			) {
				return $recipient;
			}

			if (
				current_filter() == 'woocommerce_email_recipient_new_order' &&
				filter_var( $order->get_meta( Alg_MOWC_Order_Metas::IS_SUB_ORDER ), FILTER_VALIDATE_BOOLEAN )
			) {
				return '';
			}

			$send_to_main_order = get_option( Alg_MOWC_Settings_General::OPTION_EMAILS_SEND_MAIN_ORDER, 'no' );
			$send_to_sub_orders = get_option( Alg_MOWC_Settings_General::OPTION_EMAILS_SEND_SUB_ORDER, 'no' );

			if ( $send_to_sub_orders != 'yes' ) {
				if ( filter_var( $order->get_meta( Alg_MOWC_Order_Metas::IS_SUB_ORDER ), FILTER_VALIDATE_BOOLEAN ) ) {
					$recipient = '';
				}
			}

			if ( $send_to_main_order != 'yes' ) {
				$suborders = wp_list_pluck( $order->get_meta( Alg_MOWC_Order_Metas::SUB_ORDERS, false ), 'value' );

				if (
					$suborders &&
					is_array( $suborders ) &&
					count( $suborders ) > 0
				) {
					$recipient = '';
				}
			}

			return $recipient;
		}

		/**
		 * Set main order initial status
		 *
		 * @version 1.0.10
		 * @since   1.0.0
		 *
		 * @param $result
		 * @param $order_id
		 *
		 * @return mixed
		 */
		public function set_main_order_initial_status( $order_id ) {
			$default_main_order_status = get_option( Alg_MOWC_Settings_General::OPTION_DEFAULT_MAIN_ORDER_STATUS );
			if ( ! empty( $default_main_order_status ) ) {
				$order     = wc_get_order( $order_id );
				$suborders = $order ? wp_list_pluck( $order->get_meta( Alg_MOWC_Order_Metas::SUB_ORDERS, false ), 'value' ) : array();
				if ( count( $suborders ) > 1 ) {
					$order->set_status( $default_main_order_status );
					$order->save();
				}
			}
		}

		/**
		 * Tries to recalculate order price
		 *
		 * @version 1.0.1
		 * @since   1.0.1
		 *
		 * @param $order_id
		 */
		public function try_to_recalculate_order_price( $order_id ) {
			$plugin = Alg_MOWC_Core::get_instance();
			$bkg_process = $plugin->order_bkg_process;

			$orders_queue = get_option( 'alg_mowc_orders_queue', array() );
			if ( ! in_array( $order_id, $orders_queue, true ) ) {
				$bkg_process->cancel_process();
				$bkg_process->push_to_queue( $order_id );
				$bkg_process->save()->dispatch();
			}
			$orders_queue[] = $order_id;
			$orders_queue   = array_unique( $orders_queue );
			update_option( 'alg_mowc_orders_queue', $orders_queue );

			/*wp_clear_scheduled_hook( 'recalculate_order_price_event', array( $order_id ) );
			wp_schedule_single_event( time() + 1, 'recalculate_order_price_event', array( $order_id ) );*/
		}

		/**
		 * Creates suborders automatically on new order item creation
		 *
		 * @version 1.1.0
		 * @since   1.0.0
		 *
		 * @param $item_id
		 * @param $item
		 * @param $order_id
		 */
		public function create_suborders_call_on_new_order_item( $item_id, $item, $order_id ) {
			if ( ! is_admin() ) {
				return;
			}

			$order = wc_get_order( $order_id );
			if ( $order ) {
				$order->update_meta_data( Alg_MOWC_Order_Metas::SORT_ID, $order_id . '9999' );
				$order->save_meta_data();
			}

			if ( $order && filter_var( $order->get_meta( Alg_MOWC_Order_Metas::IS_SUB_ORDER ), FILTER_VALIDATE_BOOLEAN ) ) {
				return;
			}
			if ( ! filter_var( get_option( Alg_MOWC_Settings_General::OPTION_SUBORDERS_CREATE_AUTOMATICALLY ), FILTER_VALIDATE_BOOLEAN ) ) {
				return;
			}
			$this->create_suborders( $order_id );
		}

		/**
		 * Creates sort meta on orders that don't have it yet
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		public static function set_sort_order_meta() {
			$order_ids = wc_get_orders( array(
				'status'     => array_keys( wc_get_order_statuses() ),
				'limit'      => -1,
				'return'     => 'ids',
				'meta_query' => array(
					array(
						'key'     => Alg_MOWC_Order_Metas::SORT_ID,
						'compare' => 'NOT EXISTS',
					),
				),
			) );

			foreach ( $order_ids as $order_id ) {
				$order = wc_get_order( $order_id );
				if ( ! $order ) {
					continue;
				}
				$order->update_meta_data( Alg_MOWC_Order_Metas::SORT_ID, $order_id . '9999' );
				$order->save_meta_data();
			}
		}

		/**
		 * Deletes suborder item from main order in case a suborder post is removed
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $post_id
		 */
		public function remove_suborder_item_on_suborder_post_removal( $post_id ) {
			$order = wc_get_order( $post_id );

			if ( ! $order || 'shop_order' !== $order->get_type() ) {
				return;
			}

			$parent_order_id = $order->get_meta( Alg_MOWC_Order_Metas::PARENT_ORDER );
			if ( empty( $parent_order_id ) ) {
				return;
			}

			$parent_order_item_id = $order->get_meta( Alg_MOWC_Order_Metas::PARENT_ORDER_ITEM );
			$parent_order         = wc_get_order( $parent_order_id );
			if ( $parent_order ) {
				$parent_order->delete_meta_data_value( Alg_MOWC_Order_Metas::SUB_ORDERS, (string) $post_id );
				$parent_order->save_meta_data();
			}
			$order->update_meta_data( Alg_MOWC_Order_Metas::DELETING, true );
			$order->save_meta_data();
			wc_delete_order_item( $parent_order_item_id );
		}

		/**
		 * Deletes suborder if correspondent item id is removed from main order
		 *
		 * @version 1.1.0
		 * @since   1.0.0
		 *
		 * @param $item_id
		 */
		public function remove_suborder_post_on_main_order_item_removal( $item_id ) {
			$suborder_id = wc_get_order_item_meta( $item_id, Alg_MOWC_Order_Item_Metas::SUB_ORDER, true );
			$suborder    = wc_get_order( $suborder_id );
			if ( $suborder_id && $suborder ) {
				$main_order_id = $suborder->get_meta( Alg_MOWC_Order_Metas::PARENT_ORDER );
				$main_order    = wc_get_order( $main_order_id );
				if ( $main_order ) {
					$main_order->delete_meta_data_value( Alg_MOWC_Order_Metas::SUB_ORDERS, (string) $suborder_id );
					$main_order->save_meta_data();
				}
				$suborder->delete_meta_data( Alg_MOWC_Order_Metas::PARENT_ORDER );
				$suborder->save_meta_data();
				if ( ! filter_var( $suborder->get_meta( Alg_MOWC_Order_Metas::DELETING ), FILTER_VALIDATE_BOOLEAN ) ) {
					$suborder->delete( true );
				}

				if ( $main_order ) {
					$main_order->calculate_totals();
				}
			}
		}

		/**
		 * Recalculate order total price
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param      $order_id
		 * @param bool $calculate_taxes
		 */
		/*public function recalculate_order( $order_id, $calculate_taxes = false ) {
			$order = wc_get_order( $order_id );
			if ( $calculate_taxes ) {
				$order->calculate_taxes();
			}
			$order->calculate_totals();
		}*/

		/**
		 * Deducts suborder from main order
		 *
		 * @version  1.0.1
		 * @since    1.0.0
		 *
		 * @param          $order_id
		 * @param          $transition_from
		 * @param          $transition_to
		 *
		 * @internal param $order_id
		 * @internal param WC_Order $order
		 */
		public function deduct_order( $order_id, $transition_from, $transition_to ) {
			$order = wc_get_order( $order_id );
			$main_order = $order->get_meta( Alg_MOWC_Order_Metas::PARENT_ORDER );

			/* @var WC_Order_Item_Product $order_item */
			foreach ( $order->get_items() as $item_id => $order_item ) {
				//wc_update_order_item_meta( $item_id, '_line_total', 0 );
				//wc_update_order_item_meta( $item_id, '_line_tax', 0 );

				$line_tax_data = wc_get_order_item_meta( $item_id, '_line_tax_data', true );
				if ( is_string( $line_tax_data ) ) {
					$line_tax_data = unserialize( $line_tax_data );
				}
				if ( isset( $line_tax_data['total'] ) ) {
					foreach ( $line_tax_data['total'] as $data_key => $data_value ) {
						//$line_tax_data['total'][ $data_key ] = 0;
					}
					wc_update_order_item_meta( $item_id, '_line_tax_data', $line_tax_data );
				}

				if ( ! empty( $main_order ) ) {
					$this->update_main_order_price_based_on_suborder( $order, $item_id );
				}
			}
			$this->try_to_recalculate_order_price( $order_id );
		}

		/**
		 * UnDeducts suborder from main order
		 *
		 * @version  1.0.1
		 * @since    1.0.0
		 *
		 * @param          $order_id
		 * @param          $transition_from
		 * @param          $transition_to
		 *
		 * @internal param $order_id
		 * @internal param WC_Order $order
		 */
		public function undeduct_order( $order_id, $transition_from, $transition_to ) {
			$order = wc_get_order( $order_id );
			$main_order = $order->get_meta( Alg_MOWC_Order_Metas::PARENT_ORDER );

			/* @var WC_Order_Item_Product $order_item */
			foreach ( $order->get_items() as $item_id => $order_item ) {
				$line_subtotal = wc_get_order_item_meta( $item_id, '_line_subtotal', true );
				wc_update_order_item_meta( $item_id, '_line_total', $line_subtotal );
				$line_tax_data  = wc_get_order_item_meta( $item_id, '_line_tax_data', true );
				$line_tax_count = 0;
				if ( is_string( $line_tax_data ) ) {
					$line_tax_data = unserialize( $line_tax_data );
				}
				if ( isset( $line_tax_data['subtotal'] ) ) {
					foreach ( $line_tax_data['subtotal'] as $data_key => $data_value ) {
						$line_tax_count                      += $data_value;
						$line_tax_data['total'][ $data_key ] = $data_value;
					}
					wc_update_order_item_meta( $item_id, '_line_tax', $line_tax_count );
					wc_update_order_item_meta( $item_id, '_line_tax_data', $line_tax_data );
				}
				if ( ! empty( $main_order ) ) {
					$this->update_main_order_price_based_on_suborder( $order, $item_id );
				}
			}
			$this->try_to_recalculate_order_price( $order_id );
		}

		/**
		 * Update main order price based on suborder
		 *
		 * @version  1.0.1
		 * @since    1.0.0
		 * @param WC_Order $suborder
		 * @param          $suborder_item_id
		 */
		public function update_main_order_price_based_on_suborder( WC_Order $suborder, $suborder_item_id ) {
			$suborder_id          = $suborder->get_id();
			$main_order_id        = $suborder->get_meta( Alg_MOWC_Order_Metas::PARENT_ORDER );
			$parent_order_item_id = $suborder->get_meta( Alg_MOWC_Order_Metas::PARENT_ORDER_ITEM );
			$this->clone_order_itemmetas( $suborder_item_id, $parent_order_item_id, array( Alg_MOWC_Order_Item_Metas::SUB_ORDER ), 'update' );
			$this->try_to_recalculate_order_price( $main_order_id );
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
		/*public function deduct_suborder_from_order( $order_id, $transition_from, $transition_to ) {
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
			wp_clear_scheduled_hook( 'recalculate_order_price_event', array( $main_order_id ) );
			wp_schedule_single_event( time() + 1, 'recalculate_order_price_event', array( $main_order_id ) );
		}*/

		/**
		 * Saves suborders status from parent order
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $parent_order_id
		 */
		public function sync_suborders_status_from_parent( $parent_order_id, $suborders, $transition_from, $transition_to ) {
			$payment_status_exception_slugs = get_option( Alg_MOWC_Settings_General::OPTION_SUBORDERS_EXCEPTION_COPY_STATUS, true );
			foreach ( $suborders as $suborder_id ) {

				$payment_status_tax = new Alg_MOWC_Order_Payment_Status();
				$terms              = wp_get_post_terms( $suborder_id, $payment_status_tax->id );
				foreach ( $terms as $term ) {
					if ( in_array( $term->slug, $payment_status_exception_slugs ) ) {
						continue 2;
					}
				}

				wp_update_post( array(
					'ID'          => $suborder_id,
					'post_status' => 'wc-' . $transition_to,
				) );

				do_action( 'woocommerce_order_status_changed', $suborder_id, $transition_from, $transition_to );
				do_action( "woocommerce_order_status_{$transition_from}_to_{$transition_to}_notification", $suborder_id );
			}
		}

		/**
		 * Create suborders call automatically on new order creation
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $order_id
		 */
		public function create_suborders_call_on_new_order( $order_id ) {
			if ( is_a( $order_id, 'WC_Order' ) ) {
				$order_id = $order_id->get_id();
			}
			$order = wc_get_order( $order_id );
			if ( $order && filter_var( $order->get_meta( Alg_MOWC_Order_Metas::IS_SUB_ORDER ), FILTER_VALIDATE_BOOLEAN ) ) {
				return;
			}

			if ( ! filter_var( get_option( Alg_MOWC_Settings_General::OPTION_SUBORDERS_CREATE_AUTOMATICALLY ), FILTER_VALIDATE_BOOLEAN ) ) {
				return;
			}

			$this->create_suborders( $order_id );
		}

		/**
		 * Detects "create suborders" button click
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function create_suborders_call_on_btn_click( $post_id ) {
			$order = wc_get_order( $post_id );
			if ( ! $order || 'shop_order' !== $order->get_type() ) {
				return;
			}

			if ( ! isset( $_POST['wpwham_multi_order_create_suborders'] ) ) {
				return;
			}

			if ( filter_var( $order->get_meta( Alg_MOWC_Order_Metas::IS_SUB_ORDER ), FILTER_VALIDATE_BOOLEAN ) ) {
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
			$suborder = wc_get_order( $suborder_id );

			foreach ( $main_order_metadata->get_meta_data() as $meta ) {
				if ( ! in_array( $meta->key, $exclude ) ) {
					$suborder->add_meta_data( $meta->key, $meta->value, false );
				}
			}

			$suborder->save_meta_data();
		}

		/**
		 * Clones order item metas
		 *
		 * @version 1.0.2
		 * @since   1.0.0
		 *
		 * @param        $order_item_id
		 * @param        $target_order_id
		 * @param string $method 'add' | 'update'
		 */
		public function clone_order_itemmetas( $order_item_id, $target_order_id, $exclude = array(), $method = 'add' ) {
			$order_item_metas = wc_get_order_item_meta( $order_item_id, '' );
			foreach ( $order_item_metas as $index => $meta_value ) {
				foreach ( $meta_value as $value ) {
					if ( ! in_array( $index, $exclude ) ) {
						if ( $method == 'add' ) {
							wc_add_order_item_meta( $target_order_id, $index, maybe_unserialize( $value ) );
						} else if ( $method == 'update' ) {
							wc_update_order_item_meta( $target_order_id, $index, maybe_unserialize( $value ) );
						}
					}
				}
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
		 *
		 * @return bool|int
		 */
		public function add_line_item_in_suborder( $main_order_item, $item_id, $suborder_id ) {
			$item_name        = $main_order_item['name'];
			$item_type        = $main_order_item->get_type();
			$suborder_item_id = wc_add_order_item( $suborder_id, array(
				'order_item_name' => $item_name,
				'order_item_type' => $item_type,
			) );

			// Clone order item metas
			$this->clone_order_itemmetas( $item_id, $suborder_item_id, array( Alg_MOWC_Order_Item_Metas::SUB_ORDER ) );
			return $suborder_item_id;
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
		 * Adds shipping in suborder
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 *
		 * @param $shippings
		 * @param $suborder_id
		 * @param $main_order
		 *
		 * @return float|int
		 */
		function add_shipping_in_suborder( $shippings, $suborder_id, $main_order ) {
			$shipping_value_count = 0;
			/* @var WC_Order_Item_Shipping $shippings */
			foreach ( $shippings as $shipping ) {
				$item_name           = $shipping->get_name();
				$item_type           = $shipping->get_type();
				$suborder_new_shipping_id = wc_add_order_item( $suborder_id, array(
					'order_item_name' => $item_name,
					'order_item_type' => $item_type,
				) );
				$this->clone_order_itemmetas( $shipping->get_id(), $suborder_new_shipping_id );
				$shipping_value       = $shipping->get_total() / $main_order->get_item_count();
				$shipping_value_count += $shipping_value;
				wc_update_order_item_meta( $suborder_new_shipping_id, 'cost', $shipping_value );
			}
			return $shipping_value_count;
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
				//wc_update_order_item_meta( $suborder_new_tax_id, 'tax_amount', $main_order_item->get_total_tax() );
			}
		}

		/**
		 * Calculates order item product discount.
		 *
		 * @version 1.2.2
		 * @since   1.2.2
		 *
		 * @param WC_Order_Item_Product $order_item_product
		 *
		 * @return float|int
		 */
		function calculate_order_item_product_discount( WC_Order_Item_Product $order_item_product ) {
			$subtotal = $order_item_product->get_subtotal();
			$total    = $order_item_product->get_total();
			$discount = $order_item_product->get_quantity() * ( $subtotal - $total );
			return $discount;
		}

		/**
		 * Creates suborders from a main order
		 *
		 * @since   1.0.0
		 *
		 * @param $main_order_id
		 */
		public function create_suborders( $main_order_id, $args = array() ) {
			$args = wp_parse_args( $args, array(
				'delete_prev_suborders' => false,
			) );

			if ( true !== filter_var( apply_filters( 'alg_mowc_create_suborders', true, $main_order_id ), FILTER_VALIDATE_BOOLEAN ) ) {
				return;
			}

			$main_order                 = wc_get_order( $main_order_id );
			if ( ! $main_order ) {
				return;
			}
			$order_number               = is_a( $main_order, 'WC_Order_Refund' ) ? apply_filters( 'woocommerce_order_number', $main_order->get_id(), $main_order ) : $main_order->get_order_number();
			$original_main_order_status = $main_order->get_status();

			$consider_quantity = false;

			// Only create suborders if there is more than 1 item in order
			if ( ! $consider_quantity && count( $main_order->get_items() ) <= 1 ) {

				// Creates a fake id for main order
				$main_order->update_meta_data( Alg_MOWC_Order_Metas::SUB_ORDER_FAKE_ID, $order_number );
				$main_order->save_meta_data();

				return;
			} elseif ( $consider_quantity && count( $main_order->get_items() ) == 1 ) {

				/* @var WC_Order_Item_Product $item_data */
				foreach ( $main_order->get_items() as $item_id => $main_order_item ) {
					if ( $main_order_item->get_quantity() < get_option( Alg_MOWC_Settings_General::OPTION_QUANTITY_MIN, 2 ) ) {
						return;
					}
				}
			}

			self::$is_creating_suborder = true;

			// Get meta data from order
			$main_order_metadata = $main_order;

			$default_main_order_status = false;

			// Delete previous suborders
			if ( $args['delete_prev_suborders'] ) {
				$this->delete_suborders_from_main_order( $main_order_id );
			}

			// Gets fees and taxes
			$fees  = $main_order->get_fees();
			$taxes = $main_order->get_taxes();
			$shipping = $main_order->get_items( 'shipping' );

			// Counter for creating fake suborders ids
			$last_suborder_id      = $main_order->get_meta( Alg_MOWC_Order_Metas::LAST_SUBORDER_SUB_ID );
			$order_counter         = $last_suborder_id ? $last_suborder_id + 1 : 1;
			$order_inverse_counter = $last_suborder_id ? 999-$last_suborder_id : 999;

			$suborders = array();

			/* @var WC_Order_Item_Product $main_order_item */
			foreach ( $main_order->get_items() as $item_id => $main_order_item ) {

				$prev_suborder_ids = wc_get_order_item_meta( $item_id, Alg_MOWC_Order_Item_Metas::SUB_ORDER, ! $consider_quantity );

				if ( ! is_array( $prev_suborder_ids ) ) {
					if ( $prev_suborder_ids ) {
						$prev_suborder_ids = array( $prev_suborder_ids );
					} else {
						$prev_suborder_ids = array();
					}
				}

				$repetitions = 1;
				$product = $main_order_item->get_product();
				if ( true !== filter_var( apply_filters( 'alg_mowc_order_item_valid_as_suborder', true, $item_id, $main_order_item, $main_order ), FILTER_VALIDATE_BOOLEAN ) ) {
					continue;
				}

				if ( $consider_quantity ) {
					if ( $main_order_item->get_quantity() >= get_option( Alg_MOWC_Settings_General::OPTION_QUANTITY_MIN, 2 ) ) {
						$repetitions = $main_order_item->get_quantity() - count( $prev_suborder_ids );
						// prevent duplicates
						if ( $repetitions < 1 ) {
							continue;
						}
					}
				} else {
					if ( ! empty( $prev_suborder_ids ) ) {
						continue;
					}
				}

				for ( $i = 0; $i < $repetitions; $i ++ ) {

					$fee_value        = 0;

					// Suborder default status from admin settings
					$suborder_status_from_admin_settings = false;
					if ( empty( $suborder_status_from_admin_settings ) ) {
						$suborder_status = $original_main_order_status;
					} else {
						if ( empty( $default_main_order_status ) ) {
							$suborder_status = $suborder_status_from_admin_settings == 'main_order' ? $original_main_order_status : $suborder_status_from_admin_settings;
						} else {
							$suborder_status = $suborder_status_from_admin_settings == 'main_order' ? $default_main_order_status : $suborder_status_from_admin_settings;
						}
					}

					// Create sub order
					$suborder = new WC_Order();
					$suborder->set_customer_id( $main_order->get_customer_id() );
					$suborder->set_status( $suborder_status );
					$suborder->set_created_via( $main_order->get_created_via() );
					$suborder->set_currency( $main_order->get_currency() );
					$suborder->set_prices_include_tax( $main_order->get_prices_include_tax() );
					$suborder->set_customer_note( $main_order->get_customer_note() );
					$suborder->set_payment_method( $main_order->get_payment_method() );
					$suborder->set_payment_method_title( $main_order->get_payment_method_title() );
					$meta_input = array(
						Alg_MOWC_Order_Metas::IS_SUB_ORDER      => true,
						Alg_MOWC_Order_Metas::PARENT_ORDER      => $main_order_id,
						Alg_MOWC_Order_Metas::SUB_ORDER_SUB_ID  => $order_counter,
						Alg_MOWC_Order_Metas::SUB_ORDER_FAKE_ID => $order_number . '-' . $order_counter,
						Alg_MOWC_Order_Metas::SORT_ID           => $main_order->get_id() . $order_inverse_counter,
						Alg_MOWC_Order_Metas::PARENT_ORDER_ITEM => $item_id,
					);
					foreach ( $meta_input as $key => $value ) {
						$suborder->update_meta_data( $key, $value );
					}
					$suborder_id = $suborder->save();
					$suborders[] = $suborder_id;

					// Creates a fake id for main order
					$main_order->update_meta_data( Alg_MOWC_Order_Metas::SUB_ORDER_FAKE_ID, $order_number );
					$main_order->update_meta_data( Alg_MOWC_Order_Metas::REMAINING, $main_order->get_total() );
					$main_order->save_meta_data();

					// Delete previous association with suborder id
					//wc_delete_order_item_meta( $item_id, Alg_MOWC_Order_Item_Metas::SUB_ORDER );

					// Clone order post metas into suborder
					$exclude_post_metas = apply_filters( 'alg_mowc_exclude_cloned_order_postmetas', array(
						Alg_MOWC_Order_Metas::SUB_ORDERS,
						Alg_MOWC_Order_Metas::SUB_ORDER_FAKE_ID,
						Alg_MOWC_Order_Metas::SORT_ID,
						Alg_MOWC_Order_Metas::LAST_SUBORDER_SUB_ID,
						Alg_MOWC_Order_Metas::REMAINING,
						'_wcj_order_number',
						'has_sub_order',
						'_order_key',
						'_customer_user',
						'_payment_method',
						'_payment_method_title',
						'_order_currency',
						'_prices_include_tax',
						'_order_total',
						'_order_tax',
						'_cart_discount',
					) );
					$this->clone_order_postmetas( $main_order_metadata, $suborder_id, $exclude_post_metas );

					// Adds line item in suborder
					$suborder_item_id = $this->add_line_item_in_suborder( $main_order_item, $item_id, $suborder_id );

					// Adds fees in suborder
					$fee_value = $this->add_fees_in_suborder( $fees, $suborder_id, $main_order );

					// Adds shipping in suborder
					$this->add_shipping_in_suborder( $shipping, $suborder_id, $main_order );

					// Adds taxes in suborder
					$this->add_taxes_in_suborder( $taxes, $suborder_id, $main_order_item );

					// Updates suborder price
					$suborder = wc_get_order( $suborder_id );
					$suborder->update_meta_data( Alg_MOWC_Order_Metas::REMAINING, $main_order_item->get_total_tax() + $main_order_item->get_total() + $fee_value );
					$suborder->save_meta_data();

					if ( $consider_quantity ) {
						wc_update_order_item_meta( $suborder_item_id, '_qty', 1 );
						wc_update_order_item_meta( $suborder_item_id, '_line_total', wc_get_price_excluding_tax( $product ) );
						wc_update_order_item_meta( $suborder_item_id, '_line_subtotal', wc_get_price_excluding_tax( $product ) );
					}
					
					// Grab the order and recalculate taxes.
					$suborder->calculate_taxes();
					$suborder->calculate_totals( false );
					unset( $suborder );

					// Updates main order meta regarding suborder
					$main_order->add_meta_data( Alg_MOWC_Order_Metas::SUB_ORDERS, (string) $suborder_id, false );

					// Associate main order item with suborder id
					wc_add_order_item_meta( $item_id, Alg_MOWC_Order_Item_Metas::SUB_ORDER, (string) $suborder_id, ! $consider_quantity );

					// Saves last suborder sub id
					$main_order->update_meta_data( Alg_MOWC_Order_Metas::LAST_SUBORDER_SUB_ID, $order_counter );
					$main_order->update_meta_data( Alg_MOWC_Order_Metas::SORT_ID, $main_order_id . '9999' );
					$main_order->save_meta_data();

					// Update status
					do_action( 'mofwc_after_insert_suborder', $suborder_id, $main_order_id );

					$order_counter ++;
					$order_inverse_counter --;
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
		public function delete_suborders_from_main_order( $main_order_id ) {
			$main_order     = wc_get_order( $main_order_id );
			$prev_suborders = $main_order ? wp_list_pluck( $main_order->get_meta( Alg_MOWC_Order_Metas::SUB_ORDERS, false ), 'value' ) : array();
			if ( count( $prev_suborders ) > 0 ) {
				foreach ( $prev_suborders as $prev_suborder_id ) {
					$prev_suborder = wc_get_order( $prev_suborder_id );
					if ( $prev_suborder ) {
						$prev_suborder->delete( true );
					}
				}
				$main_order->delete_meta_data( Alg_MOWC_Order_Metas::SUB_ORDERS );
				$main_order->save_meta_data();
			}
		}

	}
}