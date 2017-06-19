<?php
/**
 * Multi order for WooCommerce - General section
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Alg_MOWC_Settings_General' ) ) {

	class Alg_MOWC_Settings_General extends Alg_MOWC_Settings_Section {

		const OPTION_ENABLE_PLUGIN                    = 'alg_mowc_opt_enable';
		const OPTION_DISABLE_CANCEL_BUTTON            = 'alg_mowc_disable_cancel_btn';
		const OPTION_DISABLE_ORDER_ITEM_QTY           = 'alg_mowc_disable_order_item_qty';
		const OPTION_SUBORDERS_ADMIN_SHOW             = 'alg_mowc_suborders_admin_show';
		const OPTION_SUBORDERS_FRONTEND_SHOW          = 'alg_mowc_suborders_frontend_show';
		const OPTION_SUBORDERS_SUBTRACTION_STATUS     = 'alg_mowc_suborders_subtraction_status';
		const OPTION_SUBORDERS_UNDEDUCT_STATUS        = 'alg_mowc_suborders_undeduct_status';
		const OPTION_SUBORDERS_COPY_MAIN_ORDER_STATUS = 'alg_mowc_suborders_cmos';
		const OPTION_SUBORDERS_EXCEPTION_COPY_STATUS  = 'alg_mowc_suborders_ecs';
		const OPTION_SUBORDERS_CREATE_AUTOMATICALLY   = 'alg_mowc_suborders_autocreate';
		const OPTION_DEFAULT_PAYMENT_STATUS           = 'alg_mowc_default_payment_status';
		const OPTION_DEFAULT_MAIN_ORDER_STATUS        = 'alg_mowc_default_main_order_status';
		const OPTION_DEFAULT_SUB_ORDER_STATUS         = 'alg_mowc_default_sub_order_status';
		const OPTION_PAY_BUTTON_LABEL                 = 'alg_mowc_pay_button_label';
		const OPTION_METABOX_PRO                      = 'alg_mowc_cmb_pro';

		protected $pro_version_url = 'https://wpcodefactory.com/item/multi-order-for-woocommerce/';

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
		 * get_settings.
		 *
		 * @version 1.0.0
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
					'title'          => 'Pro version',
					'enabled'         => !function_exists( 'alg_mowc_pro_plugin' ),
					'type'           => 'wccso_metabox',
					'show_in_pro'    => false,
					'accordion' => array(
						'title' => __( 'Take a look on some of its features:', 'multi-order-for-woocommerce' ),
						'items' => array(
							array(
								'trigger'     => __( 'Sync your orders', 'multi-order-for-woocommerce' ),
								'description' => __( 'Whenever you change a suborder item price or tax, the correspondent item on main order gets updated, and vice-versa.', 'multi-order-for-woocommerce' ),
								//'img_src'     => plugins_url( '../../assets/images/icons.gif', __FILE__ ),
							),
							array(
								'trigger'     => __( 'Display more intuitive numbers to your suborders', 'multi-order-for-woocommerce' ),
								'description' => __( 'E.g If your main order is 100, your suborders numbers will be 100-1, 100-2, and so on.', 'multi-order-for-woocommerce' ),
								//'img_src'     => plugins_url( '../../assets/images/icons.gif', __FILE__ ),
							),
							array(
								'trigger'     => __( 'Deduct suborders from main orders', 'multi-order-for-woocommerce' ),
								'description' => __( 'If you get your suborder paid, subtract its value from main order automatically.', 'multi-order-for-woocommerce' ).' '.__('You also have the option to undo this depending on the suborder status.','multi-order-for-woocommerce'),
								//'img_src'     => plugins_url( '../../assets/images/icons.gif', __FILE__ ),
							),
							array(
								'trigger'     => __( 'Present your orders with custom payment status', 'multi-order-for-woocommerce' ),
								'description' => __( 'Besides your orders status, use at least 3 more order payment status (paid, unpaid, partial) to make it more intuive to your customers.', 'multi-order-for-woocommerce' ),
								//'img_src'     => plugins_url( '../../assets/images/icons.gif', __FILE__ ),
							),
							array(
								'trigger' => __( 'Support', 'multi-order-for-woocommerce' ),
							),
						),
					),
					'call_to_action' => array(
						'href'   => $this->pro_version_url,
						'label'  => 'Upgrade to Pro version now',
					),
					'description'    => __( 'Do you like the free version of this plugin? Imagine what the Pro version can do for you!', 'multi-order-for-woocommerce' ) . '<br />' . sprintf( __( 'Check it out <a target="_blank" href="%1$s">here</a> or on this link: <a target="_blank" href="%1$s">%1$s</a>', 'multi-order-for-woocommerce' ), esc_url( $this->pro_version_url ) ),
					'id'             => self::OPTION_METABOX_PRO,
				),
				array(
					'title'   => __( 'Enable Multi Order', 'multi-order-for-woocommerce' ),
					'desc'    => sprintf( __( 'Enables <strong>"%s"</strong> plugin', 'multi-order-for-woocommerce' ), __( 'Multi order for WooCommerce' ) ),
					'id'      => self::OPTION_ENABLE_PLUGIN,
					'default' => 'yes',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Default main order status', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'New main orders will have this default status', 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_DEFAULT_MAIN_ORDER_STATUS,
					'default' => '',
					'type'    => 'select',
					'class'   => 'chosen_select',
					'options' => array( '' => __( 'Set by payment gateway', 'multi-order-for-woocommerce' ) ) + wc_get_order_statuses(),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'alg_mowc_opt',
				),

				// Suborders section
				array(
					'title' => __( 'Sub Order options', 'multi-order-for-woocommerce' ),
					'desc'  => __( 'Options regarding Sub Orders', 'multi-order-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'alg_mowc_suborders_opt',
				),
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
				array(
					'type' => 'sectionend',
					'id'   => 'alg_mowc_suborders_opt',
				),
			);

			return parent::get_settings( array_merge( $settings, $new_settings ) );
		}
	}
}