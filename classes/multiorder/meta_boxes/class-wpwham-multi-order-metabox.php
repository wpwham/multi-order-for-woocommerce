<?php
/**
 * Multi order for WooCommerce - Order custom meta box
 *
 * @version 1.0.8
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'WPWham_Multi_Order_Meta_Box' ) ) {

	class WPWham_Multi_Order_Meta_Box {
		public $meta_box_id = 'wpwham_multi_order_meta_box';

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			// Adds metabox
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		}

		/**
		 * Hides multi order metabox on single item orders
		 *
		 * @version 1.0.4
		 * @since   1.0.3
		 */
		public function hide_on_single_item_orders( $order ) {
			if ( ! $order || 'shop_order' !== $order->get_type() ) {
				return false;
			}
			if ( $order->get_meta( Alg_MOWC_Order_Metas::IS_SUB_ORDER ) ) {
				return true;
			}
			if ( $order->get_meta( Alg_MOWC_Order_Metas::SUB_ORDERS ) ) {
				return true;
			}

			$items = $order->get_items();
			if ( count( $items ) < 2 ) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Adds the Custom meta box
		 *
		 * @version 1.0.3
		 * @since   1.0.0
		 */
		public function add_meta_boxes() {
			$order_id = get_the_ID() ? get_the_ID() : ( isset( $_GET['id'] ) ? $_GET['id'] : 0 );
			if ( ! $this->hide_on_single_item_orders( wc_get_order( $order_id ) ) ) {
				return;
			}

			$screens = array( 'shop_order' );
			if ( function_exists( 'wc_get_page_screen_id' ) ) {
				$hpos_screen = wc_get_page_screen_id( 'shop-order' );
				if ( $hpos_screen && ! in_array( $hpos_screen, $screens, true ) ) {
					$screens[] = $hpos_screen;
				}
			}
			foreach ( $screens as $screen ) {
				add_meta_box(
					$this->meta_box_id,
					__( 'Multi order', 'multi-order-for-woocommerce' ),
					array( $this, 'render_meta_box' ),
					$screen,
					'side'
				);
			}
		}

		public function render_meta_box( $post_or_order ) {
			if ( is_a( $post_or_order, 'WC_Order' ) ) {
				$order = $post_or_order;
			} else {
				$order = wc_get_order( $post_or_order->ID );
			}

			if ( $this->config_create_suborders_btn_display( $order ) ) {
				echo '<button type="submit" class="button button-primary" name="wpwham_multi_order_create_suborders" value="1">';
				echo __( 'Create suborders', 'multi-order-for-woocommerce' );
				echo '</button>';
			}

			$this->render_field_as_list( $order );
		}

		/*
		 * Setups the create suborders button display
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function config_create_suborders_btn_display( $order ) {
			if ( $order->get_meta( Alg_MOWC_Order_Metas::IS_SUB_ORDER ) ) {
				return false;
			}
			if ( $order->get_meta( Alg_MOWC_Order_Metas::SUB_ORDERS ) ) {
				return false;
			}
			return true;
		}

		/**
		 * Renders field as list
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param WC_Order $order
		 */
		public function render_field_as_list( $order ) {
			$is_suborder = filter_var( $order->get_meta( Alg_MOWC_Order_Metas::IS_SUB_ORDER ), FILTER_VALIDATE_BOOLEAN );
			if ( $is_suborder ) {
				$label  = __( 'Parent order', 'multi-order-for-woocommerce' );
				$orders = array( $order->get_meta( Alg_MOWC_Order_Metas::PARENT_ORDER ) );
			} else {
				$label  = __( 'Sub Orders', 'multi-order-for-woocommerce' );
				$orders = wp_list_pluck( $order->get_meta( Alg_MOWC_Order_Metas::SUB_ORDERS, false ), 'value' );
			}
			?>

			<?php if ( count( array_filter( $orders ) ) > 0 ) { ?>
                <div class="alg-mowc-suborders">
                    <p><label><?php echo $label; ?></label></p>
                    <ul style="list-style:inside;">

						<?php foreach ( $orders as $order_id ): ?>
                            <?php $order = wc_get_order( $order_id ); ?>
                            <?php if ( $order ) { ?>
                            <li>
                                <a href="<?php echo $order->get_edit_order_url(); ?>">#<?php echo $order->get_order_number(); ?></a>
                            </li>
                            <?php } ?>
						<?php endforeach; ?>

                    </ul>
                </div>
			<?php } ?>
			<?php
		}
	}

}
