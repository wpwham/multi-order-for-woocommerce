<?php
/**
 * Multi order for WooCommerce - WooCommerce admin settings
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Settings_Page' ) ) {

	class Alg_MOWC_Settings_Page extends WC_Settings_Page {
		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			$this->id    = 'alg_mowc';
			$this->label = __( 'Multi order', 'multi-order-for-woocommerce' );
			parent::__construct();
		}

		/**
		 * get_settings.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function get_settings() {
			global $current_section;
			return apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() );
		}

	}

}