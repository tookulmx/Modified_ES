<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) die;  

final class PQC_Stripe
{

    public function payment_start( $order_id ) {
        
        if ( ! pqc_is_stripe_ready( true ) ) return false;
        
        require_once PQC_PATH . 'core/lib/stripe/init.php';

        $options = (object) maybe_unserialize( get_option( PQC_SETTING_OPTIONS ) );

        $stripe = array(
            "secret_key"      => $options->pqc_checkout_settings['stripe_secret_key'],
            "publishable_key" => $options->pqc_checkout_settings['stripe_publishable_key'],
        );

        \Stripe\Stripe::setAppInfo( PQC_NAME, PQC_VERSION, PQC_AUTHOR_URI );
        
        \Stripe\Stripe::setApiKey( $stripe['secret_key'] );

        $processor_link = get_permalink( pqc_page_exists( 'pqc-orders' ) );
        
        $description    = esc_html__( 'Payment for printing and shipping items at ', 'pqc' ) . esc_url_raw( home_url() );
        
        $order_meta     = get_post_meta( $order_id, 'pqc_order_data' )[0];
        
        if ( $order_meta['order_status'] != 'pending' )
            return array( 'error' => true, 'response' => __( 'Sorry, payment cannot be proccessed for this order.', 'pqc' ) );
        
        $items          = $order_meta['items'];
        $currency       = esc_attr( $order_meta['currency'] );
        $shipping_cost  = number_format( floatval( $order_meta['shipping_cost'] ), 2, '.', ',' );
        $cart_total     = number_format( floatval( $order_meta['cart_total'] ), 2, '.', ',' );
        $subtotal       = number_format( floatval( $order_meta['subtotal'] ), 2, '.', ',' );
        $total          = floatval( $order_meta['total'] ) * 100;
        $coupon         = $order_meta['coupon'];
        
        if ( ! empty( $coupon ) ) $coupon_id = pqc_get_coupon_id( $coupon );
        
        wp_enqueue_script( PQC_NAME . '_stripecheckout', 'https://checkout.stripe.com/checkout.js', array( 'jquery' ), PQC_VERSION, false );
        
        wp_enqueue_script( PQC_NAME . ' Stripe Handler', PQC_URL . 'assets/js/stripe-handler.js', array( 'jquery' ), PQC_VERSION, false );
        
        wp_localize_script(
            PQC_NAME . ' Stripe Handler', 'PQC_STRIPE',
            array(
                'name'          => get_bloginfo(),
                'button'        => __( 'Pay with Stripe', 'pqc' ),
                'desc'          => $description,
                'form_action'   => $processor_link,
                'currency'      => $currency,
                'pub_key'       => $options->pqc_checkout_settings['stripe_publishable_key'],
                'order_id'      => $order_id,
                'price'         => $total,
            )
        );
        
    }
    
    public function payment_end( $order_id ) {

        if ( ! pqc_is_stripe_ready( true ) ) return false;
        
        if ( ! isset( $_GET['stripeToken'] ) || empty( $_GET['stripeToken'] ) ) return false;
        
        require_once PQC_PATH . 'core/lib/stripe/init.php';

        $options = (object) maybe_unserialize( get_option( PQC_SETTING_OPTIONS ) );

        $stripe = array(
            "secret_key"      => $options->pqc_checkout_settings['stripe_secret_key'],
            "publishable_key" => $options->pqc_checkout_settings['stripe_publishable_key'],
        );

        \Stripe\Stripe::setAppInfo( PQC_NAME, PQC_VERSION, PQC_AUTHOR_URI );
        
        \Stripe\Stripe::setApiKey( $stripe['secret_key'] );
        
        $description    = esc_html__( 'Payment for printing and shipping items at ', 'pqc' ) . esc_url_raw( home_url() );
        
        $order_meta     = get_post_meta( $order_id, 'pqc_order_data' )[0];
        
        if ( $order_meta['order_status'] != 'pending' )
            return array( 'error' => true, 'response' => __( 'Sorry, payment cannot be proccessed for this order.', 'pqc' ) );
        
        $items          = $order_meta['items'];
        $currency       = esc_attr( $order_meta['currency'] );
        $shipping_cost  = pqc_money_format( floatval( $order_meta['shipping_cost'] ), $currency, true );
        $cart_total     = pqc_money_format( floatval( $order_meta['cart_total'] ), $currency, true );
        $subtotal       = pqc_money_format( floatval( $order_meta['subtotal'] ), $currency, true );
        $tax            = pqc_money_format( floatval( 0.00 ), $currency, true );
        $total          = ceil( floatval( $order_meta['total'] ) );
        $coupon         = $order_meta['coupon'];

        $token  = $_GET['stripeToken'];
        $email  = $_GET['stripeEmail'];

        $metadata = array(
            'order_id'  => $order_id,
            'subtotal'  => $subtotal,
            'tax'       => $tax,
            'shipping'  => $shipping_cost,
        );
        
        foreach( $items as $item_data ) {
            
            $name = $item_data['name'];
            $qty = $item_data['quantity'];
            $price = pqc_money_format( floatval( $item_data['price'] ), $currency, true );
            
            $implode[] = "$name - ( $price x $qty )";   
            
        }
        
        $metadata['items'] = implode( ', ', $implode );
        
        if ( ! empty( $coupon ) ) {

            $discount_amount = pqc_money_format( floatval( $order_meta['cart_total'] ) - floatval( $order_meta['subtotal'] ), $currency, true );

            $metadata['discount'] = "$coupon - ( -$discount_amount )";

        }

        try {
            
            $charge = \Stripe\Charge::create( array(
                'source'        => $token,
                'description'   => $description,
                'amount'        => $total * 100,
                'receipt_email' => $email,
                'currency'      => $currency,
                'metadata'      => $metadata,
                )
            );
            
        } catch( \Stripe\Error\Card $e ) {
            
            $body = $e->getJsonBody();
            
            $err  = $body['error'];
            
            $msg = $err['message'];
            
            return array( 'error' => true, 'response' => $msg );
            
            
        } catch ( \Stripe\Error\RateLimit $e ) {
            
            $body = $e->getJsonBody();
            
            $err  = json_encode( $body['error'] );
            
            error_log( 'Too many requests made to the Stripe API too quickly.' . "\r\n" . $err ); 
            
        } catch ( \Stripe\Error\InvalidRequest $e ) {
            
            $body = $e->getJsonBody();
            
            $err  = json_encode( $body['error'] );
            
            error_log( 'Invalid parameters were supplied to Stripe\'s API.' . "\r\n" . $err );
            
        } catch ( \Stripe\Error\Authentication $e ) {
            
            $body = $e->getJsonBody();
            
            $err  = json_encode( $body['error'] );
            
            error_log( 'Authentication with Stripe\'s API failed, maybe you changed API keys recently.' . "\r\n" . $err );
            
        } catch (\Stripe\Error\ApiConnection $e ) {
            
            $body = $e->getJsonBody();
            
            $err  = json_encode( $body['error'] );
            
            error_log( 'Network communication with Stripe failed.' . "\r\n" . $err );

        } catch ( Exception $e ) {
            
            $body = $e->getJsonBody();
            
            $err  = json_encode( $body['error'] );

            error_log( 'Error Occured.' . "\r\n" . $err );
        }
        
        if ( ! isset( $charge ) || ! $charge || $charge->status != 'succeeded' )
            return array( 'error' => true, 'response' => __( 'Error Occurred. Transaction failed and you were not charged.', 'pqc' ) );
    
        if ( $charge->amount == $total * 100 && $charge->currency == strtolower( $currency ) && $charge->receipt_email == $email ) {

            $prev = get_post_meta( $order_id, 'pqc_order_data' )[0];
            
            // Order succeeded
            $prev['payer_email']    = $email;
            $prev['txn_id']         = $charge->id;
            $prev['order_status']   = 'completed';
            $prev['payment_method'] = 'stripe';

            // Update Post Meta
            update_post_meta( $order_id, 'pqc_order_data', $prev );
            
            // Send Email
            pqc_send_order_email( $order_id );
            
            $GLOBALS['payment_message'] = sprintf(
                __( 'Transaction Complete, Transaction ID - <strong>%s</strong> ', 'pqc' ),
                $charge->id 
            );
    
        }
        
    }    

}