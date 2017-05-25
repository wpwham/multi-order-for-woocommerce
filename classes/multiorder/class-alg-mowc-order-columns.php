<?php
/**
 * Multi order for WooCommerce - Order columns
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Order_Columns' ) ) {

	class Alg_MOWC_Order_Columns {

		public $suborders_column_id = 'alg_mowc_suborders';

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			$post_type = 'shop_order';

			// Setups admin columns
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'setup_admin_order_columns' ), 1, 2 );
			add_filter( "manage_{$post_type}_posts_columns", array( $this, 'add_admin_suborders_column' ), 20 );

			// Setups frontend columns
			add_filter( "woocommerce_account_orders_columns", array( $this, 'add_frontend_order_columns' ) );
			add_action( "woocommerce_my_account_my_orders_column_{$this->suborders_column_id}", array(
				$this,
				'setup_frontend_suborders_column',
			) );
			add_action( "woocommerce_my_account_my_orders_column_order-number", array(
				$this,
				'setup_frontend_order_number_column',
			) );
		}

		/**
		 * Setups frontend order number column
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param WC_Order $order
		 */
		public function setup_frontend_order_number_column( WC_Order $order ) {
			$suborder_fake_id = get_post_meta( $order->get_id(), Alg_MOWC_Order_Metas::SUB_ORDER_FAKE_ID, true );
			if ( $suborder_fake_id ) {
				echo '<div style="margin-bottom:5px"><a href="' . $order->get_view_order_url() . '" class="row-title"><strong>' . esc_attr( $suborder_fake_id ) . '</strong> (suborder)</a></div>';
			} else {
				?>
                <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
					<?php echo _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number(); ?>
                </a>
				<?php
			}
		}

		/**
		 * Setups frontend suborders column
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param WC_Order $order
		 */
		public function setup_frontend_suborders_column( WC_Order $order ) {

			$suborders = get_post_meta( $order->get_id(), Alg_MOWC_Order_Metas::SUB_ORDERS );
			$counter   = 1;
			if ( is_array( $suborders ) && count( $suborders ) > 0 ) {
				echo '<ul style="margin:0;padding:0;list-style:none">';
				foreach ( $suborders as $suborder_id ) {
					$suborder = wc_get_order( $suborder_id );
					echo '<li style="margin-bottom:1px;color:#DDD;"><a style="font-size:12px !important;" href="' . $suborder->get_view_order_url() . '" class="row-title"><strong>' . $order->get_id() . '-' . $counter . ' / <span style="color:#999">#' . esc_attr( $suborder_id ) . '</span></strong></a></li>';
					$counter ++;
				}
				echo '<ul>';
			}
		}

		/**
		 * Adds frontend suborders column
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $columns
		 *
		 * @return array
		 */
		public function add_frontend_order_columns( $columns ) {
			//return $columns;
			$new = array();
			foreach ( $columns as $key => $title ) {
				if ( $key == 'order-date' ) {
					$new[ $this->suborders_column_id ] = __( 'Suborders', 'multi-order-for-woocommerce' );
				}
				$new[ $key ] = $title;
			}
			return $new;
		}

		/**
		 * Adds admin suborders column
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $columns
		 *
		 * @return array
		 */
		public function add_admin_suborders_column( $columns ) {
			$new = array();
			foreach ( $columns as $key => $title ) {
				if ( $key == 'shipping_address' ) {
					$new[ $this->suborders_column_id ] = __( 'Sub Orders', 'multi-order-for-woocommerce' ) . ' <span style="display:none;color:#999;font-size:11px;">' . '(Sub Order ID / Order ID)' . '</span>';
				}
				$new[ $key ] = $title;
			}
			return $new;
		}

		/**
		 * Setups admin order columns
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $column
		 * @param $post_id
		 *
		 * @return mixed
		 */
		public function setup_admin_order_columns( $column, $post_id ) {
			if ( $column == 'order_title' ) {
				$suborder_fake_id = get_post_meta( $post_id, Alg_MOWC_Order_Metas::SUB_ORDER_FAKE_ID, true );
				if ( $suborder_fake_id ) {
					echo '<div style="margin-bottom:5px"><a href="' . admin_url( 'post.php?post=' . absint( $post_id ) . '&action=edit' ) . '" class="row-title"><strong>' . esc_attr( $suborder_fake_id ) . '</strong> (suborder)</a></div>';
				}
			}
			if ( $column == $this->suborders_column_id ) {
				$suborders = get_post_meta( $post_id, Alg_MOWC_Order_Metas::SUB_ORDERS );
				$counter   = 1;
				if ( is_array( $suborders ) && count( $suborders ) > 0 ) {
					echo '<ul style="margin:0;padding:0;list-style:none">';
					foreach ( $suborders as $suborder_id ) {
						echo '<li style="margin-bottom:1px;color:#DDD;"><a style="font-size:12px !important;" href="' . admin_url( 'post.php?post=' . absint( $suborder_id ) . '&action=edit' ) . '" class="row-title"><strong>' . $post_id . '-' . $counter . ' / <span style="color:#999">#' . esc_attr( $suborder_id ) . '</span></strong></a></li>';
						$counter ++;
					}
					echo '</ul>';
				}
			}
		}
	}
}