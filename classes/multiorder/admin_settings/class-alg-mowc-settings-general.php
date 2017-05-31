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

		const OPTION_ENABLE_PLUGIN        = 'alg_mowc_opt_enable';
		const OPTION_SUBORDERS_ADMIN_SHOW = 'alg_mowc_suborders_admin_show';
		const OPTION_SUBORDERS_FRONTEND_SHOW = 'alg_mowc_suborders_frontend_show';

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
					'title'   => __( 'Show on admin', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Displays suborders as table rows on admin', 'multi-order-for-woocommerce' ).' <strong>'.__( '(WooCommerce > orders)', 'multi-order-for-woocommerce' ).'</strong>',
					'id'      => self::OPTION_SUBORDERS_ADMIN_SHOW,
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Show on frontend', 'multi-order-for-woocommerce' ),
					'desc'    => __( 'Displays suborders as table rows on frontend', 'multi-order-for-woocommerce' ).' <strong>'.__( '(My Account > orders)', 'multi-order-for-woocommerce' ).'</strong>',
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