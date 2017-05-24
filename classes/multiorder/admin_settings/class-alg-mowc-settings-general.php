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

		const OPTION_ENABLE_PLUGIN = 'alg_mowc_opt_enable';

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
					'title'    => __( 'Multi order options', 'multi-order-for-woocommerce' ),
					'type'     => 'title',
					'id'       => 'alg_mowc_opt',
				),
				array(
					'title'    => __( 'Enable Multi order', 'multi-order-for-woocommerce' ),
					'desc'     => sprintf( __( 'Enable <strong>"%s"</strong> plugin', 'multi-order-for-woocommerce' ), __( 'Multi order for WooCommerce' ) ),
					'id'       => self::OPTION_ENABLE_PLUGIN,
					'default'  => 'yes',
					'type'     => 'checkbox',
				),
				array(
					'type'     => 'sectionend',
					'id'       => 'alg_mowc_opt',
				),
			);

			return parent::get_settings( array_merge( $settings, $new_settings ) );
		}
	}
}