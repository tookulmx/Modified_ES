<?php
/**
 * Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2><?php printf( __( 'Order #%s', 'pqc' ), $order_id ); ?></h2>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
	<thead>
		<tr>
			<th class="td" scope="col" style="text-align:left;"><?php _e( 'Item(s)', 'pqc' ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php _e( 'Quantity', 'pqc' ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php _e( 'Price', 'pqc' ); ?></th>
		</tr>
	</thead>
	<tbody>
    <?php foreach( $items as $item_data ) : ?>
        <tr class="order_item">
            <td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
            <?php
            printf(
            '%s <br> <div class="item-meta">Volume: %s cubic cm <br> Density: %s g/cubic cm <br> Material: %s</div>',
            $item_data['name'],
            $item_data['volume'],
            $item_data['density'],
            $item_data['material']
            );
            ?>
            </td>
            <td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
            <?php echo $item_data['quantity']; ?>
            </td>
            <td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
            <?php echo pqc_money_format( $item_data['amount'], $currency, true, $currency_pos ); ?>
            </td>
        </tr>
    <?php endforeach; ?>
	</tbody>
	<tfoot>
        <tr>
			<th class="td" scope="row" colspan="2" style="text-align:left; border-top-width: 4px;">Subtotal:</th>
			<td class="td" style="text-align:left; border-top-width: 4px;">
            <?php echo pqc_money_format( $subtotal, $currency, true, $currency_pos ); ?>
            </td>
		</tr>
        
        <tr>
            <th class="td" scope="row" colspan="2" style="text-align:left; border-top-width: 4px;">Shipping Option: &nbsp;&nbsp; <?php echo $shipping_option; ?></th>
            <td class="td" style="text-align:left; border-top-width: 4px;">
            <?php echo pqc_money_format( $shipping_cost, $currency, true, $currency_pos ); ?>
            </td>
        </tr>
        
        <tr>
            <th class="td" scope="row" colspan="2" style="text-align:left; border-top-width: 4px;">Payment Method:</th>
            <td class="td" style="text-align:left; border-top-width: 4px;">
            <?php
            if ( $order_status == 'pending' && isset( $pay_for_order_id ) && ! empty( $pay_for_order_id ) ) :
                
            printf(
                '<a class="link" target="_blank" href="%s?pay_for_order=true&pay_for_order_id=%s&order_id=%s">Complete payment</a>',
                get_permalink( pqc_page_exists( 'pqc-checkout' ) ),
                $pay_for_order_id, $order_id
            );
            
            else: echo $payment_method; endif;
            ?>
            </td>
        </tr>
        
        <tr>
            <th class="td" scope="row" colspan="2" style="text-align:left; border-top-width: 4px;">Total:</th>
            <td class="td" style="text-align:left; border-top-width: 4px;">
            <?php echo pqc_money_format( $total, $currency, true, $currency_pos ); ?>
            </td>
        </tr>
        
	</tfoot>
</table>

<h2><?php _e( 'Customer details', 'pqc' ); ?></h2>
<ul>
    <?php foreach ( $fields as $name => $value ) : $name = ucfirst( $name ); ?>
        <li><strong><?php echo wp_kses_post( $name ); ?>:</strong> <span class="text"><?php echo wp_kses_post( $value ); ?></span></li>
    <?php endforeach; ?>
</ul>
