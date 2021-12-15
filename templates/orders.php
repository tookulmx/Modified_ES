<div id="pqc-wrapper" class="container">

    <?php if ( isset( $order_id ) ) : extract( $the_orders[$order_id] ); ?>
    
        <h3><?php printf( __( 'Orden #%s', 'pqc' ), $order_id ); ?></h3>
        
        <?php if ( $total_item > 0 ) : ?>

        <table class="cart">
        
            <thead style="background: #41377D; color: #fff;">
                <tr>
                    <th id="item" style="width: 52%;"><?php _e( 'Artículo(s)', 'pqc' ); ?></th>
                    <th id="quantity" style="width: 15%;"><?php _e( 'Cantidad', 'pqc' ); ?></th>
                    <th id="total" style="width: 38%;"><?php _e( 'Precio', 'pqc' ); ?></th>
                </tr>
            </thead>
            
            <tbody>
                <?php foreach( $items as $item_data ) : ?>
                    <tr class="order_item">
                        <td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;">
                        <?php
                        printf(
                        '<span style="font-weight: 700;">%s</span> 
                        <div style="%s">
                        VolumeN: %s cubic cm 
                        <br> Densidad: %s g/cubic cm 
                        <br> Material: %s
                        </div>',
                        $item_data['name'],
                        'font-size: 10px;margin-top: 0.5em;padding: 0.5% 5%;line-height: 1.8;',
                        $item_data['Volumen'],
                        $item_data['Densidad'],
                        $item_data['Material']
                        );
                        ?>
                        </td>
                        <td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">
                        <?php echo $item_data['quantity']; ?>
                        </td>
                        <td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">
                        <?php echo pqc_money_format( $item_data['amount'], $currency, true, $currency_pos ); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            
            <tfoot>
            
                <?php if ( isset( $coupon ) && ! empty( $coupon ) ) : ?>
                <tr>
                    <th class="td" scope="row" colspan="2" style="text-align:left; border-top-width: 2px;">Cupón:</th>
                    <td class="td" style="text-align:left; border-top-width: 2px;">
                    <?php echo $coupon; ?>
                    <?php
                    if ( ! empty( $coupon_amount ) && ! empty( $coupon_type ) ) {
                        $discount = $coupon_type == 'percent' ? 
                        "$coupon_amount% off" :
                        '- ' . pqc_money_format( $coupon_amount, $currency, true, $currency_pos );
                        printf( '<p style="font-style: italic; color: #888;">Discount: %s</p>', $discount );
                    }
                    ?>
                    </td>
                </tr>
                <?php endif; ?>

                <tr>
                    <th class="td" scope="row" colspan="2" style="text-align:left; border-top-width: 2px;">Subtotal:</th>
                    <td class="td" style="text-align:left; border-top-width: 2px;">
                    <?php echo pqc_money_format( $subtotal, $currency, true, $currency_pos ); ?>
                    </td>
                </tr>
                
                <tr>
                    <th class="td" scope="row" colspan="2" style="text-align:left; border-top-width: 2px;">Opción de envío: &nbsp;&nbsp; <?php echo $shipping_option; ?></th>
                    <td class="td" style="text-align:left; border-top-width: 2px;">
                    <?php echo pqc_money_format( $shipping_cost, $currency, true, $currency_pos ); ?>
                    </td>
                </tr>
                
                <tr>
                    <th class="td" scope="row" colspan="2" style="text-align:left; border-top-width: 2px;">Método de pago:</th>
                    <td class="td" style="text-align:left; border-top-width: 2px;">
                    <?php
                    if ( $order_status == 'pending' && isset( $pay_for_order_id ) && ! empty( $pay_for_order_id ) ) :
                        
                    printf(
                        '<a class="link" target="_blank" href="https://www.paypal.com/paypalme/tookulmxpago">Completar pago</a>',
                        get_permalink( pqc_page_exists( 'pqc-checkout' ) ),
                        $pay_for_order_id, $order_id
                    );
                    
                    else: echo $payment_method; endif;
                    ?>
                    </td>
                </tr>
                <?php if ( isset( $txn_id ) && ! empty( $txn_id ) ) : ?>
                <tr>
                    <th class="td" scope="row" colspan="2" style="text-align:left; border-top-width: 2px;">Clave de transacción:</th>
                    <td class="td" style="text-align:left; border-top-width: 2px;">
                    <p style="font-style: italic; color: #888; font-size: 14px;"><?php echo $txn_id; ?></P>
                    </td>
                </tr>
                <?php endif; ?>
                
                <tr>
                    <th class="td" scope="row" colspan="2" style="text-align: left; border-top-width: 2px">Total:</th>
                    <td class="td" style="text-align:left; border-top-width: 2px; background: rgb(139, 195, 74) none repeat scroll 0% 0%; color: rgb(255, 255, 255); font-size: 20px; font-weight: 800;">
                    <?php echo pqc_money_format( $total, $currency, true, $currency_pos ); ?>
                    </td>
                </tr>
                
            </tfoot>

        </table>
        
           
        <?php else : ?>
        
        <table>
            <tbody>
                <tr>
                    <td><?php _e( 'No order found', 'pqc' ); ?></td>
                </tr>
            </tbody>
        </table>
        
        <?php endif; ?>

    <?php else : ?>

    <?php $payment_message = apply_filters( 'pqc_payment_response', '' ); ?>

    <header class="codrops-header">
        <?php if ( ! empty( $payment_message ) ) : ?>
        <div class="notice"><p><?php echo $payment_message; ?></p></div>
        <?php endif; ?>
    </header>

    <header class="codrops-header">
        <?php if ( ! empty( $GLOBALS['payment_message'] ) ) : ?>
        <div class="notice"><p><?php echo $GLOBALS['payment_message']; ?></p></div>
        <?php endif; ?>
        
        <?php if ( isset( $_GET['order_request_sent'] ) && $_GET['order_request_sent'] == 'true' && isset( $_GET['order_id'] ) ) : ?>
        <div class="success"><p><?php echo __( 'Orden enviada con éxito, El ID de tu orden es #', 'pqc' ) . $_GET['order_id']; ?></p></div>
        <?php endif; ?>
    </header>

    <div id="content-full">
    
        <?php /* To be decided ?>
        <div class="search-order">
            <form method="GET" enctype="multipart/form-data">
                <input type="text" placeholder="Search order by Order ID" name="search_order_id">
                <button type="submit" name="search_order"><i class="fa fa-search"></i></button>
            </form>
        </div>
        <?php */ ?>
        
      
    
    </div>
    
    <?php endif; ?>

</div><!-- /container -->