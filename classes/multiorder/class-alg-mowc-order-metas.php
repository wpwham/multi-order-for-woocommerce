<?php
/**
 * Multi order for WooCommerce - Core Class
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Order_Metas' ) ) {

	class Alg_MOWC_Order_Metas {
		const SUB_ORDERS        = '_alg_mowc_suborder';
		const SUB_ORDER_FAKE_ID = '_alg_mowc_suborder_fake_id';
		const IS_SUB_ORDER      = '_alg_mowc_is_suborder';
		const PARENT_ORDER      = '_alg_mowc_parentorder';
	}
}