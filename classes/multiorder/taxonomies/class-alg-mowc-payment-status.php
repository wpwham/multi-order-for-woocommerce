<?php
/**
 * Multi order for WooCommerce - Payment status
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Order_Payment_Status' ) ) {

	class Alg_MOWC_Order_Payment_Status {

		public $id = 'alg_mowc_payment_status';

		// Taxonomy args
		protected $labels;
		protected $args;

		/**
		 * Setups the post type
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function setup() {
			$this->set_args();
		}

		/**
		 * Creates the taxonomy
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function create_taxonomy() {
			add_action( 'init', array( $this, 'add' ), 1 );
		}

		/**
		 * Setups a custom menu to be displayed on woocommerce menu
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function setup_menu() {
			add_action( 'parent_file', array( $this, 'menu_highlight' ) );
			add_action( 'admin_menu', array( $this, 'create_fake_page' ), 99 );
		}

		/**
		 * Creates a fake page to be displayed on woocommerce menu
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function create_fake_page() {
			add_submenu_page( 'woocommerce', __( 'Payment status', 'multi-order-for-woocommerce' ), __( 'Payment status', 'multi-order-for-woocommerce' ), 'manage_options', "edit-tags.php?taxonomy={$this->id}" );
		}

		/**
		 * Highlights correct menu
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $parent_file
		 *
		 * @return string
		 */
		public function menu_highlight( $parent_file ) {
			global $current_screen;

			$taxonomy = $current_screen->taxonomy;
			if ( $taxonomy == $this->id ) {
				$parent_file = 'woocommerce';
			}

			return $parent_file;
		}

		/**
		 * Adds taxonomy
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add() {
			$this->setup();
			$this->register();
		}

		/**
		 * Creates initial status terms
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * Called when the plugin is enabled
		 */
		public function create_initial_status() {
			if ( term_exists( 'paid', $this->id ) == null ) {
				$response = wp_insert_term(
					__( 'Paid', 'multi-order-for-woocommerce' ),
					$this->id,
					array(
						'slug' => 'paid',
					)
				);
				if ( ! is_wp_error( $response ) ) {
					$payment_status_cmb = new Alg_MOWC_Payment_Status_CMB();
					update_term_meta( $response['term_id'], $payment_status_cmb->meta_deduct_from_main_order, 'on' );
					update_term_meta( $response['term_id'], $payment_status_cmb->meta_change_main_order, 'on' );
					update_term_meta( $response['term_id'], $payment_status_cmb->meta_change_sub_order, 'on' );
					update_term_meta( $response['term_id'], $payment_status_cmb->meta_status, array(
						'wc-processing',
						'wc-completed',
					) );
				}
			}

			if ( term_exists( 'unpaid', $this->id ) == null ) {
				$response = wp_insert_term(
					__( 'Unpaid', 'multi-order-for-woocommerce' ),
					$this->id,
					array(
						'slug' => 'unpaid',
					)
				);
				if ( ! is_wp_error( $response ) ) {
					$payment_status_cmb = new Alg_MOWC_Payment_Status_CMB();
					update_term_meta( $response['term_id'], $payment_status_cmb->meta_deduct_from_main_order, 'off' );
					update_term_meta( $response['term_id'], $payment_status_cmb->meta_change_main_order, 'on' );
					update_term_meta( $response['term_id'], $payment_status_cmb->meta_change_sub_order, 'on' );
					update_term_meta( $response['term_id'], $payment_status_cmb->meta_status, array(
						'wc-pending',
						'wc-on-hold',
						'wc-cancelled',
						'wc-refunded',
						'wc-failed',
					) );
				}
			}

			if ( term_exists( 'partial-paid', $this->id ) == null ) {
				$response = wp_insert_term(
					__( 'Partial paid', 'multi-order-for-woocommerce' ),
					$this->id,
					array(
						'slug' => 'partial-paid',
					)
				);
				if ( ! is_wp_error( $response ) ) {
					$payment_status_cmb = new Alg_MOWC_Payment_Status_CMB();
					update_term_meta( $response['term_id'], $payment_status_cmb->meta_deduct_from_main_order, 'off' );
					update_term_meta( $response['term_id'], $payment_status_cmb->meta_change_main_order, 'on' );
					update_term_meta( $response['term_id'], $payment_status_cmb->meta_change_sub_order, 'off' );
				}
			}
		}

		/**
		 * Setups the arguments for creating the taxonomy
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function set_args() {
			// Add new taxonomy, make it hierarchical (like categories)
			$labels = array(
				'name'              => __( 'Payment Status', 'multi-order-for-woocommerce' ),
				'singular_name'     => __( 'Payment Status', 'multi-order-for-woocommerce' ),
				'search_items'      => __( 'Search Status', 'multi-order-for-woocommerce' ),
				'all_items'         => __( 'All Status', 'multi-order-for-woocommerce' ),
				'parent_item'       => __( 'Parent Status', 'multi-order-for-woocommerce' ),
				'parent_item_colon' => __( 'Parent Status:', 'multi-order-for-woocommerce' ),
				'edit_item'         => __( 'Edit Status', 'multi-order-for-woocommerce' ),
				'update_item'       => __( 'Update Status', 'multi-order-for-woocommerce' ),
				'add_new_item'      => __( 'Add New Status', 'multi-order-for-woocommerce' ),
				'new_item_name'     => __( 'New Status Name', 'multi-order-for-woocommerce' ),
				'menu_name'         => __( 'Payment Status', 'multi-order-for-woocommerce' ),
			);

			$args = array(
				'hierarchical'       => true,
				'labels'             => $labels,
				'show_ui'            => true,
				//'show_in_menu'       => 'edit.php?post_type=shop_order',
				'show_in_menu'       => true,
				'show_admin_column'  => false,
				'show_in_quick_edit' => false,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'payment-status' ),
			);

			$this->labels = $labels;
			$this->args   = $args;
		}

		/**
		 * Registers the taxonomy
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function register() {
			$args = $this->args;
			register_taxonomy( $this->id, 'shop_order', $args );
		}
	}
}