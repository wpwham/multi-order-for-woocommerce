<?php
/**
 * Multi order for WooCommerce - Setups suborders view
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Suborders_View' ) ) {

	class Alg_MOWC_Suborders_View {

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			// Show | Hides admin suborder list view
			add_action( 'pre_get_posts', array( $this, 'show_or_hide_admin_suborders_list_view' ) );

			// Show | Hides frontend suborder list view
			add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array(
				$this,
				'show_or_hide_frontend_suborders_list_view',
			) );

			// Manages frontend order view template on my account
			add_action( 'woocommerce_view_order', array( $this, 'woocommerce_frontend_suborder_view' ), 1 );
			add_action( 'woocommerce_view_order', array( $this, 'woocommerce_frontend_parent_order_view' ), 1 );
		}

		/**
		 * Displays the parent order view on frontend
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $order_id
		 */
		public function woocommerce_frontend_parent_order_view( $order_id ) {
			$suborders = get_post_meta( $order_id, Alg_MOWC_Order_Metas::SUB_ORDERS );
			if ( ! is_array( $suborders ) || count( $suborders ) == 0 ) {
				return;
			}

			$multiorder_plugin = alg_multiorder_for_wc();
			$template_args     = array(
				'suborders' => $suborders,
				'order_id' => $order_id,
			);
			wc_get_template( 'multiorder-view-parent-order.php', $template_args, 'woocommerce/multiorder/', $multiorder_plugin->dir . 'templates' . DIRECTORY_SEPARATOR );
		}

		/**
		 * Displays the suborder view on frontend
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $order_id
		 */
		public function woocommerce_frontend_suborder_view( $order_id ) {
			$is_sub_order = filter_var( get_post_meta( $order_id, Alg_MOWC_Order_Metas::IS_SUB_ORDER, true ), FILTER_VALIDATE_BOOLEAN );
			if ( ! $is_sub_order ) {
				return;
			}

			$fake_suborder_id = get_post_meta( $order_id, Alg_MOWC_Order_Metas::SUB_ORDER_FAKE_ID, true );

			$multiorder_plugin = alg_multiorder_for_wc();
			$template_args     = array(
				'fake_suborder_id' => $fake_suborder_id,
				'parent_order_id'  => get_post_meta( $order_id, Alg_MOWC_Order_Metas::PARENT_ORDER, true ),
			);
			wc_get_template( 'multiorder-view-suborder.php', $template_args, 'woocommerce/multiorder/', $multiorder_plugin->dir . 'templates' . DIRECTORY_SEPARATOR );
		}

		/**
		 * Setups frontend suborders display
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $query
		 */
		public function show_or_hide_frontend_suborders_list_view( $query ) {
			$show_suborders = filter_var( get_option( Alg_MOWC_Settings_General::OPTION_SUBORDERS_FRONTEND_SHOW ), FILTER_VALIDATE_BOOLEAN );
			if ( $show_suborders ) {
				return $query;
			}

			$query['meta_query'][] =
				array(
					'relation' => 'OR',
					array(
						'key'     => Alg_MOWC_Order_Metas::IS_SUB_ORDER,
						'value'   => array( 1, 'on' ),
						'compare' => 'NOT IN',
					),
					array(
						'key'     => Alg_MOWC_Order_Metas::IS_SUB_ORDER,
						'compare' => 'NOT EXISTS',
					),
				);
			return $query;
		}

		/**
		 * Setups admin suborders display
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $query
		 */
		public function show_or_hide_admin_suborders_list_view( $query ) {
			if ( ! is_admin() ) {
				return;
			}

			if ( $query->query['post_type'] != 'shop_order' ) {
				return;
			}

			$show_suborders = filter_var( get_option( Alg_MOWC_Settings_General::OPTION_SUBORDERS_ADMIN_SHOW ), FILTER_VALIDATE_BOOLEAN );
			if ( $show_suborders ) {
				return;
			}

			$query->set( 'meta_query', array(
				'relation' => 'OR',
				array(
					'key'     => Alg_MOWC_Order_Metas::IS_SUB_ORDER,
					'value'   => array( 1, 'on' ),
					'compare' => 'NOT IN',
				),
				array(
					'key'     => Alg_MOWC_Order_Metas::IS_SUB_ORDER,
					'compare' => 'NOT EXISTS',
				),
			) );
		}
	}
}