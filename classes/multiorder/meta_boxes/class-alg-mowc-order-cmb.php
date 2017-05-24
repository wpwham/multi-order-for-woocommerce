<?php
/**
 * Multi order for WooCommerce - Order custom meta box
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Order_CMB' ) ) {

	class Alg_MOWC_Order_CMB {
		public $cmb_id = 'alg_mowc_cmb';

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
		    // Adds metabox
			add_action( 'cmb2_admin_init', array( $this, 'add_cmb' ) );

			// Style for metabox
			$object = 'post';
			$cmb_id = $this->cmb_id;
			add_action( "cmb2_after_{$object}_form_{$cmb_id}", array($this,'add_custom_script'), 10, 2 );


		}

		/**
		 * Setups a austom style for this meta box
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add_custom_script($post_id, CMB2 $cmb){
			?>
			<style type="text/css" media="screen">
				#<?php echo $cmb->cmb_id;?> .cmb-td{
					text-align:center;
				}
				#<?php echo $cmb->cmb_id;?> .table-layout{
                    margin:0;
                }
			</style>
			<?php
		}

		/**
		 * Adds the Custom meta box
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add_cmb() {
			$cmb_demo = new_cmb2_box( array(
				'id'           => $this->cmb_id,
				'title'        => __( 'Multi order', 'multi-order-for-woocommerce' ),
				'object_types' => array( 'shop_order' ), // Post type
				'context'      => 'side',
			) );

			$cmb_demo->add_field( array(
				//'name'       => esc_html__( 'Test Text', 'cmb2' ),
				//'desc'       => esc_html__( 'field description (optional)', 'cmb2' ),
				'id'         => '_alg_mowc_cmb_create_suborders_btn',
				'type'       => 'text',
				'default'    => __( 'Create suborders', 'multi-order-for-woocommerce' ),
				'attributes' => array(
					'type'  => 'submit',
					'class' => 'button button-primary',
					'style' => 'text-align:center;',
					'name'  => 'alg_mpwc_cmb_create_suborders',
				),
			) );
		}
	}

}