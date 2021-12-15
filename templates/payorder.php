<div id="pqc-wrapper" class="container">

    <?php do_action( 'pqc_wrapper_payorder_start' ); ?>

    <?php $payment_message = apply_filters( 'pqc_payment_response', '' ); ?>

    <header class="codrops-header">
        <?php if ( ! empty( $payment_message ) ) : ?>
        <div class="notice"><p><?php echo $payment_message; ?></p></div>
        <?php endif; ?>
    </header>
    
    <?php if ( ( isset( $error ) && $error ) || ( isset( $notice ) && $notice ) ) : ?>
    
    <header class="codrops-header">
        <?php if ( isset( $error ) ) : ?>
        <div class="error"><p><?php echo $error_msg; ?></p></div>
        <?php elseif ( isset( $notice ) ) : ?>
        <div class="notice"><p><?php echo $notice_msg ?></p></div>
        <?php endif; ?>
    </header>
    
    <?php else : ?>
    
    <header class="codrops-header" style="text-align: left;">
        <h3><?php _e( 'Your order', 'pqc' ); ?> <a href="#" id="show-cart-details" style="font-size: 14px; float: right;">Toggle Details</a></h3>                
    </header>
    
    <form name="pqc_place_order" id="pqc_place_order" method="POST" enctype="multipart/form-data">
    
        <div id="content-full" style="display: none;">
            <div class="item-total-heading">
                <h1 style="padding: 0; float: left; width: 50%;"><?php printf( _n( '%1$s item to checkout', '%1$s items to checkout', $total_item, 'pqc' ), $total_item ); ?></h1>
            </div>

            <table class="cart">
                <thead>
                    <tr>
                        <th id="item"><?php _e( 'Item x Qty', 'pqc' ); ?></th>
                        <th id="material"><?php _e( 'Materials', 'pqc' ); ?></th>
                        <th id="infill"><?php _e( 'Infill Rate', 'pqc' ); ?></th>
                        <th id="total"><?php _e( 'Total', 'pqc' ); ?></th>
                    </tr>
                </thead>
                
                <tbody>
                    <?php $i = 0; foreach( $the_items as $the_item ) : ?>
                    <tr id="<?php echo $i; ?>" <?php if ( $i % 2 ) { echo 'class="even"'; } ?>>
                        <td class="item">
                            <p><strong><?php echo $the_item['name']; ?></strong> x <?php echo $the_item['quantity']; ?></p>
                        </td>
                        <td class="item">
                            <div id="materials"><?php echo $the_item['material']; ?></div>
                        </td>
                        <td class="item">
                            <div id="infill"><?php echo ! isset( $the_item['infill'] ) && empty( $the_item['infill'] ) ? 100 : $the_item['infill']; ?>%</div>
                        </td>
                        <td class="item" id="<?php echo $i; ?>-total"><p><?php  echo pqc_money_format( $the_item['amount'], $currency, $currency_pos ); ?></p></td>
                    </tr>
                    <?php $i++; endforeach; ?>
                </tbody>
                <tfoot style="background: #689F38;font-size: 20px;text-decoration: underline;color: #fff;">
                    <tr <?php if ( $i % 2 ) { echo 'class="even"'; } ?>>
                        <td class="item"></td>
                        <td class="item">Cart Total</td>
                        <td class="item"></td>
                        <td id="cart-total"><p><?php echo $cart_total; ?></p></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div id="content-left">
        
            <div id="item-attributes">
            
                <fieldset id="item-attributes_fieldset" class="pqc-item-fieldset">
                    
                    <legend><?php _e( 'Shipping Details', 'pqc' ); ?></legend>
                    
                    <div class="field-wrap" id="buyer-first-name">
                        <p><?php printf( __( 'First Name: %s', 'pqc' ), $firstname ); ?></p>
                    </div>
                    
                    <div class="field-wrap" id="buyer-last-name">
                        <p><?php printf( __( 'Last Name: %s', 'pqc' ), $lastname ); ?></p>
                    </div>
                    
                    <div class="field-wrap" id="buyer-address">
                        <p><?php printf( __( 'Shipping Address: %s', 'pqc' ), $address ); ?></p>
                    </div>
                    
                    <div class="field-wrap" id="buyer-city-zipcode">
                        <p><?php printf( __( 'City: %s', 'pqc' ), $city ); ?> - <?php printf( __( '%s: %s', 'pqc' ), $location_info[0], $zipcode ); ?></p>
                    </div>
                    
                    <div class="field-wrap" id="buyer-state">
                        <p><?php printf( __( '%s: %s', 'pqc' ), $location_info[1], $state ); ?></p>
                    </div>
                    
                </fieldset>
                
            </div>
            
        </div>
        
        <div id="content-right">
        
            <div id="item-attributes">
            
                <fieldset id="item-attributes_fieldset" class="pqc-item-fieldset">
                    
                    <legend><?php _e( 'Order', 'pqc' ); ?></legend>
                    
                    <table class="cart-total">
                        <thead>
                            <tr>
                                <th style="width: 40%; visibility: hidden;"></th>
                                <th style="width: 60%; visibility: hidden;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ( isset( $coupon ) && ! empty( $coupon ) ) : ?>
                            <tr class="coupon">
                                <td>Coupon Used</td>
                                <td id="coupon"><?php echo $coupon; ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="sub-total">
                                <td>Subtotal</td>
                                <td id="subtotal"><?php echo $subtotal; ?></td>
                            </tr>
                            
                            <tr class="shipping">
                                <td>Shipping</td>
                                <td>
                                    <div id="shipping_options">
                                        
                                        <p id="shipping-option"><?php if ( isset( $shipping_option ) ) echo $shipping_option; ?></p>
                                        
                                        <p id="shipping-cost"><?php if ( isset( $shipping_cost ) ) echo 'Cost: ' . $shipping_cost; ?></p>
                                                 
                                    </div>
                                </td>
                            </tr>
                                                        
                        </tbody>
                        
                        <tfoot>
                            <tr class="total">
                                <td>Total</td>
                                <td id="total"><?php echo $total; ?></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                </fieldset>
                
            </div>
            
        </div>

        <div id="payment-options">

            <fieldset id="payment_options_fieldset" class="pqc-payment-options-fieldset">
            
                <legend><?php _e( 'Checkout', 'pqc' ); ?></legend>
                
                <ul class="pqc-payment-options-list" style="float: left; margin: 0 0 0 0.2em;"><?php $this->payment_options( $data ); ?></ul>
                
                <input type="submit" name="complete_order" id="complete-order-button" style="float: right; display: none;" value="<?php _e( 'Complete Order', 'pqc' ); ?>">
                
            </fieldset>
        
        </div>
    
    </form>
    
    <?php endif; ?>
    
    <?php do_action( 'pqc_wrapper_payorder_end' ); ?>
    
</div><!-- /container -->