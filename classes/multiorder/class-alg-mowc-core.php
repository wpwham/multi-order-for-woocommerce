<?php
/**
 * Multi order for WooCommerce - Core Class
 *
 * @version 1.4.1
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 * @author  WP Wham
 */

if ( ! class_exists( 'Alg_MOWC_Core' ) ) {

	class Alg_MOWC_Core extends Alg_MOWC_WP_Plugin {

		/**
		 * Ensures only one instance is loaded or can be loaded.
		 *
		 * @version 1.0.1
		 * @since   1.0.1
		 * @return Alg_MOWC_Core
		 */
		public static function get_instance() {
			return parent::get_instance(); // TODO: Change the autogenerated stub
		}

		/**
		 * @var Alg_MOWC_Order_Bkg_Process
		 */
		public $order_bkg_process;

		/**
		 * @var Alg_MOWC_Order_Pay_Status_Bkg_Process;
		 */
		public $order_pay_status_bkg_process;

		/**
		 * Initializes the plugin.
		 *
		 * Should be called after the set_args() method
		 *
		 * @version 1.4.1
		 * @since   1.0.0
		 *
		 * @param array $args
		 */
		public function init() {
			parent::init();

			// Initializes order background process
			$this->order_bkg_process = new Alg_MOWC_Order_Bkg_Process();

			// Initializes order payment status background process
			$this->order_pay_status_bkg_process = new Alg_MOWC_Order_Pay_Status_Bkg_Process();

			// Init admin part
			if ( is_admin() ) {
				$this->init_admin_settings();
				add_action( 'woocommerce_system_status_report', array( $this, 'add_settings_to_status_report' ) );
			}

			if ( filter_var( get_option( Alg_MOWC_Settings_General::OPTION_ENABLE_PLUGIN ), FILTER_VALIDATE_BOOLEAN ) ) {
				$this->setup_plugin();
			}
		}

		/**
		 * Initializes admin settings
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function init_admin_settings() {
			new Alg_MOWC_Admin_Settings();
		}

		/**
		 * Setups the plugin
		 *
		 * @version 1.0.5
		 * @since   1.0.0
		 */
		public function setup_plugin() {
			new Alg_MOWC_Multiorder_CMB();
			new Alg_MOWC_Order_Manager();
			new Alg_MOWC_Order_Columns();
			new Alg_MOWC_Orders_View();
			new Alg_MOWC_Orders_Search();
			new Alg_MOWC_Order_Item();
			new Alg_MOWC_Order_Actions();
			new Alg_MOWC_WC_Report();
		}

		/**
		 * Called when plugin is enabled
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		public static function on_plugin_activation() {
			parent::on_plugin_activation();
			Alg_MOWC_Order_Manager::set_sort_order_meta();
		}

		/**
		 * add settings to WC status report
		 *
		 * @version 1.4.1
		 * @since   1.4.1
		 * @author  WP Wham
		 */
		public static function add_settings_to_status_report() {
			#region add_settings_to_status_report
			$protected_settings = array( 'wpwham_multi_order_license', 'alg_mowc_admin_email' );
			$settings_general   = new Alg_MOWC_Settings_General( false );
			$settings_general   = $settings_general->get_settings( array() );
			$settings = array_merge(
				$settings_general
			);
			?>
			<table class="wc_status_table widefat" cellspacing="0">
				<thead>
					<tr>
						<th colspan="3" data-export-label="Multi Order Settings"><h2><?php esc_html_e( 'Multi Order Settings', 'multi-order-for-woocommerce' ); ?></h2></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $settings as $setting ): ?>
					<?php 
					if ( in_array( $setting['type'], array( 'title', 'sectionend' ) ) ) { 
						continue;
					}
					if ( isset( $setting['title'] ) ) {
						$title = $setting['title'];
					} elseif ( isset( $setting['desc'] ) ) {
						$title = $setting['desc'];
					} else {
						$title = $setting['id'];
					}
					$value = get_option( $setting['id'] ); 
					if ( in_array( $setting['id'], $protected_settings ) ) {
						$value = $value > '' ? '(set)' : 'not set';
					}
					?>
					<tr>
						<td data-export-label="<?php echo esc_attr( $title ); ?>"><?php esc_html_e( $title, 'multi-order-for-woocommerce' ); ?>:</td>
						<td class="help">&nbsp;</td>
						<td><?php echo is_array( $value ) ? print_r( $value, true ) : $value; ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php
			#endregion add_settings_to_status_report
		}

	}
}