<?php
/**
 * Multi order for WooCommerce - Payment status meta box
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MOWC_Payment_Status_CMB' ) ) {

	class Alg_MOWC_Payment_Status_CMB {
		public $cmb_id = 'alg_mowc_payment_status_cmb';
		public $meta_deduct_from_main_order = 'alg_mowc_ps_dfmo';
		public $meta_change_main_order = 'alg_mowc_ps_mo';
		public $meta_change_sub_order = 'alg_mowc_ps_so';
		public $meta_set_status = 'alg_mowc_set_status';

		/**
		 * Initializes
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function init() {
			// Adds metabox
			add_action( 'cmb2_admin_init', array( $this, 'add_cmb' ) );

			$object = 'term';
			$cmb_id = $this->cmb_id;
			add_action( "cmb2_after_{$object}_form_{$cmb_id}", array( $this, 'cmb2_custom_style' ), 10, 2 );
		}

		/**
		 * Handles Cmb2 custom style
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function cmb2_custom_style( $post_id, $cmb ) {
			?>
            <style type="text/css" media="screen">
                #cmb2-metabox-alg_mowc_payment_status_cmb .cmb-type-checkbox .cmb-td {
                    line-height: 2.4;
                }
                .taxonomy-alg_mowc_payment_status table.wp-list-table .column-name{
                    width:15%;
                }
                .alg-mowc-status-list{
                    list-style:none;
                    margin:0;
                    padding:0;
                }
                .alg-mowc-status-list-item{
                    margin:0 0 5px;
                }
                .alg-mowc-status-list-item:before{
                    content:"- "
                }
            </style>
			<?php
		}

		/**
		 * Displays checkbox column
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param            $field_args
		 * @param CMB2_Field $field
		 */
		public function display_checkbox_column( $field_args, CMB2_Field $field ) {
			$checkbox_value     = filter_var( $field->value, FILTER_VALIDATE_BOOLEAN );
			$checkbox_value_str = $checkbox_value ? 'checked' : '';
			?>
            <div class="custom-column-display <?php echo $field->row_classes(); ?>">
                <input style="opacity:1;cursor:default" type="checkbox" disabled <?php echo $checkbox_value_str; ?>/>
                <p class="description"><?php echo $field->args( 'description' ); ?></p>
            </div>

			<?php
		}

		/**
		 * Displays multiselect column
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param            $field_args
		 * @param CMB2_Field $field
		 */
		public function display_multiselect_column( $field_args, CMB2_Field $field ) {
			$values = $field->value;
			if(!is_array($values)){
			    return;
            }
			foreach ( $values as $value ) {
			    echo '<ul class="alg-mowc-status-list">';
				foreach ( wc_get_order_statuses() as $wc_status_key => $wc_status_value ) {
                    if($value==$wc_status_key){
                        echo '<li class="alg-mowc-status-list-item">'.$wc_status_value.'</li>';
                    }
				}
				echo '</ul>';
			}
			//error_log(print_r($value,true));
		}

		/**
		 * Adds the Custom meta box
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add_cmb() {
			$payment_status_tax = new Alg_MOWC_Order_Payment_Status();

			$cmb_demo = new_cmb2_box( array(
				'id'           => $this->cmb_id,
				'title'        => __( 'Multi order', 'multi-order-for-woocommerce' ),
				'object_types' => array( 'term' ),
				'taxonomies'   => array( $payment_status_tax->id ),
				'context'      => 'side',

			) );

			$cmb_demo->add_field( array(
				'name'       => esc_html__( 'Deduct from main order', 'multi-order-for-woocommerce' ),
				'id'         => $this->meta_deduct_from_main_order,
				'type'       => 'checkbox',
				'default'    => false,
				'column'     => array(
					'position' => 2,
					'name'     => __( 'Deduct', 'multi-order-for-woocommerce' ),
				),
				'display_cb' => array( $this, 'display_checkbox_column' ),
			) );

			/*$cmb_demo->add_field( array(
				'name'       => esc_html__( 'Change suborders', 'multi-order-for-woocommerce' ),
				'id'         => $this->meta_change_sub_order,
				'type'       => 'checkbox',
				'default'    => true,
				'column'     => array(
					'position' => 3,
					'name'     => __( 'Suborders', 'multi-order-for-woocommerce' ),
				),
				'display_cb' => array( $this, 'display_checkbox_column' ),
			) );

			$cmb_demo->add_field( array(
				'name'       => esc_html__( 'Change main orders', 'multi-order-for-woocommerce' ),
				'id'         => $this->meta_change_main_order,
				'type'       => 'checkbox',
				'default'    => true,
				'column'     => array(
					'position' => 4,
					'name'     => __( 'Main orders', 'multi-order-for-woocommerce' ),
				),
				'display_cb' => array( $this, 'display_checkbox_column' ),
			) );*/

			$cmb_demo->add_field( array(
				'name'    => esc_html__( 'Status', 'multi-order-for-woocommerce' ),
				'desc'    => 'Status that will automatically set the order payment status',
				'id'      => $this->meta_set_status,
				'type'    => 'pw_multiselect',
				'default' => '',
				'attributes' => array(
					'placeholder' => 'Select status'
				),
				'column'  => array(
					'position' => 2,
					'name'     => __( 'Status', 'multi-order-for-woocommerce' ),
				),
				'options' => wc_get_order_statuses(),
				'display_cb' => array( $this, 'display_multiselect_column' ),
				//'display_cb' => array( $this, 'display_checkbox_column' ),
			) );
		}


	}

}