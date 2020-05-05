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
    <h2><?php _e( 'Sub Order details', 'multi-order-for-woocommerce' ); ?></h2>
    <table>
        <thead>
        <tr>
            <th><?php _e( 'Suborders', 'multi-order-for-woocommerce' ); ?></th>
        </tr>
        </thead>
        <tbody>

		<?php foreach ( $suborders as $suborder_id ): ?>
			<?php $suborder = wc_get_order( $suborder_id ); ?>
            <tr>
                <td><a href="<?php echo $suborder->get_view_order_url(); ?>">#<?php echo $suborder->get_order_number(); ?></a></td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>
</section>
