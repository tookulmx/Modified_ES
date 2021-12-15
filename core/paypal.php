<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) die;  

final class PQC_PayPal
{
    
    /**
    * Get Access Token From PayPal
    * 
    * @param mixed $url
    * @param mixed $postdata
    */
    public function get_access_token( $url, $postdata ) {
        
        $options = maybe_unserialize( get_option( PQC_SETTING_OPTIONS ) );
        
        $pqc_checkout_settings = (object) $options['pqc_checkout_settings'];
        
        $client_id       =   $pqc_checkout_settings->paypal_client_id;
        $client_secret   =   $pqc_checkout_settings->paypal_client_secret_key;

        $curl = curl_init( $url ); 
        curl_setopt( $curl, CURLOPT_POST, true ); 
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $curl, CURLOPT_USERPWD, $client_id . ":" . $client_secret );
        curl_setopt( $curl, CURLOPT_HEADER, false ); 
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true ); 
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $postdata ); 
        #    curl_setopt( $curl, CURLOPT_VERBOSE, TRUE );
        $response = curl_exec( $curl );
        
        if ( empty( $response ) ) {
            
            // some kind of an error happened
            $error = curl_error( $curl );
            
            error_log( $error );
            
            curl_close( $curl ); // close cURL handler
            
            return array( 'error' => true, 'response' => $error );
            
        }
        else {
            
            $info = curl_getinfo( $curl );
            
            // echo "Time took: " . $info['total_time']*1000 . "ms\n";
            curl_close( $curl ); // close cURL handler
            
            if ( $info['http_code'] != 200 && $info['http_code'] != 201 ) {
                
                error_log( $response );
                
                return array( 'error' => true, 'response' => $response );
                
            }
        }

        // Convert the result from JSON format to a PHP array 
        $json_response = json_decode( $response );
        
        return $json_response->access_token;
    }
    
    public function make_post_call( $url, $postdata, $token ) {
        
        /**
        $headers = array(
            'Authorization' => 'Bearer ' .$token,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        );

        $response_con = wp_remote_post( $url,
            array(
                'headers'       => $headers,
                'method'        => 'POST',
                'timeout'       => 45,
                'redirection'   => 5,
                'httpversion'   => '1.0',
                'blocking'      => true,   
                'body'          => $postdata,
                'cookies'       => array()
            )
        );
        
        $response = wp_remote_retrieve_body( $response_con );

        if ( is_wp_error( $response ) ) {
            exit( 'conection error' );
        } 

        // Convert the result from JSON format to a PHP array 
        $json_response = json_decode( $response, TRUE );
        
        return $json_response;
        */
        
        // global $token;
        $curl = curl_init( $url ); 
        curl_setopt( $curl, CURLOPT_POST, true );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $curl, CURLOPT_HEADER, false );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl, CURLOPT_HTTPHEADER,
            array(
                'Authorization: Bearer '.$token,
                'Accept: application/json',
                'Content-Type: application/json'
            )
        );

        curl_setopt( $curl, CURLOPT_POSTFIELDS, $postdata ); 
        #curl_setopt( $curl, CURLOPT_VERBOSE, TRUE );
        $response = curl_exec( $curl );

        if ( empty( $response ) ) {
            
            // some kind of an error happened
            $error = curl_error( $curl );
            
            error_log( $error );
            
            curl_close( $curl ); // close cURL handler
            
            return array( 'error' => true, 'response' => $error );
            
        }
        else {
            
            $info = curl_getinfo( $curl );
            
            // echo "Time took: " . $info['total_time']*1000 . "ms\n";
            curl_close( $curl ); // close cURL handler
            
            if ( $info['http_code'] != 200 && $info['http_code'] != 201 ) {
                
                error_log( $response );
                
                return array( 'error' => true, 'response' => $response );
                
            }
        }

        // Convert the result from JSON format to a PHP array 
        $json_response = json_decode( $response, TRUE );
        
        return $json_response;
    }
    
    public function payment_start( $order_id ) {
        
        if ( ! pqc_is_paypal_ready() ) return false;

        $options = maybe_unserialize( get_option( PQC_SETTING_OPTIONS ) );
        
        $pqc_checkout_settings = (object) $options['pqc_checkout_settings'];
        
        $paypal_sandbox = $pqc_checkout_settings->paypal_sandbox;
        
        $host           = $paypal_sandbox == 0 ? 'https://api.paypal.com' : 'https://api.sandbox.paypal.com';
        $url            = $host . '/v1/oauth2/token'; 
        $post_args      = 'grant_type=client_credentials';
        $token          = $this->get_access_token( $url, $post_args );
        
        if ( is_array( $token ) && isset( $token['error'] ) ) return $token;
        
        $url            = $host . '/v1/payments/payment';
        
        if ( preg_match( '/page_id=/i', get_permalink( pqc_page_exists( 'pqc-orders' ) ) ) == 1 )
            $query_string =  "&order_id=$order_id&order_status=complete&payment_method=paypal";
        else
            $query_string = "?order_id=$order_id&order_status=complete&payment_method=paypal";
        
        $processor_link = get_permalink( pqc_page_exists( 'pqc-orders' ) ) . $query_string;
        
        $cancel_link    = get_permalink( pqc_page_exists( 'pqc-checkout' ) );
        $description    = esc_html__( 'Payment for printing and shipping items at ', 'pqc' ) . esc_html( home_url() );
        
        $order_meta     = get_post_meta( $order_id, 'pqc_order_data' )[0];
        
        if ( $order_meta['order_status'] != 'pending' )
            return array( 'error' => true, 'response' => __( 'Sorry, payment cannot be proccessed for this order.', 'pqc' ) );
        
        $items          = $order_meta['items'];
        $currency       = esc_attr( $order_meta['currency'] );
        $shipping_cost  = $this->number_format( $order_meta['shipping_cost'] );
        $cart_total     = $this->number_format( $order_meta['cart_total'] );
        $subtotal       = $this->number_format( $order_meta['subtotal'] );
        $tax            = $this->number_format( 0.00 );
        $total          = $this->number_format( $order_meta['total'] );
        $coupon         = $order_meta['coupon'];
        
        if ( ! empty( $coupon ) ) $coupon_id = pqc_get_coupon_id( $coupon );
        
        $payment = array(
            'intent' => 'sale',
            "redirect_urls" => array(
                "return_url" => $processor_link,
                "cancel_url" => $cancel_link
            ),
            'payer' => array( "payment_method" => "paypal" ),
        );

        $payment['transactions'][0] = array(
            'amount' => array(
                'total' => $total,
                'currency' => $currency,
                'details' => array(
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'shipping' => $shipping_cost
                )
            ),
            'description' => $description
        );
        
        foreach( $items as $item_data ) {
            
            $name   = $item_data['name'];
            $qty    = $item_data['quantity'];
            $price  = $this->number_format( $item_data['price'] );

            $payment['transactions'][0]['item_list']['items'][] = array(
                'quantity'  => "$qty",
                'name'      => "$name",
                'price'     => "$price",
                'currency'  => "$currency",
                'sku'       => 'Paying for ' . $item_data['unique_id'] . ' - Material Used: ' . $item_data['material'],
            );    
            
        }
        
        if ( isset( $coupon_id ) ) {

            $discount_amount = $this->number_format( floatval( $order_meta['cart_total'] ) - floatval( $order_meta['subtotal'] ) );

            $payment['transactions'][0]['item_list']['items'][] = array(
                'quantity'  => 1,
                'name'      => "Discount",
                'price'     => "-$discount_amount",
                'currency'  => "$currency",
                'sku'       => "Coupon ($coupon) for Order #$order_id",
            );

        }
        
        $json = json_encode( $payment );
        
        $json_resp = $this->make_post_call( $url, $json, $token );
        
        if ( is_array( $json_resp ) && isset( $json_resp['error'] ) ) return $json_resp;
        
        foreach ( $json_resp['links'] as $link ) {
            
            if ( $link['rel'] == 'execute' ) {
                
                $payment_execute_url = $link['href'];
                
                $payment_execute_method = $link['method'];
            }
            elseif ( $link['rel'] == 'approval_url' ) {
                
                $payment_approval_url = $link['href'];
                
                $payment_approval_method = $link['method'];
            }

        }
        
        $executor['paypal_execute']     =   $payment_execute_url;
        $executor['paypal_token']       =   $token;
        $executor['order_id']           =   $order_id;
        
        if ( is_user_logged_in() ) {
            update_user_meta( get_current_user_id(), 'pqc_buyer_paypal_data', $executor );
        }
        else {
            update_option( 'pqc_buyer_paypal_data_' . pqc_real_ip(), $executor );
        }

        wp_redirect( $payment_approval_url );
        
        exit;
        
    }
    
    public function payment_end( $order_id ) {

        if ( ! pqc_is_paypal_ready() ) return;

        $options = maybe_unserialize( get_option( PQC_SETTING_OPTIONS ) );
        
        $pqc_checkout_settings = (object) $options['pqc_checkout_settings'];
        
        $paypal_sandbox = $pqc_checkout_settings->paypal_sandbox;
        
        $host           = $paypal_sandbox == 0 ? 'https://api.paypal.com' : 'https://api.sandbox.paypal.com';

        $processor_link = get_permalink( pqc_page_exists( 'pqc-checkout' ) ) . 'processor/';
        
        $allowed_html   =   array();
        
        if ( isset( $_GET['token'] ) && isset( $_GET['PayerID'] ) ) {
            
            $get_token  =   sanitize_text_field( wp_kses( $_GET['token'], $allowed_html ) );
            $payment_id =   sanitize_text_field( wp_kses( $_GET['paymentId'] , $allowed_html ) );
            $payer_id   =   sanitize_text_field( wp_kses( $_GET['PayerID'] , $allowed_html ) );
            
            if ( is_user_logged_in() ) {
                $save_data = get_user_meta( get_current_user_id(), 'pqc_buyer_paypal_data', false )[0];
            }
            else {
                $save_data = get_option( 'pqc_buyer_paypal_data_' . pqc_real_ip(), array() )[0];
            }
            
            $payment_execute_url    =   $save_data['paypal_execute'];
            $token                  =   $save_data['paypal_token'];
            
            $payment_execute = array(
                'payer_id' => $payer_id
            );
            
            $json       =   json_encode( $payment_execute );
            $json_resp  =   $this->make_post_call( $payment_execute_url, $json, $token );
            
            /*
            var_dump( $json_resp );
            
            echo '<pre>';
            print_r( $json_resp );
            echo '</pre>';
            */
            
            if ( isset( $json_resp['error'] ) && $json_resp['error'] == true ) {

                if ( isset( $json_resp['response'] ) && json_decode( $json_resp['response'] )->name == 'PAYMENT_ALREADY_DONE' ) {
                    
                    return array( 'error' => true, 'response' => __( 'Payment has been made already.', 'pqc' ) );
                    
                }
                
                return false;
            }
            
            if ( $json_resp['state'] == 'approved' ) {
                
                $prev = get_post_meta( $order_id, 'pqc_order_data' )[0];
                
                // Order approved
                $prev['payer_email']    = $json_resp['payer']['payer_info']['email'];
                $prev['txn_id']         = $payment_id;
                $prev['order_status']   = 'completed';
                $prev['payment_method'] = 'paypal';

                // Update Post Meta
                update_post_meta( $order_id, 'pqc_order_data', $prev );
                
                // Send Email
                pqc_send_order_email( $order_id );
                
                $GLOBALS['payment_message'] = sprintf(
                    __( 'Transaction Complete, Transaction ID - <strong>%s</strong> ', 'pqc' ),
                    $payment_id 
                );
                
            } else {
                
                $prev = get_post_meta( $order_id, 'pqc_order_data' )[0];
                
                // Order cancelled
                $prev['payer_email']    = isset( $json_resp['payer']['payer_info']['email'] ) ? $json_resp['payer']['payer_info']['email'] : '-';
                $prev['txn_id']         = isset( $payment_id ) ? $payement_id : '-';
                $prev['order_status']   = 'cancelled';
                $prev['payment_method'] = 'paypal';

                // Update Post Meta
                update_post_meta( $order_id, 'pqc_order_data', $prev );
                
                $GLOBALS['payment_message'] = __( 'Transaction Cancelled.', 'pqc' );
                
            }
            
        }
            
    }

    /**
    * Format a number with grouped thousands
    * 
    * @param int|float(double) $number The number to be formatted
    */
    private function number_format( $number ) {
        
        $control = pqc_money_format_control();
        
        $number = empty( $number ) ? 0.00 : $number;
        
        $number = number_format( floatval( $number ), $control['decimals'], '.', ',' );

        return $number;
        
    }

}