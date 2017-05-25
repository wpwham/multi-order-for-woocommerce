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
    <h2>Sub Order details</h2>
    <table>
        <thead>
        <tr>
            <th>Sub Order ID</th>
            <th>Parent Order ID</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?php echo $fake_suborder_id; ?></td>
            <td><a href="<?php echo $parent_order->get_view_order_url(); ?>">#<?php echo $parent_order_id; ?></a></td>
        </tr>
        </tbody>
    </table>
</section>