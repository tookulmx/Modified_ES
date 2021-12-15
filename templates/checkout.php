<div id="pqc-wrapper" class="container">

    <?php do_action( 'pqc_wrapper_checkout_start' ); ?>

    <?php $payment_message = apply_filters( 'pqc_payment_response', '' ); ?>

    <header class="codrops-header">
        <?php if ( ! empty( $payment_message ) ) : ?>
        <div class="notice"><p><?php echo $payment_message; ?></p></div>
        <?php endif; ?>
    </header>

    <?php if ( ( ! isset( $shipping_option_set ) || ! $shipping_option_set ) && $items ) : ?>
    
        <header class="codrops-header">
            <h3><?php _e( 'Necesitas seleccionar una opción de envío para continuar', 'pqc' ); ?></h3>        
        </header>
    
    <?php else: ?>

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
        
            <h3>
                <?php _e( 'Tu pedido', 'pqc' ); ?>
                <a href="#" id="show-cart-details" style="font-size: 14px; float: right;">
                    <?php _e( 'Detalles', 'pqc' ); ?>
                </a>
            </h3>

            <div id="coupon-msg" style="margin-bottom: 2%;">
                <?php if ( isset( $coupon_msg ) ) : ?>
                <div class="success"><p><?php echo $coupon_msg; ?></p></div>
                <?php endif; ?>
            </div>
            
            <div id="place-order-msg" style="margin-bottom: 2%;">
                <?php if ( isset( $place_order_msg ) ) : ?>
                <div class="error"><p><?php echo $place_order_msg; ?></p></div>
                <?php endif; ?>
            </div>
            
            <?php if ( ( isset( $update_notice ) && $update_notice ) ) : ?>

            <div class="notice"><p><?php echo $update_notice_msg; ?></p></div>
            
            <?php endif; ?>
                    
        </header>
        
        <div class="content">
        
            <form name="pqc_place_order" id="pqc_place_order" method="POST" enctype="multipart/form-data">
            
                <div id="content-full" style="display: none;">
                    <div class="item-total-heading">
                        <h1 style="padding: 0; float: left; width: 50%;"><?php printf( _n( '%1$s artículos a revisar', '%1$s items to checkout', $total_item, 'pqc' ), $total_item ); ?></h1>
                        <p style="float: right; width: 50%; margin-bottom: 0.2%;">
                            <a id="return-to-cart" href="<?php echo get_permalink( pqc_page_exists( 'pqc-cart' ) ); ?>">
                                <i style="font-size: 15px; vertical-align: sub;" class="fa fa-arrow-circle-left"></i>&nbsp;&nbsp;<?php _e( 'Regresar al carrito', 'pqc' ); ?>
                            </a>
                            <a id="add-more-item" href="<?php echo get_permalink( pqc_page_exists( 'pqc-upload' ) ); ?>">
                                <i style="font-size: 15px; vertical-align: sub;" class="fa fa-cart-arrow-down"></i>&nbsp;&nbsp;<?php _e( 'Agregar más artículos', 'pqc' ); ?>
                            </a>
                        </p>
                    </div>

                    <table class="cart">
                        <thead>
                            <tr>
                                <th id="item"><?php _e( 'Artículos x Cantidad', 'pqc' ); ?></th>
                                <th id="material"><?php _e( 'Materiales', 'pqc' ); ?></th>
                                <th id="total"><?php _e( 'Total', 'pqc' ); ?></th>
                            </tr>
                        </thead>
                        
                        <tbody>
                            <?php $i = 0; foreach( $the_items as $the_item ) : ?>
                            <tr id="<?php echo $the_item['ID']; ?>" <?php if ( $i % 2 ) { echo 'class="even"'; } ?>>
                                <td class="item">
                                    <p><strong><?php echo $the_item['name']; ?></strong> x <?php echo $the_item['quantity']; ?></p>
                                </td>
                                <td class="item">
                                    <div id="materials">
                                    <?php
                                    printf( 'Material: %s - %sg/cm<sup>3</sup>',
                                    $selected_material[$the_item['ID']]['name'],
                                    pqc_number_format( $selected_material[$the_item['ID']]['density'] ) );
                                    ?>
                                    </div>
                                </td>
                                <td class="item" id="<?php echo $the_item['ID']; ?>-total"><p><?php echo $the_item['total']; ?></p></td>
                            </tr>
                            <?php $i++; endforeach; ?>
                        </tbody>
                        <tfoot style="background: #41377D;font-size: 20px;text-decoration: underline;color: #fff;">
                            <tr <?php if ( $i % 2 ) { echo 'class="even"'; } ?>>
                                <td class="item"></td>
                                <td class="item">Total del carrito</td>
                                <td id="cart-total"><p><?php echo $cart_total; ?></p></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div id="content-left">
                
                    <div id="item-attributes">
                    
                        <fieldset id="item-attributes_fieldset" class="pqc-item-fieldset">
                            
                            <legend><?php _e( 'Detalles del pedido', 'pqc' ); ?></legend>
                            
                            <div class="field-wrap" id="buyer-first-name">
                                <input type="text" name="firstname" placeholder="<?php _e( 'Nombre *', 'pqc' ); ?>" required="required">
                            </div>
                            
                            <div class="field-wrap" id="buyer-last-name">
                                <input type="text" name="lastname" placeholder="<?php _e( 'Apellido *', 'pqc' ); ?>" required="required">
                            </div>
                            
                            <div class="field-wrap" id="buyer-email">
                                <input type="email" name="email" placeholder="<?php _e( 'Correo *', 'pqc' ); ?>" required="required">
                            </div>
                            
                            <div class="field-wrap" id="buyer-address">
                                <input type="text" name="address" placeholder="<?php _e( 'Dirección *', 'pqc' ); ?>" required="required">
                            </div>
                            
                            <div class="field-wrap" id="buyer-city-zipcode">
                                <input type="text" name="city" placeholder="<?php _e( 'Ciudad *', 'pqc' ); ?>" required="required" style="width: 49%; float: left;">
                                <input type="text" name="zipcode" placeholder="<?php echo $location_info[0]; ?> *" required="required" style="width: 49%; float: right;">
                            </div>
                            
                            <div class="field-wrap" id="buyer-state">
                                <input type="text" name="state" placeholder="<?php echo $location_info[1]; ?> *" required="required">
                            </div>

                            <div class="field-wrap" id="buyer-order-note">
                                <textarea name="order_note" cols="5" rows="3" placeholder="<?php _e( 'Algún comentario (Opcional)', 'pqc' ); ?>"></textarea>
                            </div>
                            
                        </fieldset>
                        
                    </div>
                    
                </div>
                
                <div id="content-right">
                
                    <div id="item-attributes">
                    
                        <fieldset id="item-attributes_fieldset" class="pqc-item-fieldset">
                            
                            <legend><?php _e( 'Orden', 'pqc' ); ?></legend>
                            
                            <table class="cart-total">
                                <thead>
                                    <tr style="display: none;">
                                        <th style="width: 40%; visibility: hidden;"></th>
                                        <th style="width: 60%; visibility: hidden;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ( isset( $coupon_name ) ) : ?>
                                    <tr class="coupon">
                                        <td>Cupón usado</td>
                                        <td id="coupon"><?php echo $coupon_name; ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr class="sub-total">
                                        <td>Subtotal</td>
                                        <td id="subtotal"><?php echo $subtotal; ?></td>
                                    </tr>
                                    
                                    <tr class="shipping">
                                        <td>Envío</td>
                                        <td>
                                            <div id="shipping_options">
                                                
                                                <p id="shipping-option"><?php if ( isset( $current_shipping_option ) ) echo $current_shipping_option['title']; ?></p>
                                                
                                                <p id="shipping-description"><?php if ( isset( $current_shipping_option ) ) echo $current_shipping_option['desc']; ?></p>
                                                
                                                <p id="shipping-cost"><?php if ( isset( $current_shipping_option ) ) echo 'Cost: ' . $current_shipping_option['cost']; ?></p>
                                                         
                                            </div>
                                        </td>
                                    </tr>
                                                                
                                </tbody>
                                
                                <tfoot>
                                    <tr class="total">
                                        <td>Total (IVA Incluido)</td>
                                        <td id="total"><?php echo $total; ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                            
                            <?php if ( $checkout_option == 2 && ! $material_is_lost ) : ?>
                            <input type="hidden" name="payment_method" value="request price">
                            <input type="submit" name="place_order" id="place-order-button" value="<?php _e( 'Solicitar pedido', 'pqc' ); ?>">
                            <?php endif; ?>
                            
                        </fieldset>
                        
                    </div>
                    
                </div>
                
                <?php if ( $checkout_option != 2 && ! $material_is_lost ) : ?>
                
                <div id="payment-options">

                    <fieldset id="payment_options_fieldset" class="pqc-payment-options-fieldset">
                    
                        <legend><?php _e( 'Checkout', 'pqc' ); ?></legend>
                        
                        <ul class="pqc-payment-options-list"><?php $this->payment_options( $data ); ?></ul>
                        
                        <input type="submit" name="place_order" id="place-order-button" value="<?php _e( 'Place Order', 'pqc' ); ?>">
                        
                    </fieldset>
                
                </div>
                
                <?php endif; ?>
            
            </form>
        
        </div>
        
        <?php endif; ?>    
    
    <?php endif; ?>
    
    <?php do_action( 'pqc_wrapper_checkout_end' ); ?>
    
</div><!-- /container -->