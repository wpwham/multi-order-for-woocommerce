<?php
/**
 * Parent order view template
 *
 * @author  Algoritmika Ltd.
 * @version 1.0.0
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>

<section class="alg-mowc-suborder-details">
    <h2>Sub Order details</h2>
    <table>
        <thead>
        <tr>
            <th><?php echo __( 'Suborder ID', 'multi-order-for-woocommerce' ) ?></th>
            <th><?php echo __( 'Order ID', 'multi-order-for-woocommerce' ) ?></th>
        </tr>
        </thead>
        <tbody>
		<?php $counter = 1; ?>
		<?php foreach ( $suborders as $suborder_id ): ?>
			<?php $suborder = wc_get_order( $suborder_id ); ?>
            <tr>
                <td><a href="<?php echo $suborder->get_view_order_url(); ?>"><?php echo $order_id; ?>
                        -<?php echo $counter; ?></a></td>
                <td><a href="<?php echo $suborder->get_view_order_url(); ?>">#<?php echo $suborder_id; ?></a></td>
            </tr>
			<?php $counter ++; ?>
		<?php endforeach; ?>
        </tbody>
    </table>
</section>