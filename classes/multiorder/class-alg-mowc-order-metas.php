<?php
/**
 * Multi order for WooCommerce - Order metas
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Order_Metas' ) ) {

	class Alg_MOWC_Order_Metas {
		const SUB_ORDERS           = '_alg_mowc_suborder';
		const SUB_ORDER_FAKE_ID    = '_alg_mowc_suborder_fake_id';
		const DELETING             = '_alg_mowc_deleting';
		const SUB_ORDER_SUB_ID     = '_alg_mowc_suborder_sub_id';
		const IS_SUB_ORDER         = '_alg_mowc_is_suborder';
		const PARENT_ORDER         = '_alg_mowc_parentorder';
		const PARENT_ORDER_ITEM    = '_alg_mowc_parentorderitem';
		const DEDUCTED             = '_alg_mowc_suborder_deducted';
		const LAST_SUBORDER_SUB_ID = '_alg_mowc_last_suborder_sub_id';
		const SORT_ID              = '_alg_mowc_sort_id';
	}
}