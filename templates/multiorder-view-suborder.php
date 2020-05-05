<?php
/**
 * Sub Order view template
 *
 * @author  Algoritmika Ltd.
 * @version 1.0.0
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>

<?php $parent_order = wc_get_order( $parent_order_id ); ?>

<section class="alg-mowc-suborder-details">
    <h2><?php _e( 'Sub Order details', 'multi-order-for-woocommerce' ); ?></h2>
    <table>
        <thead>
        <tr>
            <th><?php _e( 'Parent Order ID', 'multi-order-for-woocommerce' ); ?></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><a href="<?php echo $parent_order->get_view_order_url(); ?>">#<?php echo $parent_order->get_order_number(); ?></a></td>
        </tr>
        </tbody>
    </table>
</section>
