<?php
/**
 * Multi order for WooCommerce - Order searching and sorting
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Orders_Search' ) ) {

	class Alg_MOWC_Orders_Search {

		private $current_suborder_id_searched = '';

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {

			// Finds suborders
			add_action( 'pre_get_posts', array( $this, 'find_suborder_by_custom_number' ), 11 );

			// Find suborders using [woocommerce_order_tracking]
			add_filter( 'woocommerce_shortcode_order_tracking_order_id', array( $this, 'allow_suborders_to_be_tracked' ) );

			// Sort orders
			add_action( 'pre_get_posts', array( $this, 'sort_admin_orders' ), 11 );
			add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array( $this, 'sort_frontend_orders' ) );
			add_filter( 'woocommerce_my_account_my_orders_query', array( $this, 'sort_frontend_orders' ) );
		}

		/**
		 * Sort orders on frontend
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $query
		 */
		public function sort_frontend_orders( $query ) {

			$query['meta_query'][] =
				array(
					'custom_sort' => array(
						'key'     => Alg_MOWC_Order_Metas::SORT_ID,
						'compare' => 'EXISTS',
					),
				);
			$query['orderby']      = array(
				'custom_sort' => 'DESC',
			);
			return $query;
		}

		/**
		 * Sort orders
		 *
		 * @version  1.0.0
		 * @since    1.0.0
		 *
		 * @param $query
		 */
		public function sort_admin_orders( $query ) {
			if (
				! isset( $query->query['post_type'] ) ||
				$query->query['post_type'] != 'shop_order' ||
				! is_admin()
			) {
				return;
			}

			$query->set( 'meta_query', array(
				'custom_sort' => array(
					'key'     => Alg_MOWC_Order_Metas::SORT_ID,
					'compare' => 'EXISTS',
				),
			) );

			$query->set( 'orderby', array(
				'custom_sort' => 'DESC',
			) );
		}

		/**
		 * Allows suborders to be tracked.
		 *
		 * It tries to get the order id by the post meta Alg_MOWC_Order_Metas::SUB_ORDER_FAKE_ID.
		 * It also allows to track an order using # or not.
		 *
		 * @version  1.0.0
		 * @since    1.0.0
		 *
		 * @param $order_id
		 *
		 * @return mixed|null|string
		 */
		public function allow_suborders_to_be_tracked( $order_id ) {
			$order_id = filter_var( $order_id, FILTER_SANITIZE_NUMBER_INT );

			global $wpdb;
			$meta_value = Alg_MOWC_Order_Metas::SUB_ORDER_FAKE_ID;

			$query = $wpdb->prepare( "SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_value = %s AND meta_key = %s", $order_id, $meta_value );
			$var   = $wpdb->get_var( $query );

			if ( ! empty( $var ) ) {
				$order_id = $var;
			}

			return $order_id;
		}

		/**
		 * Fixes the "Search results for" in case of searching for a suborder
		 *
		 * @version  1.0.0
		 * @since    1.0.0
		 *
		 * @param $string
		 *
		 * @return string
		 */
		public function change_search_query( $string ) {
			if ( ! empty( $this->current_suborder_id_searched ) ) {
				$string = $this->current_suborder_id_searched;
			}
			return $string;
		}

		/**
		 * Finds suborders by their custom ids
		 *
		 * @version  1.0.0
		 * @since    1.0.0
		 *
		 * @param $query
		 */
		public function find_suborder_by_custom_number( $query ) {
			if (
				! is_admin() ||
				! isset( $query->query ) ||
				! isset( $query->query['s'] ) ||
				1 !== preg_match( '/^\d.*\-\d.*$/', $query->query['s'] ) ||
				true === is_numeric( $query->query['s'] ) ||
				0 == $query->query['s'] ||
				'shop_order' !== $query->query['post_type']
			) {
				return;
			}

			$custom_order_id                    = $query->query['s'];
			$this->current_suborder_id_searched = $custom_order_id;
			$query->query_vars['post__in']      = array();
			$query->query['s']                  = '';
			$query->query_vars['s']             = '';
			$query->query_vars['meta_key']      = Alg_MOWC_Order_Metas::SUB_ORDER_FAKE_ID;
			$query->query_vars['meta_value']    = $custom_order_id;

			add_filter( 'get_search_query', array( $this, 'change_search_query' ) );
		}
	}
}