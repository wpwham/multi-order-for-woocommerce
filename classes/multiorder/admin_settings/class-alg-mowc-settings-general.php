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

		const OPTION_ENABLE_PLUGIN                             = 'alg_mowc_opt_enable';
		const OPTION_DISABLE_CANCEL_BUTTON                     = 'alg_mowc_disable_cancel_btn';
		const OPTION_DISABLE_ORDER_ITEM_QTY                    = 'alg_mowc_disable_order_item_qty';
		const OPTION_SUBORDERS_ADMIN_SHOW                      = 'alg_mowc_suborders_admin_show';
		const OPTION_SUBORDERS_FRONTEND_SHOW                   = 'alg_mowc_suborders_frontend_show';
		const OPTION_SUBORDERS_SUBTRACTION_STATUS              = 'alg_mowc_suborders_subtraction_status';
		const OPTION_SUBORDERS_COPY_MAIN_ORDER_STATUS          = 'alg_mowc_suborders_cmos';
		const OPTION_SUBORDERS_CREATE_AUTOMATICALLY            = 'alg_mowc_suborders_autocreate';

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
					'id'      => self::OPTION_ENABLE_PLUGIN,
					'default' => 'yes',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Disable cancel button', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Disables the cancel button on frontend on ', 'multi-order-for-woocommerce' ). ' <strong>' . __( '(My Account > orders)', 'multi-order-for-woocommerce' ) . '</strong>',
					'id'      => self::OPTION_DISABLE_CANCEL_BUTTON,
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Disable quantity', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Disables order item quantity on order receiv', 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_DISABLE_CANCEL_BUTTON,
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'alg_mowc_opt',
				),
				array(
					'title' => __( 'Sub Order options', 'multi-order-for-woocommerce' ),
					'desc'  => __( 'Options regarding Sub Orders', 'multi-order-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'alg_mowc_suborders_opt',
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
					'title'   => __( 'Subtraction status', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Status that will make suborders values be deducted from main order', 'multi-order-for-woocommerce' ),
					'id'      => self::OPTION_SUBORDERS_SUBTRACTION_STATUS,
					'type'    => 'multiselect',
					'class'   => 'chosen_select',
					'options' => wc_get_order_statuses(),
					'default' => array('wc-cancelled','wc-processing')
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
					'default' => array( 'frontend', 'admin' ),
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