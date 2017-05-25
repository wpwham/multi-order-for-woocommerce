<?php
/**
 * Multi order for WooCommerce - Order custom meta box
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Multiorder_CMB' ) ) {

	class Alg_MOWC_Multiorder_CMB {
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
			add_action( "cmb2_after_{$object}_form_{$cmb_id}", array( $this, 'add_custom_script' ), 10, 2 );
		}

		/**
		 * Setups a austom style for this meta box
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add_custom_script( $post_id, CMB2 $cmb ) {
			?>
            <style type="text/css" media="screen">
                #
                <?php echo $cmb->cmb_id;?>
                .cmb-td {
                    text-align: center;
                }

                #
                <?php echo $cmb->cmb_id;?>
                .table-layout {
                    margin: 10px 0 0;
                }

                #
                <?php echo $cmb->cmb_id;?>
                .cmb-row {
                    /*padding:20px 0 27px !important;*/
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
				'save_field' => false,
				'default'    => __( 'Create suborders', 'multi-order-for-woocommerce' ),
				'show_on_cb' => array( $this, 'config_create_suborders_btn_display' ),
				'attributes' => array(
					'type'  => 'submit',
					'class' => 'button button-primary',
					'style' => 'text-align:center;',
					'name'  => 'alg_mpwc_cmb_create_suborders',
				),
			) );

			$cmb_demo->add_field( array(
				'name'          => __( 'Sub orders', 'multi-order-for-woocommerce' ),
				//'desc'       => esc_html__( 'field description (optional)', 'cmb2' ),
				'id'            => Alg_MOWC_Order_Metas::SUB_ORDERS,
				'type'          => 'text',
				'save_field'    => false,
				'render_row_cb' => array( $this, 'render_field_as_list' ),

			) );

			$cmb_demo->add_field( array(
				'name'          => __( 'Parent order', 'multi-order-for-woocommerce' ),
				//'desc'       => esc_html__( 'field description (optional)', 'cmb2' ),
				'id'            => Alg_MOWC_Order_Metas::PARENT_ORDER,
				'save_field'    => false,
				'type'          => 'text',
				'render_row_cb' => array( $this, 'render_field_as_list' ),
			) );
		}

		/*
		 * Setups the create suborders button display
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function config_create_suborders_btn_display( CMB2_Field $field ) {
			return ! get_post_meta( $field->object_id(), Alg_MOWC_Order_Metas::IS_SUB_ORDER, true );
		}

		/**
		 * Renders field as list
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param            $field_args
		 * @param CMB2_Field $field
		 */
		public function render_field_as_list( $field_args, CMB2_Field $field ) {
			$id              = $field->args( 'id' );
			$label           = $field->args( 'name' );
			$name            = $field->args( '_name' );
			$orders          = get_post_meta( $field->object_id(), $name );
			$is_suborder     = filter_var( get_post_meta( $field->object_id(), Alg_MOWC_Order_Metas::IS_SUB_ORDER, true ), FILTER_VALIDATE_BOOLEAN );
			$suborder_id_str = '';
			$counter         = 1;
			?>

			<?php if ( is_array( $orders ) && count( $orders ) > 0 ) { ?>
                <div class="alg-mowc-suborders">
                    <p><label for="<?php echo $id; ?>"><?php echo $label; ?></label></p>
                    <ul style="list-style:inside;">

						<?php foreach ( $orders as $order_id ): ?>
							<?php if ( ! $is_suborder ) { ?>
								<?php $suborder_id_str = $field->object_id() . '-' . $counter . ' / '; ?>
							<?php } ?>
                            <li>
                                <a href="<?php echo get_edit_post_link( $order_id ) ?>"><?php echo $suborder_id_str; ?> #<?php echo $order_id; ?></a>
                            </li>
							<?php $counter ++; ?>
						<?php endforeach; ?>

                    </ul>
                </div>
			<?php } ?>
			<?php
		}
	}

}