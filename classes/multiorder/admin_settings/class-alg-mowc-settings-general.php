<?php
/**
 * Multi order for WooCommerce - General section
 *
 * @version 1.4.4
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Alg_MOWC_Settings_General' ) ) {

	class Alg_MOWC_Settings_General extends Alg_MOWC_Settings_Section {

		const OPTION_ENABLE_PLUGIN                      = 'alg_mowc_opt_enable';
		//const OPTION_DISABLE_CANCEL_BUTTON              = 'alg_mowc_disable_cancel_btn';
		//const OPTION_DISABLE_ORDER_ITEM_QTY             = 'alg_mowc_disable_order_item_qty';
		const OPTION_SUBORDERS_ADMIN_SHOW               = 'alg_mowc_suborders_admin_show';
		const OPTION_SUBORDERS_FRONTEND_SHOW            = 'alg_mowc_suborders_frontend_show';
		const OPTION_SUBORDERS_SUBTRACTION_STATUS       = 'alg_mowc_suborders_subtraction_status';
		const OPTION_SUBORDERS_UNDEDUCT_STATUS          = 'alg_mowc_suborders_undeduct_status';
		const OPTION_SUBORDERS_COPY_MAIN_ORDER_STATUS   = 'alg_mowc_suborders_cmos';
		const OPTION_SUBORDERS_EXCEPTION_COPY_STATUS    = 'alg_mowc_suborders_ecs';
		const OPTION_SUBORDERS_CREATE_AUTOMATICALLY     = 'alg_mowc_suborders_autocreate';
		//const OPTION_DEFAULT_PAYMENT_STATUS             = 'alg_mowc_default_payment_status';
		const OPTION_DEFAULT_MAIN_ORDER_STATUS          = 'alg_mowc_default_main_order_status';
		const OPTION_DEFAULT_SUB_ORDER_STATUS           = 'alg_mowc_default_sub_order_status';
		const OPTION_PAY_BUTTON_LABEL                   = 'alg_mowc_pay_button_label';
		const OPTION_COLUMN_REMAINING_ADMIN             = 'alg_mowc_col_remaining_admin';
		const OPTION_COLUMN_REMAINING_FRONTEND          = 'alg_mowc_col_remaining_front';
		const OPTION_SUBORDERS_BY_QUANTITY              = 'alg_mowc_suborders_by_qty';
		const OPTION_EMAILS_SEND_MAIN_ORDER             = 'alg_mowc_emails_send_main_order';
		const OPTION_EMAILS_SEND_SUB_ORDER              = 'alg_mowc_emails_send_sub_order';
		const OPTION_SUBORDERS_CHANGE_MAIN_ORDER_STATUS = 'alg_mowc_suborders_set_morder_status';


		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct( $handle_autoload = true ) {
			$this->id   = '';
			$this->desc = __( 'General', 'multi-order-for-woocommerce' );
			parent::__construct( $handle_autoload );
		}

		/**
		 * Gets payment status
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @return array
		 */
		function get_payment_status_terms() {
			$payment_status = new Alg_MOWC_Order_Payment_Status();
			if ( taxonomy_exists( $payment_status->id ) ) {
				$terms = get_terms( array(
					'taxonomy'   => $payment_status->id,
					'hide_empty' => false,
				) );

				return wp_list_pluck(
					get_terms( array(
						'taxonomy'   => $payment_status->id,
						'hide_empty' => false,
					) ),
					'name',
					'slug'
				);
			} else {
				return array();
			}
		}

		/**
		 * Get default main order status
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		/*public function get_default_main_order_status(){
			$status = wc_get_order_statuses();
			return array( '' => __( 'None', 'multi-order-for-woocommerce' ) ) + $status;
		}*/

		/**
		 * get_settings.
		 *
		 * @version 1.4.4
		 * @since   1.0.0
		 */
		function get_settings( $settings = null ) {
			$payment_status_tax = new Alg_MOWC_Order_Payment_Status();

			$new_settings = array(
				array(
					'title' => __( 'Multi Order options', 'multi-order-for-woocommerce' ),
					'type'  => 'title',
					'desc'  => __( 'Multi order general options', 'multi-order-for-woocommerce' ),
					'id'    => 'alg_mowc_opt',
				),
				array(
					'title'   => __( 'Enable Multi Order', 'multi-order-for-woocommerce' ),
					'desc'    => sprintf( __( 'Enables <strong>"%s"</strong> plugin', 'multi-order-for-woocommerce' ), __( 'Multi order for WooCommerce' ) ),
					'desc_tip' => '<a href="https://wpwham.com/documentation/multi-order-for-woocommerce/?utm_source=documentation_link&utm_campaign=free&utm_medium=multi_order" target="_blank" class="button">' .
					__( 'Documentation', 'multi-order-for-woocommerce' ) . '</a>',
					'id'      => self::OPTION_ENABLE_PLUGIN,
					'default' => 'yes',
					'type'    => 'checkbox',
				),
				/*array(
					'title'   => __( 'Disable cancel button', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Disables the cancel button on frontend ', 'multi-order-for-woocommerce' ). ' <strong>' . __( '(My Account > orders)', 'multi-order-for-woocommerce' ) . '</strong>',
					'id'      => self::OPTION_DISABLE_CANCEL_BUTTON,
					'default' => 'no',
					'type'    => 'checkbox',
				),*/
				/*array(
					'title'   => __( 'Hide quantity', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Hides order item quantity on some places ', 'multi-order-for-woocommerce' ),
					'desc_tip'=> __( 'E.g order received page / order pay page / Emails ', 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_DISABLE_ORDER_ITEM_QTY,
					'default' => 'no',
					'type'    => 'checkbox',
				),*/
				/*array(
					'title'   => __( 'Pay button label', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Pay button label for the main order', 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_PAY_BUTTON_LABEL,
					'default' => __( 'Collectively Pay', 'multi-order-for-woocommerce' ),
					'type'    => 'text',
				),*/
				/*array(
					'title'   => __( 'Default payment status', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Default payment status', 'multi-order-for-woocommerce' ),
					'desc_tip'=> __( 'New orders will have this default payment status', 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_DEFAULT_PAYMENT_STATUS,
					'default' => 'unpaid',
					'type'    => 'select',
					'class'   => 'chosen_select',
					'options'=> $this->get_payment_status_terms()
				),*/
				array(
					'title'   => __( 'Default main order status', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'New main orders will have this default status', 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_DEFAULT_MAIN_ORDER_STATUS,
					'default' => '',
					'type'    => 'select',
					'class'   => 'chosen_select',
					'options' => array( '' => __( 'Set by payment gateway', 'multi-order-for-woocommerce' ) ) + wc_get_order_statuses(),
				),
				/*array(
					'title'   => __( 'Remaining on Admin', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Displays a "Remaining" column on admin that shows how much is still necessary to pay', 'multi-order-for-woocommerce' ),
					'desc_tip'=> __( '(WooCommerce > orders)', 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_COLUMN_REMAINING_ADMIN,
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Remaining on Frontend', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Displays a "Remaining" column on frontend that shows how much is still necessary to pay', 'multi-order-for-woocommerce' ),
					'desc_tip'=> __( '(My Account > orders)', 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_COLUMN_REMAINING_FRONTEND,
					'default' => 'no',
					'type'    => 'checkbox',
				),*/
				array(
					'type' => 'sectionend',
					'id'   => 'alg_mowc_opt',
				),

				// Email section
				array(
					'title' => __( 'Email options', 'multi-order-for-woocommerce' ),
					'desc'  => __( "Options regarding emails", 'multi-order-for-woocommerce' ),
					//'desc'  => __( "Options regarding emails <strong>on the moment order is placed</strong> only.", 'multi-order-for-woocommerce' ).'<br />'.__( "After that, emails will be sent normally.", 'multi-order-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'alg_mowc_emails_opt',
				),
				array(
					'title'   => __( 'Send to main order', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Send email to main order', 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_EMAILS_SEND_MAIN_ORDER,
					'default' => 'yes',
					'type'    => 'checkbox',
				),
				/*array(
					'title'   => __( 'Send to suborders', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Send email to each suborder', 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_EMAILS_SEND_SUB_ORDER,
					'default' => 'no',
					'type'    => 'checkbox',
				),*/
				array(
					'type' => 'sectionend',
					'id'   => 'alg_mowc_emails_opt',
				),

				// Suborders section
				array(
					'title' => __( 'Sub Order options', 'multi-order-for-woocommerce' ),
					'desc'  => __( 'Options regarding Sub Orders', 'multi-order-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'alg_mowc_suborders_opt',
				),
				/*array(
					'title'   => __( 'Consider quantity', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Take order item quantity into consideration', 'multi-order-for-woocommerce' ),
					'desc_tip'=> __( "Note: After the order is placed, it will lose sync between main-order and sub-orders, meaning that if price or quantity changes, associated orders won't change automatically", 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_SUBORDERS_BY_QUANTITY,
					'default' => 'no',
					'type'    => 'checkbox',
				),*/
				/*array(
					'title'   => __( 'Change main order status', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'When all suborders have the same status, the main order is set to this status', 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_SUBORDERS_CHANGE_MAIN_ORDER_STATUS,
					'default' => 'no',
					'type'    => 'checkbox',
				),*/
				array(
					'title'   => __( 'Default suborder status', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'New suborders will have this default status', 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_DEFAULT_SUB_ORDER_STATUS,
					'default' => '',
					'type'    => 'select',
					'class'   => 'chosen_select',
					'options' => array(
						''           => __( 'Set by payment gateway', 'multi-order-for-woocommerce' ),
						'main_order' => __( 'Same as Main order', 'multi-order-for-woocommerce' )
		             ) + wc_get_order_statuses(),
				),
				array(
					'title'   => __( 'Automatic creation', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Creates suborders automatically when new orders are created', 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_SUBORDERS_CREATE_AUTOMATICALLY,
					'type'    => 'checkbox',
					'default' => 'yes'
				),
				array(
					'title'   => __( 'Show on admin', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Displays suborders as table rows on admin', 'multi-order-for-woocommerce' ) . ' <strong>' . __( '(WooCommerce > orders)', 'multi-order-for-woocommerce' ) . '</strong>',
					'id'      => self::OPTION_SUBORDERS_ADMIN_SHOW,
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Show on frontend', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Displays suborders as table rows on frontend', 'multi-order-for-woocommerce' ) . ' <strong>' . __( '(My Account > orders)', 'multi-order-for-woocommerce' ) . '</strong>',
					'id'      => self::OPTION_SUBORDERS_FRONTEND_SHOW,
					'default' => 'no',
					'type'    => 'checkbox',
				),
				/*array(
					'title'   => __( 'Deduct status', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Status that will make suborders values be deducted from main order.', 'multi-order-for-woocommerce' ),
					//'desc'    => __( 'Status that will make suborders values be deducted from main order.', 'multi-order-for-woocommerce' ).'<br />'.sprintf(__( '<strong>NOTE:</strong> It will override other <a href="%s">payment status</a>', 'multi-order-for-woocommerce' ),admin_url("edit-tags.php?taxonomy={$payment_status_tax->id}")),
					'id'      => self::OPTION_SUBORDERS_SUBTRACTION_STATUS,
					'type'    => 'multiselect',
					'class'   => 'chosen_select',
					'options' => wc_get_order_statuses(),
					'default' => array()
				),
				array(
					'title'   => __( 'Undeduct status', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Status that will make suborders values be undeducted from main order. I.e Back to their original value.', 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_SUBORDERS_UNDEDUCT_STATUS,
					'type'    => 'multiselect',
					'class'   => 'chosen_select',
					'options' => wc_get_order_statuses(),
					'default' => array()
				),
				array(
					'title'   => __( 'Copy main order status', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Suborders get the same status of main order when it changes', 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_SUBORDERS_COPY_MAIN_ORDER_STATUS,
					'type'    => 'multiselect',
					'class'   => 'chosen_select',
					'options' => array(
						'frontend' => __( 'If customer takes an action' ),
						'admin'    => __( 'If admin takes an action' ),
					),
					'default' => array(),
				),
				array(
					'title'   => __( 'Suborders status exception', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Suborders with these payment status will not be changed by <strong>Copy main order status</strong> option', 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_SUBORDERS_EXCEPTION_COPY_STATUS,
					'type'    => 'multiselect',
					'class'   => 'chosen_select',
					'options' => $this->get_payment_status_terms(),
					'default' => array(),
				),*/
				array(
					'type' => 'sectionend',
					'id'   => 'alg_mowc_suborders_opt',
				),
			);

			return parent::get_settings( array_merge( $settings, $new_settings ) );
		}
	}
}