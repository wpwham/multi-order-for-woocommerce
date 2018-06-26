<?php
/**
 * Multi order for WooCommerce - Order columns
 *
 * @version 1.0.8
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Order_Columns' ) ) {

	class Alg_MOWC_Order_Columns {

		public $column_suborders_id = 'alg_mowc_suborders';
		public $column_order_total_id = 'alg_mowc_order-total';
		public $column_order_remaining_id = 'alg_mowc_order-remaining';
		//public $column_order_payment_status = 'alg_mowc_payment_status';

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			$post_type = 'shop_order';
			$column_order_remaining_id = $this->column_order_remaining_id;

			// Setups admin columns
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'setup_admin_order_columns' ), 20, 2 );
			add_filter( "manage_{$post_type}_posts_columns", array( $this, 'change_admin_columns' ), 20 );

			// Change Total column label to Remaining
			//add_filter( "manage_{$post_type}_posts_columns", array( $this, 'change_total_column_to_remaining_on_admin' ), 20 );

			// Setups frontend columns
			add_filter( "woocommerce_my_account_my_orders_columns", array( $this, 'change_frontend_order_columns' ) );
			add_filter( "woocommerce_account_orders_columns", array( $this, 'change_frontend_order_columns' ) );
			add_action( "woocommerce_my_account_my_orders_column_{$this->column_suborders_id}", array( $this, 'setup_frontend_suborders_column') );
			//add_action( "woocommerce_my_account_my_orders_column_{$this->column_order_total_id}", array( $this, 'setup_frontend_total_column') );
			//add_action( "woocommerce_my_account_my_orders_column_{$this->column_order_payment_status}", array( $this, 'setup_frontend_payment_column') );
			//add_action( "woocommerce_my_account_my_orders_column_order-total", array( $this, 'setup_frontend_remaining_column') );
			add_action( "woocommerce_my_account_my_orders_column_{$column_order_remaining_id}", array( $this, 'setup_frontend_remaining_column') );
			add_action( "woocommerce_my_account_my_orders_column_order-number", array( $this, 'setup_frontend_order_number_column' ) );
		}

		/**
         * Convert total column label to remaining on admin
         *
		 * @param $columns
         *
		 * @version 1.0.0
		 * @since   1.0.0
         *
		 * @return mixed
		 */
		/*public function change_total_column_to_remaining_on_admin( $columns ) {
			$columns['order_total'] = __( 'Remaining', 'multi-order-for-woocommerce' );
			return $columns;
		}*/

		/**
		 * Setups frontend order number column
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param WC_Order $order
		 */
		public function setup_frontend_order_number_column( WC_Order $order ) {
			$is_suborder  = filter_var( get_post_meta( $order->get_id(), Alg_MOWC_Order_Metas::IS_SUB_ORDER, true ), FILTER_VALIDATE_BOOLEAN );
			$suborder_str = $is_suborder ? __( '(Suborder)', 'multi-order-for-woocommerce' ) : '';
			if ( ! $is_suborder ) {
				?>
                <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
					<?php echo _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number() . ' ' . $suborder_str; ?>
                </a>
				<?php
			}
		}

		/**
		 * Setups frontend payment status column
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param WC_Order $order
		 */
		/*public function setup_frontend_payment_column( WC_Order $order ) {
			echo $this->html_payment_status_column( $order->get_id() );
		}*/

		/**
		 * Setups frontend total column (new total column)
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param WC_Order $order
		 */
		public function setup_frontend_total_column( WC_Order $order ) {
			$order_total = $order->get_subtotal() + WC_Tax::round( WC_Tax::round( $order->get_total_tax() ) + WC_Tax::round( $order->get_discount_tax() ) );
			echo wc_price($order_total);
        }

		/**
		 * Setups frontend remaining column (old total column)
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param WC_Order $order
		 */
		/*public function setup_frontend_remaining_column( WC_Order $order ) {
		    echo $order->get_formatted_order_total();
		}*/

		public function setup_frontend_remaining_column( WC_Order $order ) {
			echo $this->display_remaining_column($order->get_id());
		}

		private function display_remaining_column( $post_id ) {
			$is_suborder   = filter_var( get_post_meta( $post_id, Alg_MOWC_Order_Metas::IS_SUB_ORDER, true ), FILTER_VALIDATE_BOOLEAN );
			$order         = wc_get_order( $post_id );
			$remaining     = $order->get_total();
			$deduct_status = get_option( Alg_MOWC_Settings_General::OPTION_SUBORDERS_SUBTRACTION_STATUS, true );
			if ( $is_suborder ) {
				if ( in_array( 'wc-' . $order->get_status(), $deduct_status ) ) {
					$remaining = 0;
				}
			} else {
				$suborders = get_post_meta( $post_id, Alg_MOWC_Order_Metas::SUB_ORDERS );
				foreach ( $suborders as $suborder_id ) {
					$suborder = wc_get_order( $suborder_id );
					if ( in_array( 'wc-' . $suborder->get_status(), $deduct_status ) ) {
						$remaining -= $suborder->get_total();
					}
				}
			}

			return wc_price( $remaining );
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
			$is_suborder = filter_var( get_post_meta( $order->get_id(), Alg_MOWC_Order_Metas::IS_SUB_ORDER, true ), FILTER_VALIDATE_BOOLEAN );
			if ( $is_suborder ) {
				echo '<a href="' . $order->get_view_order_url() . '">#' . $order->get_order_number() . '</a>';
			}
		}

		/**
		 * Changes frontend suborders column
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $columns
		 *
		 * @return array
		 */
		public function change_frontend_order_columns( $columns ) {
			$new = array();
			foreach ( $columns as $key => $title ) {
				if ( $key == 'order-date' ) {
					$show_suborders = filter_var( get_option( Alg_MOWC_Settings_General::OPTION_SUBORDERS_FRONTEND_SHOW ), FILTER_VALIDATE_BOOLEAN );
					if ( $show_suborders ) {
						$new[ $this->column_suborders_id ] = __( 'Suborder', 'multi-order-for-woocommerce' );
					}
					$show_remaining = filter_var( get_option( Alg_MOWC_Settings_General::OPTION_COLUMN_REMAINING_FRONTEND ), FILTER_VALIDATE_BOOLEAN );
					if ( $show_remaining ) {
						$new[ $this->column_order_remaining_id ] = __( 'Remaining', 'multi-order-for-woocommerce' );
					}
				}
				$new[ $key ] = $title;

				/*if ( $key == 'order-status' ) {
					//$new[ $this->column_order_total_id ]       = __( 'Total', 'woocommerce' );
					$new[ $this->column_order_payment_status ] = __( 'Payment', 'woocommerce' );
				}*/
				$new[ $key ] = $title;
			}

			//$new['order-total'] = __( 'Remaining', 'multi-order-for-woocommerce' );
			return $new;
		}

		/**
		 * Changes admin columns
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $columns
		 *
		 * @return array
		 */
		public function change_admin_columns( $columns ) {
			$new = array();
			foreach ( $columns as $key => $title ) {
				if ( $key == 'shipping_address' ) {
					$new[ $this->column_suborders_id ]       = __( 'Sub Orders', 'multi-order-for-woocommerce' ) . ' <span style="display:none;color:#999;font-size:11px;">' . '(Sub Order ID / Order ID)' . '</span>';

					$show_remaining = filter_var( get_option( Alg_MOWC_Settings_General::OPTION_COLUMN_REMAINING_ADMIN ), FILTER_VALIDATE_BOOLEAN );
					if ( $show_remaining ) {
						$new[ $this->column_order_remaining_id ] = __( 'Remaining', 'multi-order-for-woocommerce' );
					}
				}
				$new[ $key ] = $title;

				/*if ( $key == 'order_date' ) {
					$new[ $this->column_order_payment_status ] = __( 'Payment', 'multi-order-for-woocommerce' );
				}*/
				$new[ $key ] = $title;
			}
			return $new;
		}

		/**
		 * Displays payment status column
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $order_id
		 *
		 * @return string
		 */
		/*public function html_payment_status_column( $order_id ) {
			$payment_status_tax = new Alg_MOWC_Order_Payment_Status();
			return implode( ",", wp_get_post_terms( $order_id, $payment_status_tax->id, array( "fields" => "names" ) ) );
		}*/

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

		    switch($column){
                case 'order_title':
	                $is_suborder  = filter_var( get_post_meta( $post_id, Alg_MOWC_Order_Metas::IS_SUB_ORDER, true ), FILTER_VALIDATE_BOOLEAN );
	                $suborder_str = $is_suborder ? '<strong>'.__( '(Suborder)', 'multi-order-for-woocommerce' ) .'</strong>' : '';
	                echo $suborder_str;
                break;

			    /*case $this->column_order_total_id:
				    $order       = wc_get_order( $post_id );
				    $order_total = $order->get_subtotal() + WC_Tax::round( WC_Tax::round( $order->get_total_tax() ) + WC_Tax::round( $order->get_discount_tax() ) );
				    echo wc_price( $order_total );
                break;*/

			    case $this->column_order_remaining_id:
				    echo $this->display_remaining_column($post_id);
                break;

			    /*case $this->column_order_payment_status:
				    echo $this->html_payment_status_column($post_id);
			    break;*/

                case $this->column_suborders_id:
	                $suborders = get_post_meta( $post_id, Alg_MOWC_Order_Metas::SUB_ORDERS );
	                $counter   = 1;
	                if ( is_array( $suborders ) && count( $suborders ) > 0 ) {
		                echo '<ul style="margin:0;padding:0;list-style:none">';
		                foreach ( $suborders as $suborder_id ) {
			                $suborder = wc_get_order($suborder_id);
			                if($suborder){
				                echo '<li style="margin-bottom:1px;color:#DDD;"><a style="font-size:12px !important;" href="' . admin_url( 'post.php?post=' . absint( $suborder_id ) . '&action=edit' ) . '" class="row-title"><strong>#' . $suborder->get_order_number() . '</strong></a></li>';
				                $counter ++;
			                }

		                }
		                echo '</ul>';
	                }
                break;

		    }

		}
	}
}