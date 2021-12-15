<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) die;

if ( ! class_exists( 'PQC_Public' ) ) :

final class PQC_Public
{

    private $ajax_action = 'public_ajax';

    /**
    * The Constructor
    *
    */
    public function __construct() {

        if ( ! defined( 'DOING_AJAX' ) ) {

            global $pqc;

            // if ( $pqc->has_valid_license() ) $this->payment_load();
            $this->payment_load();

            add_shortcode( PQC_UPLOAD_SHORTCODE, array( $this, 'upload' ) );

            add_shortcode( PQC_CART_SHORTCODE, array( $this, 'cart' ) );

            add_shortcode( PQC_CHECKOUT_SHORTCODE, array( $this, 'checkout' ) );

            add_shortcode( PQC_ORDERS_SHORTCODE, array( $this, 'orders' ) );

            add_action( 'wp_enqueue_scripts',  array( &$this, 'public_scripts' ), 0 );

        }
        else {

            $priv   = 'wp_ajax_' . $this->ajax_action;
            $nopriv = 'wp_ajax_nopriv_' . $this->ajax_action;

            // Ajax Cart Update Actions
            add_action( "$priv-update",                 array( $this, $this->ajax_action . '_update' ) );
            add_action( "$nopriv-update",               array( $this, $this->ajax_action . '_update' ) );

            // Ajax Cart Delete Actions
            add_action( "$priv-delete",                 array( $this, $this->ajax_action . '_delete' ) );
            add_action( "$nopriv-delete",               array( $this, $this->ajax_action . '_delete' ) );

        }

    }

    private function payment_load() {

        if (
            ( isset( $_GET['order_status'] ) && ! empty( $_GET['order_status'] ) ) &&
            ( isset( $_GET['payment_method'] ) && ! empty( $_GET['payment_method'] ) ) &&
            ( isset( $_GET['order_id'] ) && ! empty( $_GET['order_id'] ) )
        ) {

            global $pqc_payment_options;

            $status         = sanitize_text_field( $_GET['order_status'] );
            $payment_method = sanitize_text_field( $_GET['payment_method'] );
            $order_id       = intval( $_GET['order_id'] );

            $allowed = array( 'processing' => true, 'complete' => false );

            if ( ! array_key_exists( $status, $allowed ) || ! array_key_exists( $payment_method, $pqc_payment_options ) ) return;

            $this->load_payment_method( $order_id, $payment_method, $allowed[$status] );

        }

        if ( isset( $_POST['place_order'] ) ) $this->place_order();

        if ( isset( $_POST['complete_order'] ) ) $this->complete_order();

    }

    private function place_order() {

        extract( $_POST );

        global $wpdb;

        $place_order_error = false;

        $settings = maybe_unserialize( get_option( PQC_SETTING_OPTIONS, array() ) );

        $checkout_option = $settings['pqc_checkout_settings']['checkout_option'];

        if (
        ! isset( $firstname ) || empty( $firstname ) ||
        ! isset( $lastname ) || empty( $lastname ) ||
        ! isset( $email ) || empty( $email ) ||
        ! isset( $address ) || empty( $address ) ||
        ! isset( $city ) || empty( $city ) ||
        ! isset( $zipcode ) || empty( $zipcode ) ||
        ! isset( $state ) || empty( $state )
        ) {

            $place_order_error = true;

            $place_order_msg = __( '<strong> Error! </strong> Missing required field.', 'pqc' );

        }
        elseif ( $checkout_option != 2 ) {

            global $pqc_payment_options;

            if ( ! isset( $payment_method ) || empty( $payment_method ) | ! $payment_method ) {

                $place_order_error = true;

                $place_order_msg = __( '<strong> Error! </strong> No payment method selected.', 'pqc' );

            }
            elseif ( ! array_key_exists( $payment_method, $pqc_payment_options ) ) {

                $place_order_error = true;

                $place_order_msg = __( '<strong> Error! </strong> Invalid payment option selected.', 'pqc' );

            }

        }
        
        // var_dump( $place_order_error );
        // var_dump( $place_order_msg );
        // exit;

        if ( ! $place_order_error ) {

            $user_ip = pqc_real_ip();

            $materials = $wpdb->get_results( "SELECT * FROM " . PQC_MATERIALS_TABLE );

            if ( $materials ) {

                $g = 0;

                foreach ( $materials as $material ) {

                    if ( $g == 0 ) $material_id = $material->ID;

                    $the_materials[$material->ID] = array(
                        'name'      => $material->material_name,
                        'density'   => $material->material_density,
                    );

                    $g++;
                }

            }

            $fix = array(
                'quantity' => 1,
                'material' => $material_id,
                'infill'   => 100,
                'scale'    => 100,
            );

            $items = $wpdb->get_results( "SELECT * FROM " . PQC_DATA_TABLE . " WHERE user_ip = '$user_ip' AND status = 'pending'" );

            if ( $items ) {

                $args = array(
                    'post_title'        => 'Auto Draft',
                    'post_name'         => '',
                    'post_status'       => 'auto-draft',
                    'post_type'         => 'page',
                    'comment_status'    => 'closed',
                    'ping_status'       => 'closed',
                    'post_date'         => current_time( 'mysql' ),
                );

                $order_id = wp_insert_post( $args );

                $firstname  = sanitize_text_field( $firstname );
                $lastname   = sanitize_text_field( $lastname );
                $email      = sanitize_email( $email );
                $address    = sanitize_text_field( $address );
                $city       = sanitize_text_field( $city );
                $zipcode    = sanitize_text_field( $zipcode );
                $state      = sanitize_text_field( $state );
                $order_note = sanitize_textarea_field( $order_note );

                $buyer_data = array(
                    'first_name'        => $firstname,
                    'last_name'         => $lastname,
                    'shipping_address'  => $address,
                    'city'              => $city,
                    'zipcode'           => $zipcode,
                    'state'             => $state,
                    'email'             => $email,
                );

                // Update the Buyer Data
                pqc_update_buyer_data( $buyer_data );

                $total_item = count( $items );

                $x = 0;

                $currency       = pqc_money_format_control()['currency'];
                $currency_pos   = pqc_money_format_control()['currency_pos'];

                foreach( $items as $item ) {

                    $id             = $item->ID;
                    $name           = $item->item_name;
                    $data           = maybe_unserialize( $item->item_data );
                    $data           = wp_parse_args( $data, $fix ); // Back Compat fix
                    $unique_id      = $item->unique_id;
                    $volume         = ceil( $data['volume'] );
                    $weight         = ceil( $data['weight'] );
                    $selected       = ! isset( $the_materials[absint( $data['material'] )] ) ? $material_id : absint( $data['material'] );
                    $density        = $the_materials[$selected]['density'];
                    $triangle       = $data['triangle'];
                    $quantity       = $data['quantity'];
                    $infill         = absint( $data['infill'] );
                    $scale          = absint( $data['scale'] );
                    $target_file    = PQC_CONTENT_DIR . $unique_id . ".stl";

                    $material_is_lost = ! isset( $the_materials[absint( $data['material'] )] ) ? true : false;

                    $data['material'] = $selected;

                    if ( ! file_exists( $target_file ) ) continue;

                    $calculate = $this->calculate( $data );

                    $the_items[$x]['unique_id'] = $unique_id;
                    $the_items[$x]['name']      = $name;
                    $the_items[$x]['volume']    = round( ( $scale * $volume ) / 100, 2, PHP_ROUND_HALF_UP );
                    $the_items[$x]['weight']    = round( ( $scale * $weight ) / 100, 2, PHP_ROUND_HALF_UP );
                    $the_items[$x]['density']   = round( ( $scale * $density ) / 100, 2, PHP_ROUND_HALF_UP );
                    $the_items[$x]['triangle']  = $triangle;
                    $the_items[$x]['url']       = PQC_CONTENT_URL . $unique_id . ".stl";
                    $the_items[$x]['quantity']  = $quantity;
                    $the_items[$x]['infill']    = $infill;
                    $the_items[$x]['scale']     = $scale;
                    $the_items[$x]['material']  = $the_materials[$selected]['name'];
                    $the_items[$x]['price']     = $calculate['price'];
                    $the_items[$x]['amount']    = $calculate['amount'];

                    $subtotal[] = $calculate['amount'];

                    $complete_ids[] = "ID = " . $id;

                    $delete[] = $unique_id;

                    $x++;

                    if ( $material_is_lost ) {

                        $place_order_error = true;

                        $place_order_msg = sprintf(
                            __( '<strong> Notice! </strong> Your are required to update your cart before you can checkout. <a href="%s">Go to cart</a>', 'pqc' ),
                            get_permalink( pqc_page_exists( 'pqc-cart' ) )
                        );

                        return;

                    }

                }

                $subtotal = array_sum( $subtotal );

                $cart_total = $subtotal;

                if ( pqc_get_current_user_coupon() ) {

                    $coupon_id      = (int) pqc_get_current_user_coupon();
                    $coupon_name    = pqc_get_coupon_name( $coupon_id );
                    $coupon_details = pqc_get_coupon_details( $coupon_id );
                    $old_subtotal   = $subtotal;
                    $subtotal       = pqc_apply_coupon( $coupon_id, $subtotal );

                    if ( $subtotal === false ) $subtotal = $old_subtotal;

                }

                $total = $subtotal;

                $shipping_options =  $this->get_shipping_options();

                $buyer_data = pqc_get_buyer_data();

                if ( $buyer_data && isset( $buyer_data['shipping_option'] ) && ! empty( $buyer_data['shipping_option'] ) ) {

                    $current_shipping_option_id = (int) $buyer_data['shipping_option'];

                    $current_shipping_option = $shipping_options[$current_shipping_option_id];

                    $shipping_option_cost = floatval( $current_shipping_option['amount'] );

                    $total = $subtotal + $shipping_option_cost;

                }

                // Place Order
                $item_data['firstname']         = $firstname;
                $item_data['lastname']          = $lastname;
                $item_data['email']             = $email;
                $item_data['address']           = $address;
                $item_data['city']              = $city;
                $item_data['zipcode']           = $zipcode;
                $item_data['state']             = $state;
                $item_data['note']              = $order_note;
                $item_data['txn_id']            = '';
                $item_data['order_action']      = 'new';
                $item_data['order_status']      = 'pending';
                $item_data['coupon']            = isset( $coupon_name ) ? $coupon_name : '';
                $item_data['coupon_amount']     = isset( $coupon_details ) && $coupon_details ? $coupon_details['amount'] : '';
                $item_data['coupon_type']       = isset( $coupon_details ) && $coupon_details ? $coupon_details['type'] : '';
                $item_data['payment_method']    = $payment_method;
                $item_data['shipping_option']   = isset( $current_shipping_option['title'] ) ? $current_shipping_option['title'] : '-';
                $item_data['shipping_cost']     = $shipping_option_cost;
                $item_data['cart_total']        = $cart_total;
                $item_data['subtotal']          = $subtotal;
                $item_data['total']             = $total;
                $item_data['currency']          = $currency;
                $item_data['currency_pos']      = $currency_pos;
                $item_data['items']             = $the_items;
                $item_data['user_ip']           = $user_ip;
                $item_data['user_id']           = is_user_logged_in() ? get_current_user_id() : '';
                $item_data['pay_for_order_id']  = uniqid( pqc_get_random_string() );
                $item_data['date']              = current_time( 'mysql' );

                $args = array(
                    'ID'                => $order_id,
                    'post_title'        => "#$order_id",
                    'post_status'       => 'publish',
                    'post_type'         => 'pqc_order',
                );

                wp_update_post( $args );

                // Insert Post Meta
                update_post_meta( $order_id, 'pqc_order_data', $item_data );

                if ( $checkout_option == 2 )
                    exit( wp_redirect( get_permalink( pqc_page_exists( 'pqc-orders' ) ) . "?order_request_sent=true&order_id=$order_id" ) );

                $this->load_payment_method( $order_id, $payment_method );

            }
            else {

                $notice = true;

                $notice_msg = sprintf(
                    __( '<strong> Sorry! </strong> You have no item in cart. <a href="%s">Add items</a>', 'pqc' ),
                    get_permalink( pqc_page_exists( 'pqc-upload' ) )
                );

            }

        }

    }

    private function complete_order() {

        extract( $_POST );

        if ( ! isset( $payment_method ) || empty( $payment_method ) ) {

            $GLOBALS['pqc_payment_message'] = __( '<strong> Error! </strong> No payment method selected.', 'pqc' );;

            add_filter( 'pqc_payment_response', array( $this, 'add_payment_response' ) );

            return;

        }

        if ( ! $this->is_pay_for_order() ) {

            $GLOBALS['pqc_payment_message'] = __( '<strong> Sorry! </strong> No pending order found for the order id.', 'pqc' );;

            add_filter( 'pqc_payment_response', array( $this, 'add_payment_response' ) );

            return;

        }

        $order_id           = intval( $_GET['order_id'] );
        $pay_for_order_id   = sanitize_text_field( $_GET['pay_for_order_id'] );
        $result             = pqc_get_order( $order_id );

        if ( $result ) {

            $data = maybe_unserialize( $result[0]->meta_value );

            if ( $data['order_status'] == 'pending' ) {

                if ( isset( $data['pay_for_order_id'] ) || $data['pay_for_order_id'] == $pay_for_order_id ) {

                    $data['payment_method'] = $payment_method;

                    $data['date'] = current_time( 'mysql' );

                    update_post_meta( $order_id, 'pqc_order_data', $data );

                }

            }

        }
        else {

            $GLOBALS['pqc_payment_message'] = __( '<strong> Sorry! </strong> No order found.', 'pqc' );;

            add_filter( 'pqc_payment_response', array( $this, 'add_payment_response' ) );

            return;
        }

        $this->load_payment_method( $order_id, $payment_method );

    }

    public function public_ajax_update() {

        ob_clean();

        if ( ! wp_verify_nonce( $_POST['nonce'], $this->ajax_action ) ) {

            $return = array(
                'type' => 'error',
                'msg' => __( 'No naughty business please', 'pqc' )
            );

        }
        elseif ( isset( $_POST['data'] ) ) {

            global $wpdb;

            $user_ip  = pqc_real_ip();

            parse_str( $_POST['data'] );

            if ( isset( $quantities ) && ! empty( $quantities ) && isset( $materials ) && ! empty( $materials ) ) {

                $items = $wpdb->get_results( "SELECT * FROM " . PQC_DATA_TABLE . " WHERE user_ip = '$user_ip' AND status = 'pending'" );

                if ( $items ) {

                    $the_data = array();

                    $the_items = array();

                    $subtotal = array();

                    $sql = array();

                    foreach( $items as $item ) $the_data[$item->ID] = maybe_unserialize( $item->item_data );

                    foreach( $quantities as $ID => $quantity ) {

                        $infills[$ID] = ! isset( $infills[$ID] ) ? 100 : $infills[$ID];

                        $scales[$ID] = ! isset( $scales[$ID] ) ? 100 : $scales[$ID];

                        if ( isset( $apply_coupon ) && $apply_coupon == 1 ) {

                            $the_data[$ID]['quantity'] = isset( $the_data[$ID]['quantity'] ) ? absint( $the_data[$ID]['quantity'] ) : 1;

                        }
                        else {

                            $the_data[$ID]['quantity'] = absint( $quantity ) < 1 ? 1 : absint( $quantity );

                        }

                        $the_data[$ID]['material'] = absint( $materials[$ID] );

                        $dens = $wpdb->get_var( "SELECT material_density FROM " . PQC_MATERIALS_TABLE . " WHERE ID = {$the_data[$ID]['material']}" );

                        $the_data[$ID]['density'] = $dens && ! empty( $dens ) && $dens != 0 ? (float) $dens : $the_data[$ID]['density'];

                        $calculate = $this->calculate( $the_data[$ID] );

                        $cost = pqc_money_format( $calculate['price'], null, true );
                        $total = pqc_money_format( $calculate['amount'], null, true );

                        /*
                        // Let's adjust volume, density and weight based on scale using our formula => x2 = ( s2 * x1 ) / s1
                        $the_items[$ID]['volume']   = $the_data[$ID]['volume']; // round( ( $the_data[$ID]['scale'] * $the_data[$ID]['volume'] ) / 100, 2, PHP_ROUND_HALF_UP );
                        $the_items[$ID]['density']  = $the_data[$ID]['density']; // round( ( $the_data[$ID]['scale'] * $the_data[$ID]['density'] ) / 100, 2, PHP_ROUND_HALF_UP );
                        $the_items[$ID]['weight']   = $the_data[$ID]['weight']; // round( ( $the_data[$ID]['scale'] * $the_data[$ID]['weight'] ) / 100, 2, PHP_ROUND_HALF_UP );
                        $the_items[$ID]['scale']    = $the_data[$ID]['scale'];
                        */
                        $the_items[$ID]['cost']     = $cost;
                        $the_items[$ID]['total']    = $total;

                        $subtotal[] = $calculate['amount'];

                        $the_data[$ID] = maybe_serialize( $the_data[$ID] );

                        $sql[] = "($ID,'$the_data[$ID]')";

                    }

                    $type = 'success';

                    $msg = __( 'Cart updated successfully.', 'pqc' );

                    $coupon_type = false;

                    $subtotal = array_sum( $subtotal );

                    // If coupon was sent, let's apply it
                    if ( ( isset( $apply_coupon ) && isset( $coupon ) ) && ( $apply_coupon == 1 && ! empty( $coupon ) ) ) {

                        $msg        = '';
                        $coupon     = sanitize_text_field( $coupon );
                        $coupon_id  = pqc_get_coupon_id( $coupon );

                        if ( $coupon_id ) {

                            $coupon_type    = 'success';
                            $coupon_msg     = __( 'Coupon applied successfully.', 'pqc' );

                            if ( ! pqc_get_current_user_coupon() || pqc_get_current_user_coupon() != $coupon_id ) {

                                $old_subtotal = $subtotal;

                                $subtotal = pqc_apply_coupon( absint( $coupon_id ), $subtotal );

                                if ( $subtotal === false ) {

                                    $subtotal = $old_subtotal;

                                    $coupon_type = 'error';

                                    $coupon_msg = __( 'Error! Coupon could not be applied.', 'pqc' );

                                }

                            }

                        } else {

                            $coupon_type = 'error';

                            if ( pqc_get_current_user_coupon() )
                                $coupon_msg = __( 'Invalid Coupon used, reverted to previous coupon used.', 'pqc' );
                            else
                                $coupon_msg = __( 'Invalid Coupon used.', 'pqc' );

                        }

                    }
                    elseif ( isset( $remove_coupon ) && $remove_coupon == 1 ) {

                        if ( pqc_get_current_user_coupon() ) {

                            $coupon_type = 'success';

                            $coupon_msg = __( 'Coupon has been removed.', 'pqc' );

                            $coupon_id = (int) pqc_get_current_user_coupon();

                            $this->delete_current_user_coupon();

                        }

                    }
                    elseif ( pqc_get_current_user_coupon() ) {

                        $coupon_type = 'success';

                        $coupon_msg = __( 'Coupon is being used. <a href="#" id="remove-coupon">remove coupon</a>', 'pqc' );

                        $coupon_id = (int) pqc_get_current_user_coupon();

                        $old_subtotal = $subtotal;

                        $subtotal = pqc_apply_coupon( absint( $coupon_id ), $subtotal );

                        if ( $subtotal === false ) $subtotal = $old_subtotal;

                    }

                    $total = $subtotal;

                    $subtotal = pqc_money_format( $subtotal, null, true );

                    $sql = implode( ',', $sql );

                    $update = $wpdb->query( "
                    INSERT INTO " . PQC_DATA_TABLE . " (ID,item_data)
                    VALUES $sql
                    ON DUPLICATE KEY UPDATE item_data = VALUES(item_data);
                    " );

                    $return = array(
                        'type'          => $type,
                        'the_items'     => $the_items,
                        'sub_total'     => $subtotal,
                        'msg'           => $msg,
                        'coupon_type'   => $coupon_type ?? '',
                        'coupon_msg'    => $coupon_msg ?? '',
                    );

                    $return = $return;

                }

            }

            $return = isset( $update ) ? $return : array( 'type' => 'error' );

            sleep(2); // Let's Wait for 2 sec

        }
        else {

            $return = array( 'type' => 'error' );
        }

        echo json_encode( $return );

        exit;

    }

    public function public_ajax_delete() {

        ob_clean();

        if ( ! wp_verify_nonce( $_POST['nonce'], $this->ajax_action ) ) {

            $return = array(
                'type' => 'error',
                'msg' => __( 'No naughty business please', 'pqc' )
            );

        }
        elseif ( isset( $_POST['unique_ids'] ) ) {

            global $wpdb;

            $user_ip  = pqc_real_ip();

            $delete = $this->delete_item( $_POST['unique_ids'] );

            if ( $delete ) {

                $items = $wpdb->get_results( "SELECT * FROM " . PQC_DATA_TABLE . " WHERE user_ip = '$user_ip' AND status = 'pending'" );;

                if ( $items ) {

                    $subtotal = array();

                    foreach( $items as $item ) {

                        $the_data = maybe_unserialize( $item->item_data );

                        $calculate = $this->calculate( $the_data );

                        $subtotal[] = $calculate['amount'];

                    }

                    $subtotal = array_sum( $subtotal );

                    if ( pqc_get_current_user_coupon() ) {

                        $coupon_type = 'success';

                        $coupon_msg = __( 'Coupon is being used. <a href="#" id="remove-coupon">remove coupon</a>', 'pqc' );

                        $coupon_id = (int) pqc_get_current_user_coupon();

                        $old_subtotal = $subtotal;

                        $subtotal = pqc_apply_coupon( $coupon_id, $subtotal );

                        if ( $subtotal === false ) $subtotal = $old_subtotal;

                    }

                    $subtotal = pqc_money_format( $subtotal, null, true );

                    $notice_msg = sprintf(
                        _n( '%s item removed from cart.', '%s items removed from cart.', count( $_POST['unique_ids'] ), 'pqc' ),
                        count( $_POST['unique_ids'] )
                    );

                }
                else {

                    $notice_msg = sprintf(
                        __( 'All item deleted successfully. <a href="%s">Add item to cart</a>', 'pqc' ),
                        get_permalink( pqc_page_exists( 'pqc-upload' ) )
                    );

                }

                $return = array(
                    'type'          => 'success',
                    'sub_total'     => $subtotal,
                    'msg'           => $notice_msg,
                    'coupon_type'   => $coupon_type ? $coupon_type : '',
                    'coupon_msg'    => $coupon_msg ? $coupon_msg : '',
                );

                $return = $return;

            }

            $return = isset( $delete ) ? $return : array( 'type' => 'error' );

            sleep(1); // Let's Wait for a sec

        }
        else {

            $return = array( 'type' => 'error' );
        }

        echo json_encode( $return );

        exit;

    }

    /**
    * Display the Upload page
    * @param mixed $args
    */
    public function upload( $args ) {

        $options = maybe_unserialize( get_option( PQC_SETTING_OPTIONS ) );

        extract( $options['pqc_general_settings'] );

        require_once PQC_PATH . 'templates/upload.php';

        wp_localize_script( PQC_NAME, 'PQC',
            array(
            'max_file_upload'   => $max_file_upload,
            'max_file_size'     => $max_file_size,
            )
        );

        wp_localize_script(
            PQC_NAME, 'PQC_Page',
            array(
                'page' => 1,
                'money_format_control' => pqc_money_format_control()
            )
        );

    }

    /**
    * Display the Cart page
    * @param mixed $args
    */
    public function cart( $args ) {

        global $wpdb;

        $user_ip = pqc_real_ip();

        if ( isset( $_POST['pqc_file_upload'] ) ) {

            $error          = false;
            $notice         = false;
            $settings       = maybe_unserialize( get_option( PQC_SETTING_OPTIONS ) );
            $files          = $_FILES['pqc_file'];

            // Check if exceeds Max. File Upload
            $limit = absint( $settings['pqc_general_settings']['max_file_size'] );

            if ( count( $files['name'] ) > $limit ) {

                $error = true;

                $error_msg = __( '<strong> Sorry! </strong> Max File upload is ' . $limit, 'pqc' );

            }
            elseif( count( $files['error'] ) == 1 && $files['error'][0] > 0 ) {

                $error = true;

                $error_msg = __( '<strong> Error Occurred! </strong>  ', 'pqc' ) . $this->file_upload_error( $files['error'][0] );

            }
            else {

                $the_files;

                for( $i = 0; $i < count( $files['name'] ); $i++ ) {

                    $the_files[$i]['name'] = $files['name'][$i];
                    $the_files[$i]['type'] = $files['type'][$i];
                    $the_files[$i]['tmp_name'] = $files['tmp_name'][$i];
                    $the_files[$i]['error'] = $files['error'][$i];
                    $the_files[$i]['size'] = $files['size'][$i];

                }

                if ( count( $files['name'] ) == count( $the_files ) ) {

                    foreach( $the_files as $file ) {

                        if ( isset( $file ) && $file['error'] == 0 ) {

                            $type           = pathinfo( $file['name'], PATHINFO_EXTENSION );
                            $unique_id      = pqc_get_random_string();
                            $name           = ucfirst( str_replace( array( '_', '-' ), array( ' ', ' ' ), basename( $file['name'], ".$type" ) ) );
                            $type           = strtolower( $type );
                            $file['name']   = $unique_id . ".$type";
                            $target_dir     = PQC_CONTENT_DIR;
                            $target_url     = PQC_CONTENT_URL . basename( $file['name'] );
                            $target_file    = $target_dir . basename( $file['name'] );
                            $allowed_types  = apply_filters( 'pqc_permitted_files', array( 'stl' ) );
                            $allowed_size   = (int) absint( $settings['pqc_general_settings']['max_file_size'] ) * 1000000; // In Bytes

                            // Check file extension
                            if ( ! in_array( strtolower( $type ), $allowed_types ) ) {

                                $error = true;

                                $error_msg = __( '<strong> Sorry! </strong> File type not supported.', 'pqc' );

                            }

                            // Check file size
                            if ( $file['size'] > $allowed_size ) {

                                $error = true;

                                $error_msg = __( '<strong> Oops! </strong> File too large.', 'pqc' );

                            }

                            // If no error, let's do the job
                            if ( ! $error ) {

                                if ( ! file_exists( $target_dir ) ) mkdir( $target_dir, 0777, true );

                                if ( ! file_exists( $target_file ) ) {

                                    $args = array(
                                        'tmp_name'      => $file["tmp_name"],
                                        'file_size'     => $file['size'],
                                        'type'          => $type,
                                        'unique_id'     => $unique_id,
                                        'name'          => $name,
                                        'user_ip'       => $user_ip,
                                    );

                                    $this->do_upload( $args );

                                } else {

                                    $args = array(
                                        'tmp_name'      => $file["tmp_name"],
                                        'file_size'     => $file['size'],
                                        'type'          => $type,
                                        'unique_id'     => pqc_get_random_string(),
                                        'name'          => $name,
                                        'user_ip'       => $user_ip,
                                    );

                                    $this->do_upload( $args );

                                }

                            }

                        }
                        elseif ( $file['error'] > 0 ) {

                            $error = true;

                            $error_msg = __( '<strong> Error Occurred! </strong>  ', 'pqc' ) . $this->file_upload_error( $file['error'] );
                        }
                        else {

                            $error = true;

                            $upload_error = true;

                            $error_files[] = $file['name'];

                        }

                    }

                    if ( isset( $upload_error ) ) {

                        $error_files = implode( ', ', $error_files );

                        $error_msg = __( '<strong> Oops! </strong> There was an error uploading ', 'pqc' ) . $error_files;

                    }

                }
                else {

                    $error = true;

                    $error_msg = __( '<strong> Sorry! </strong> Error Occurred while parsing files.', 'pqc' );

                }

            }

        }

        $this->display_cart();

        wp_localize_script(
            PQC_NAME, 'PQC_Page',
            array(
                'page' => 2,
                'money_format_control' => pqc_money_format_control()
            )
        );
    }

    /**
    * Display Checkout page
    * @param mixed $args
    */
    public function checkout( $args ) {

        global $pqc;

        /**
        if ( ! $pqc->has_valid_license() ) {
            ?>
            <div id="pqc-wrapper" class="container">

                <header class="codrops-header">

                    <div class="notice">
                        <p><?php _e( 'Sorry you cannot <strong> checkout/place order </strong> at this time. Contact the site administrator to resolve this.', 'pqc' ); ?></p>
                    </div>

                </header>

            </div>
            <?php
            return;
        }*/

        if ( $this->is_pay_for_order() )
            $this->pay_for_order();
        else
            $this->display_checkout();

        $settings = maybe_unserialize( get_option( PQC_SETTING_OPTIONS, array() ) );

        $checkout_option = $settings['pqc_checkout_settings']['checkout_option'];

        wp_localize_script(
            PQC_NAME, 'PQC_Page',
            array(
                'page' => 3,
                'money_format_control' => pqc_money_format_control(),
                'checkout_option' => $checkout_option,
            )
        );

    }

    public function orders() {

        global $wpdb, $wp_query;

        $user_ip = pqc_real_ip();

        $page = (int)( ! isset( $_GET["page_num"] ) ? 1 : $_GET["page_num"] );

        if ( $page <= 0 ) $page = 1;

        $per_page = 5;

        $startpoint = ( $page * $per_page ) - $per_page;

        $sql = "
        SELECT * FROM $wpdb->posts
        WHERE post_type = 'pqc_order'
        AND post_status = 'publish'
        ";

        if (
            isset( $_GET['order_action'] )
            && isset( $_GET['order_id'] )
            && $_GET['order_action'] == 'view-order'
            && ! empty( $_GET['order_id'] )
        ) {

            $order_id = intval( $_GET['order_id'] );

            $sql .= "AND ID = $order_id";

        }
        else {

            $sql .= "ORDER BY ID DESC";
            $sql .= " LIMIT $startpoint, $per_page";

        }

        $results = $wpdb->get_results( $sql );

        if ( $results ) {

            $the_orders = array();

            foreach( $results as $result ) {

                $id = $result->ID;

                $post_meta = get_post_meta( $id, 'pqc_order_data' )[0];

                if ( ! $post_meta || ! isset( $post_meta['user_ip'] ) || $post_meta['user_ip'] != $user_ip ) continue;

                $title = $result->post_title;

                $txn_id = '-';

                if (
                    $post_meta['order_status'] == 'pending' &&
                    isset( $post_meta['pay_for_order_id'] ) && ! empty( $post_meta['pay_for_order_id'] ) &&
                    isset( $post_meta['allow_payment'] ) && ! empty( $post_meta['allow_payment'] )
                )
                    {

                    $txn_id = sprintf(
                        '<a href="%s?pay_for_order=true&pay_for_order_id=%s&order_id=%s">Complete payment</a>',
                        get_permalink( pqc_page_exists( 'pqc-checkout' ) ),
                        $post_meta['pay_for_order_id'], $id
                    );

                }
                // var_dump( $post_meta );

                $args = wp_parse_args( array(
                    'id'            => $id,
                    'title'         => $title,
                    'txn_id'        => ! empty( $post_meta['txn_id'] ) ? $post_meta['txn_id'] : null,
                    'email'         => isset( $post_meta['email'] ) && ! empty( $post_meta['email'] ) ? $post_meta['email'] : '-',
                    'coupon_amount' => isset( $post_meta['coupon_amount'] ) && ! empty( $post_meta['coupon_amount'] ) ? $post_meta['coupon_amount'] : null,
                    'coupon_type'   => isset( $post_meta['coupon_type'] ) && ! empty( $post_meta['coupon_type'] ) ? $post_meta['coupon_type'] : null,
                    'ship_to'       => $post_meta['address'] . ', ' . $post_meta['city'] . ', ' . $post_meta['state'] . ' ' . $post_meta['zipcode'],
                    'currency_pos'  => isset( $post_meta['currency_pos'] ) ? $post_meta['currency_pos'] : null,
                    'date'          => ! isset( $post_meta['date'] ) ? $result->post_modified : $post_meta['date'],
                ), $post_meta );

                if ( isset( $order_id ) ) {

                    $fields['firstname']    = $args['firstname'];
                    $fields['lastname']     = $args['lastname'];
                    $fields['email']        = $args['email'];
                    $fields['address']      = $args['ship_to'];

                }

                /**
                $href = ! isset( $post_meta['pay_for_order_id'] ) || empty( $post_meta['pay_for_order_id'] ) ? '' :
                    sprintf(
                        'href="%s?pay_for_order=true&pay_for_order_id=%s&order_id=%s"',
                        get_permalink( pqc_page_exists( 'pqc-checkout' ) ),
                        $post_meta['pay_for_order_id'], $id
                    );

                printf(
                    '<a style="float: right; font-size: 13px;" target="_blank" %s %s>Complete Order</a>',
                    $href, $title
                );
                */

                $the_orders[$id] = $args;

            }

        }

        $total_item = ( isset( $the_orders ) && count( $the_orders ) > 0 ) ? count( $the_orders ) : 0;

        require_once PQC_PATH . 'templates/orders.php';

        wp_localize_script(
            PQC_NAME, 'PQC_Page',
            array(
                'page' => 4,
                'money_format_control' => pqc_money_format_control(),
            )
        );

    }

    private function is_pay_for_order() {

        if ( ! isset( $_GET['pay_for_order'] ) ) return false;

        if ( ! isset( $_GET['pay_for_order_id'] ) || empty( $_GET['pay_for_order_id'] ) ) return false;

        if ( ! isset( $_GET['order_id'] ) || intval( $_GET['order_id'] ) < 1 ) return false;

        return true;

    }

    private function pay_for_order() {

        $order_id           = intval( $_GET['order_id'] );
        $pay_for_order_id   = sanitize_text_field( $_GET['pay_for_order_id'] );
        $results            = pqc_get_order( $order_id );

        if ( $results ) {

            foreach( $results as $result ) {

                $data = maybe_unserialize( $result->meta_value );

                if ( $data['order_status'] != 'pending' ) continue;

                if ( ! isset( $data['pay_for_order_id'] ) || $data['pay_for_order_id'] != $pay_for_order_id ) continue;

                $the_items = $data['items'];

                extract( $data );

                break;
            }

            if ( ! isset( $the_items ) || ! $the_items ) {

                $notice = true;

                $notice_msg = __( '<strong> Sorry! </strong> No pending order found for the order id.', 'pqc' );

            }
            elseif ( ! isset( $data['allow_payment'] ) || $data['allow_payment'] != 1 ) {

                $notice = true;

                $notice_msg = __( '<strong> Sorry! </strong> You are not allowed to complete this order. Contact us to resolve this.', 'pqc' );

            }
            else {

                $total_item = count( $results );

                $s_options = array(
                    '1' => array( __( 'Zip Code', 'pqc' ), __( 'State', 'pqc' ) ),
                    '2' => array( __( 'Postal Code', 'pqc' ), __( 'County', 'pqc' ) ),
                );

                $settings           = maybe_unserialize( get_option( PQC_SETTING_OPTIONS, array() ) );
                $checkout_option    = $settings['pqc_checkout_settings']['checkout_option'];
                $shop_location      = isset( $settings['pqc_checkout_settings']['shop_location'] ) ? intval( $settings['pqc_checkout_settings']['shop_location'] ) : 1;
                $location_info      = $s_options[$shop_location];
                $shipping_cost      = pqc_money_format( $shipping_cost, $currency, true );
                $cart_total         = pqc_money_format( $cart_total, $currency, true );
                $subtotal           = pqc_money_format( $subtotal, $currency, true );
                $total              = pqc_money_format( $total, $currency, true );
                $currency_pos       = isset( $currency_pos ) ? $currency_pos : null;

            }

        }
        else {

            $notice = true;

            $notice_msg = __( '<strong> Sorry! </strong> No order found.', 'pqc' );
        }

        require_once PQC_PATH . 'templates/payorder.php';

    }

    /**
    * Display Cart
    */
    private function display_cart() {

        global $wpdb;

        $user_ip  = pqc_real_ip();

        $materials = $wpdb->get_results( "SELECT * FROM " . PQC_MATERIALS_TABLE );

        $g = 0;

        foreach ( $materials as $material ) {

            if ( $g == 0 ) $material_id = $material->ID;

            $the_materials[$material->ID] = array(
                'name'      => $material->material_name,
                'density'   => $material->material_density,
            );

            $g++;
        }

        $fix = array(
            'quantity'  => 1,
            'material'  => $material_id,
            'infill'    => 100,
            'scale'     => 100,
        );

        $items = $wpdb->get_results( "SELECT * FROM " . PQC_DATA_TABLE . " WHERE user_ip = '$user_ip' AND status = 'pending'" );

        if ( $items ) {

            $total_item = count( $items );

            $x = 0;

            $the_items = array();

            $subtotal = array();

            $target_urls = array();

            foreach( $items as $item ) {

                $id             = $item->ID;
                $name           = $item->item_name;
                $data           = maybe_unserialize( $item->item_data );
                $data           = wp_parse_args( $data, $fix ); // Back Compat fix
                $unique_id      = $item->unique_id;
                $volume         = $data['volume'];
                $weight         = $data['weight'];
                $selected       = ! isset( $the_materials[absint( $data['material'] )] ) ? $material_id : absint( $data['material'] );
                $density        = $the_materials[$selected]['density'];
                $triangle       = $data['triangle'];
                $quantity       = $data['quantity'];
                $infill         = absint( $data['infill'] );
                $scale          = absint( $data['scale'] );
                $target_url     = PQC_CONTENT_URL . $unique_id . ".stl";
                $target_file    = PQC_CONTENT_DIR . $unique_id . ".stl";

                $material_is_lost = ! isset( $the_materials[absint( $data['material'] )] ) ? true : false;

                $data['material'] = $selected;

                if ( file_exists( $target_file ) ) {

                    $calculate  = $this->calculate( $data );
                    $cost       = pqc_money_format( $calculate['price'], null, true );
                    $total      = pqc_money_format( $calculate['amount'], null, true );

                    $the_items[$x]['ID']        = $id;
                    $the_items[$x]['unique_id'] = $unique_id;
                    $the_items[$x]['name']      = $name;
                    $the_items[$x]['volume']    = round( ( $scale * $volume ) / 100, 2, PHP_ROUND_HALF_UP );
                    $the_items[$x]['weight']    = round( ( $scale * $weight ) / 100, 2, PHP_ROUND_HALF_UP );
                    $the_items[$x]['density']   = round( ( $scale * $density ) / 100, 2, PHP_ROUND_HALF_UP );
                    $the_items[$x]['triangle']  = $triangle;
                    $the_items[$x]['quantity']  = $quantity;
                    $the_items[$x]['infill']    = $infill;
                    $the_items[$x]['scale']     = $scale;
                    $the_items[$x]['cost']      = $cost;
                    $the_items[$x]['total']     = $total;
                    $the_items[$x]['url']       = $target_url;

                    $subtotal[]     = $calculate['amount'];
                    $target_urls[]  = $target_url;

                    $selected_material[$id] = array(
                        'id'    => $selected,
                        'name'  => $the_materials[$selected]['name'],
                        'density'  => $the_materials[$selected]['density'],
                    );

                    $x++;

                    if ( $material_is_lost ) {

                        $update_notice = true;

                        $update_notice_msg = __( '<strong> Notice! </strong> Your are required to update your cart. Click the update cart button.', 'pqc' );

                    }

                }
                else {

                    $notice = true;

                    $notice_msg = sprintf(
                        __( '<strong> Sorry! </strong> Your item does not exist anymore. <a href="%s">Add new item</a>', 'pqc' ),
                        get_permalink( pqc_page_exists( 'pqc-upload' ) )
                    );
                }

            }

            $subtotal = array_sum( $subtotal );

            if ( pqc_get_current_user_coupon() ) {

                $coupon_type = 'success';

                $coupon_msg = __( 'Coupon is being used. <a href="#" id="remove-coupon">remove coupon</a>', 'pqc' );

                $coupon_id = (int) pqc_get_current_user_coupon();

                $old_subtotal = $subtotal;

                $subtotal = pqc_apply_coupon( $coupon_id, $subtotal );

                if ( $subtotal === false ) $subtotal = $old_subtotal;

            }

            if ( $target_urls && ! empty( $target_urls ) ) {

                $total = $subtotal;

                $shipping_options =  $this->get_shipping_options();

                $buyer_data = pqc_get_buyer_data();

                $current_shipping_option_id = 0;

                $pqc_cdata = array(
                    'url'       => $target_urls,
                    'subtotal'  => $subtotal,
                );

                if ( $buyer_data && isset( $buyer_data['shipping_option'] ) && ! empty( $buyer_data['shipping_option'] ) ) {

                    $current_shipping_option_id = (int) $buyer_data['shipping_option'];

                    if ( $shipping_options || ! empty( $shipping_options ) ) {

                        $current_shipping_option = $shipping_options[$current_shipping_option_id];

                        $total = $subtotal + floatval( $current_shipping_option['amount'] );

                        $shipping_options = $shipping_options + array( 'shipping_set' => 1 );

                    }

                }
                else {

                    $shipping_options = $shipping_options || ! empty( $shipping_options ) ? $shipping_options + array( 'shipping_set' => 0 ) : array( 'shipping_set' => 0 );

                }

                wp_localize_script( PQC_NAME, 'PQC_Shipping', $shipping_options );

                wp_localize_script( PQC_NAME . '_STL', 'PQC', $pqc_cdata );

                unset( $shipping_options['shipping_set'] );

                $subtotal = pqc_money_format( $subtotal, null, true );

                $total = pqc_money_format( $total, null, true );

            }

        }
        elseif( isset( $_POST['pqc_file_upload'] ) && count( $_FILES['pqc_file']['error'] ) == 1 && $_FILES['pqc_file']['error'][0] > 0 ) {

            $error = true;

            $error_msg = sprintf(
                __( '<strong> Error Occurred! </strong> %1$s <a href="%2$s">Add item</a>', 'pqc' ),
                $this->file_upload_error( $_FILES['pqc_file']['error'][0] ),
                get_permalink( pqc_page_exists( 'pqc-upload' ) )
            );

        }
        else {

            $notice = true;

            $notice_msg = sprintf(
                __( '<strong> Sorry! </strong> You have no item in cart. <a href="%s">Add item</a>', 'pqc' ),
                get_permalink( pqc_page_exists( 'pqc-upload' ) )
            );

        }

        require_once PQC_PATH . 'templates/cart.php';

    }

    /**
    * Display Checkout
    */
    private function display_checkout() {

        global $wpdb;

        $user_ip  = pqc_real_ip();

        if ( isset( $_POST['pqc_proceed_checkout'] ) && isset( $_POST['shipping_option_id'] ) ) {

            $shipping_option_id = (int) $_POST['shipping_option_id'];

            $buyer_data = array( 'shipping_option' => $shipping_option_id );

            pqc_update_buyer_data( $buyer_data );

        }

        $materials = $wpdb->get_results( "SELECT * FROM " . PQC_MATERIALS_TABLE );

        $g = 0;

        foreach ( $materials as $material ) {

            if ( $g == 0 ) $material_id = $material->ID;

            $the_materials[$material->ID] = array(
                'name'      => $material->material_name,
                'density'   => $material->material_density,
            );

            $g++;
        }

        $fix = array(
            'quantity'  => 1,
            'material'  => $material_id,
            'infill'    => 100,
            'scale'     => 100,
        );

        $items = $wpdb->get_results( "SELECT * FROM " . PQC_DATA_TABLE . " WHERE user_ip = '$user_ip' AND status = 'pending'" );

        if ( $items ) {

            $total_item = count( $items );

            $x = 0;

            $the_items = array();

            $subtotal = array();

            $target_urls = array();

            foreach( $items as $item ) {
                $id             = $item->ID;
                $name           = $item->item_name;
                $data           = maybe_unserialize( $item->item_data );
                $data           = wp_parse_args( $data, $fix ); // Back Compat fix
                $unique_id      = $item->unique_id;
                $volume         = $data['volume'];
                $weight         = $data['weight'];
                $selected       = ! isset( $the_materials[absint( $data['material'] )] ) ? $material_id : absint( $data['material'] );
                $density        = $the_materials[$selected]['density'];
                $triangle       = $data['triangle'];
                $quantity       = $data['quantity'];
                $infill         = absint( $data['infill'] );
                $scale          = absint( $data['scale'] );
                $target_url     = PQC_CONTENT_URL . $unique_id . ".stl";
                $target_file    = PQC_CONTENT_DIR . $unique_id . ".stl";

                $material_is_lost = ! isset( $the_materials[absint( $data['material'] )] ) ? true : false;

                $data['material'] = $selected;

                if ( file_exists( $target_file ) ) {

                    $calculate  = $this->calculate( $data );
                    $cost       = pqc_money_format( $calculate['price'], null, true );
                    $total      = pqc_money_format( $calculate['amount'], null, true );

                    $the_items[$x]['ID']        = $id;
                    $the_items[$x]['unique_id'] = $unique_id;
                    $the_items[$x]['name']      = $name;
                    $the_items[$x]['volume']    = round( ( $scale * $volume ) / 100, 2, PHP_ROUND_HALF_UP );
                    $the_items[$x]['weight']    = round( ( $scale * $weight ) / 100, 2, PHP_ROUND_HALF_UP );
                    $the_items[$x]['density']   = round( ( $scale * $density ) / 100, 2, PHP_ROUND_HALF_UP );
                    $the_items[$x]['triangle']  = $triangle;
                    $the_items[$x]['quantity']  = $quantity;
                    $the_items[$x]['infill']    = $infill;
                    $the_items[$x]['scale']     = $scale;
                    $the_items[$x]['cost']      = $cost;
                    $the_items[$x]['total']     = $total;
                    $the_items[$x]['url']       = $target_url;

                    $subtotal[]     = $calculate['amount'];
                    $target_urls[]  = $target_url;

                    $selected_material[$id] = array(
                        'id'    => $selected,
                        'name'  => $the_materials[$selected]['name'],
                        'density'  => $the_materials[$selected]['density'],
                    );

                    $x++;

                    if ( $material_is_lost ) {

                        $update_notice = true;

                        $update_notice_msg = sprintf(
                            __( '<strong> Notice! </strong> Your are required to update your cart before you can checkout. <a href="%s">Go to cart</a>', 'pqc' ),
                            get_permalink( pqc_page_exists( 'pqc-cart' ) )
                        );

                    }

                }
                else {

                    $notice = true;

                    $notice_msg = sprintf(
                        __( '<strong> Sorry! </strong> Your item does not exist anymore. <a href="%s">Add new item</a>', 'pqc' ),
                        get_permalink( pqc_page_exists( 'pqc-upload' ) )
                    );
                }

            }

            $subtotal = array_sum( $subtotal );

            $cart_total = pqc_money_format( $subtotal, null, true );

            if ( pqc_get_current_user_coupon() ) {

                $coupon_msg = __( 'Coupon is being used.', 'pqc' );

                $coupon_id = (int) pqc_get_current_user_coupon();

                $coupon_name = pqc_get_coupon_name( $coupon_id );

                $old_subtotal = $subtotal;

                $subtotal = pqc_apply_coupon( $coupon_id, $subtotal );

                if ( $subtotal === false ) $subtotal = $old_subtotal;

            }

            $total = $subtotal;

            $shipping_options =  $this->get_shipping_options();

            $buyer_data = pqc_get_buyer_data();

            if ( $buyer_data && isset( $buyer_data['shipping_option'] ) && ! empty( $buyer_data['shipping_option'] ) ) {

                $current_shipping_option_id = (int) $buyer_data['shipping_option'];

                $current_shipping_option = $shipping_options[$current_shipping_option_id];

                $shipping_option_cost = floatval( $current_shipping_option['amount'] );

                $total = $subtotal + $shipping_option_cost;

                $shipping_option_set = true;

            }
            else {

                $shipping_option_set = false;

            }

            $subtotal = pqc_money_format( $subtotal, null, true );

            $total = pqc_money_format( $total, null, true );

        }
        else {

            $notice = true;

            $notice_msg = sprintf(
                __( '<strong> Sorry! </strong> You have no item in cart. <a href="%s">Add items</a>', 'pqc' ),
                get_permalink( pqc_page_exists( 'pqc-upload' ) )
            );

        }

        $settings = maybe_unserialize( get_option( PQC_SETTING_OPTIONS, array() ) );

        $s_options = array(
            '1' => array( __( 'Zip Code', 'pqc' ), __( 'State', 'pqc' ) ),
            '2' => array( __( 'Postal Code', 'pqc' ), __( 'County', 'pqc' ) ),
        );

        $checkout_option = $settings['pqc_checkout_settings']['checkout_option'];

        $shop_location = isset( $settings['pqc_checkout_settings']['shop_location'] ) ? intval( $settings['pqc_checkout_settings']['shop_location'] ) : 1;

        $location_info = $s_options[$shop_location];

        require_once PQC_PATH . 'templates/checkout.php';

    }

    private function get_shipping_cost( $shipping_id ) {

        $shipping_id = (int) $shipping_id;

        $meta = maybe_unserialize( get_post_custom_values( 'pqc_shipping_option_data', $shipping_id )[0] );

        return ( $meta && isset( $meta['amount'] ) ) ? floatval( $meta['amount'] ) : false;

    }

    private function get_shipping_options() {

        global $wpdb;

        $results = $wpdb->get_results( "
            SELECT * FROM $wpdb->posts
            WHERE post_type = 'pqc_shipping_option'
            AND post_status = 'publish';
        " );

        if ( ! $results ) return null;

        foreach ( $results as $result ) {

            $meta = maybe_unserialize( get_post_custom_values( 'pqc_shipping_option_data', $result->ID )[0] );

            $desc = $meta['description'];
            $amount = $meta['amount'];

            $values[$result->ID] = array(
                'ID'    => $result->ID,
                'title' => $result->post_title,
                'desc'  => $desc,
                'cost'  => pqc_money_format( $amount, null, true ),
                'amount'=> $amount,
            );

        }

        return $values;

    }

    /**
    * Deletes the current user coupon id
    */
    private function delete_current_user_coupon() {

        $buyer_data = array( 'coupon' => '' );

        return pqc_update_buyer_data( $buyer_data );

    }

    /**
    * Display all payment methods
    * @param mixed $item_data
    */
    public function payment_options( $item_data ) {

        global $pqc_payment_options;

        if ( empty( $pqc_payment_options ) ) return __( 'No checkout option available.', 'pqc' );

        foreach ( $pqc_payment_options as $key => $data ) {

            $label = $data['label'];

            $desc = '<div class="payment-options-description"><p>' . $data['desc'] . '</p></div>';

            $content = $label . $desc;

            echo '<li id="' . $key . '"><label><input value="' . $key . '" style="vertical-align: middle;" class="pqc-control" name="payment_method" type="radio">' . $content . '</label></li>';

        }
    }

    /**
    * Run the Payment Method function or file
    * @param string $item_date The Payment Method
    */
    public function load_payment_method( $post_id, $payment_method, $is_start = true ) {

        global $pqc_payment_options;

        if ( ! array_key_exists( $payment_method, $pqc_payment_options ) ) return;

        extract( $pqc_payment_options[$payment_method]['callback'] ); // Extract the callback of the payment method

        if ( ( ! isset( $url ) || empty( $url ) ) && ( ! isset( $start ) || empty( $start ) ) ) return;

        if ( ! $is_start && ( ! isset( $end ) || empty( $end ) ) ) return;

        $post_id = (int) $post_id;

        if ( ! empty( $url ) ) require_once $url;

        $function = $is_start ? $start : $end;

        if ( empty( $function ) || ! is_callable( $function, true ) ) return false;

        if ( is_array( $function ) ) {

            $obj = new $function[0]();

            $func = call_user_func_array( array( $obj, $function[1] ), array( $post_id ) ); // $obj->$function[1]( $post_id );

            if ( $func['error'] ) {

                $GLOBALS['pqc_payment_message'] = $func['response'];

                add_filter( 'pqc_payment_response', array( $this, 'add_payment_response' ) );

            }
        }
        else {

            $func = call_user_func( $function, $post_id ); // $function( $post_id );

            if ( $func['error'] ) {

                $GLOBALS['pqc_payment_message'] = $func['response'];

                add_filter( 'pqc_payment_response', array( $this, 'add_payment_response' ) );

            }

        }

    }

    public function add_payment_response() {

        return $GLOBALS['pqc_payment_message'];

    }

    /**
    * Provides plain-text error messages for file upload errors.
    * @param mixed $error_integer
    */
    private function file_upload_error( $error_integer ) {

        $upload_errors = array(
            UPLOAD_ERR_OK           => __( "No errors.", 'pqc' ),
            UPLOAD_ERR_INI_SIZE     => __( "File is larger than upload_max_filesize.", 'pqc' ),
            UPLOAD_ERR_FORM_SIZE    => __( "File is larger than form MAX_FILE_SIZE.", 'pqc' ),
            UPLOAD_ERR_PARTIAL      => __( "Partial upload.", 'pqc' ),
            UPLOAD_ERR_NO_FILE      => __( "No file added.", 'pqc' ),
            UPLOAD_ERR_NO_TMP_DIR   => __( "No temporary directory.", 'pqc' ),
            UPLOAD_ERR_CANT_WRITE   => __( "Can't write to disk.", 'pqc' ),
            UPLOAD_ERR_EXTENSION    => __( "File upload stopped by extension.", 'pqc' )
        );

        return $upload_errors[$error_integer];
    }

    /**
    * Calculate the cost for the uploaded item
    *
    * @param mixed $data The data having volume, weight, density, quantity, material etc.
    * @param object $materials The Material Object
    */
    private function calculate( $data ) {

        extract( $data );

        global $wpdb;

        $selected_material = $material;

        $options = maybe_unserialize( get_option( PQC_SETTING_OPTIONS ) );

        $pqc_general_settings = (object) $options['pqc_general_settings'];

        $initial_price  = $pqc_general_settings->initial_price;

        $price = $initial_price;

        $materials = $wpdb->get_results( "SELECT * FROM " . PQC_MATERIALS_TABLE );

        /**
        * Materials
        */
        if ( $materials ) {

            foreach( $materials as $material ) {

                if ( $material->ID != absint( $selected_material ) ) continue;

                /*
                $initial_scale  = 100;
                $final_scale    = $scale;
                */
                $initial_volume = $volume;
                $final_volume; // Unknown
                $initial_density = $density;
                $final_density; // Unknown

                // Let's get our final volume using our formula => x2 = ( s2 * x1 ) / s1
                $final_volume = floatval( $initial_volume ); // ceil( ( $final_scale * $initial_volume ) / $initial_scale );

                $price = $material->material_cost * $final_volume;

                // If charge by density is used
                if ( $pqc_general_settings->density_charge == 1 ) {

                    // Let's get our final density using our formula => x2 = ( s2 * x1 ) / s1
                    $final_density = floatval( $initial_density ); // floatval( ( $final_scale * $initial_density ) / $initial_scale );

                    $price = $price * $final_density;

                }

                break;
            }
        }

        /*
        // Calculate Infill
        $infill     = absint( $infill ) < 1 ? 1 : absint( $infill );
        $percent    = absint( 100 - $infill );
        $rate       = ( $percent / 100 ) * $price;
        $price      = floatval( $price - $rate );
        */

        // var_dump( $price );

        $price = pqc_number_format_raw( $price );

        // var_dump( $price );

        // Get the Amount
        $amount = floatval( round( $price, 2, PHP_ROUND_HALF_UP ) * absint( $quantity ) );

        $return = array(
            'price'     => round( $price, 2, PHP_ROUND_HALF_UP ),
            'amount'    => round( $amount, 2, PHP_ROUND_HALF_UP ),
        );

        return $return;

    }

    /**
    * Upload the file
    *
    * @param mixed $args
    * @param int $material_id
    */
    private function do_upload( $args ) {

        if ( empty( $args ) ) return false;

        global $wpdb;

        $materials = $wpdb->get_results( "SELECT * FROM " . PQC_MATERIALS_TABLE );

        foreach( $materials as $material ) {

            $material_id = $material->ID;

            $material_dens = $material->material_density;

            break;
        }

        parse_str( http_build_query( $args ), $parsed_data );

        extract( $parsed_data );

        $target_file = PQC_CONTENT_DIR . $unique_id . ".$type";

        $options = maybe_unserialize( get_option( PQC_SETTING_OPTIONS ) );

        $file_stay      = absint( $options['pqc_general_settings']['max_file_stay'] );
        $min_filevolume = floatval( $options['pqc_general_settings']['min_file_volume'] );

        require_once PQC_PATH . 'core/lib/STLStats.php';

        $sql= $wpdb->prepare(
            "SELECT * FROM " . PQC_DATA_TABLE . "
            WHERE user_ip = %s
            AND item_name = %s
            AND status = 'pending'",
        $user_ip, $name );

        $exist = $wpdb->get_row( $sql );

        if ( $exist ) {

            if ( filesize( PQC_CONTENT_DIR . $exist->unique_id . ".stl" ) == $file_size ) {

                $old_data = maybe_unserialize( $exist->item_data );

                $already_exists = true;

                $quantity = isset( $old_data['quantity'] ) ? absint( $old_data['quantity'] ) : 1;

                $infill = isset( $old_data['infill'] ) ? absint( $old_data['infill'] ) : 100;

                $scale = isset( $old_data['scale'] ) ? absint( $old_data['scale'] ) : 100;

                $target_file = PQC_CONTENT_DIR . $exist->unique_id . ".stl";

                $unique_id = $exist->unique_id;

                $material_id = isset( $old_data['material_id'] ) ? absint( $old_data['material_id'] ) : absint( $material_id );

                $material_dens = isset( $old_data['density'] ) ? floatval( $old_data['density'] ) : floatval( $material_dens );

            } else {

                $already_exists = false;

                move_uploaded_file( $tmp_name, $target_file );

            }

        } else {

            move_uploaded_file( $tmp_name, $target_file );
        }

        $obj = new STLStats( $target_file );

        $item_data = array(
            'volume'    => $obj->getVolume( "cm" ),
            'weight'    => $obj->getWeight(),
            'density'   => $material_dens,
            'triangle'  => $obj->getTrianglesCount(),
            'quantity'  => $exist && $already_exists ? $quantity + 1 : 1,
            'infill'    => $exist && $already_exists ? $infill : 100,
            'scale'     => $exist && $already_exists ? $scale : 100,
            'material'  => $material_id,
        );

        if ( is_nan( $item_data['volume'] ) || $item_data['volume'] < $min_filevolume ) {

            // Remove the file
            if ( file_exists( $target_file ) ) unlink( $target_file );

            return false;

        }

        $data = array(
            'unique_id'     => $unique_id,
            'item_name'     => $name,
            'user_ip'       => $user_ip,
            'item_data'     => maybe_serialize( $item_data ),
            'date_created'  => current_time( 'mysql' ),
            'expiry_date'   => date( 'Y-m-d h:i:s', strtotime( "+$file_stay days" ) ),
        );

        if ( ( $exist && ! $already_exists ) || ! $exist ) {

            $save = $wpdb->insert(
                PQC_DATA_TABLE,
                $data,
                array( '%s', '%s', '%s', '%s', '%s', '%s' )
            );

        } elseif ( $exist && $already_exists ) {

            $save = $wpdb->update(
                PQC_DATA_TABLE,
                $data,
                array( 'ID' => $exist->ID ),
                array( '%s', '%s', '%s', '%s', '%s' ),
                array( '%d' )
            );

        }

        return $save;

    }

    /**
    * Remove files and data
    * @param array $unique_ids
    * @since 1.6
    */
    private function delete_item( $unique_ids ) {

        global $wpdb;

        foreach( $unique_ids as $unique_id ) {

            $file = PQC_CONTENT_DIR . $unique_id . ".stl";

            if ( file_exists( $file ) ) unlink( $file );

            $sql = $wpdb->prepare(
                "DELETE FROM " . PQC_DATA_TABLE . "
                WHERE unique_id = %s
                AND status = 'pending'"
            , $unique_id );

            $delete = $wpdb->query( $sql );

        }

        return $delete;

        //$unique_ids = implode( "' OR unique_id = '", $unique_ids );

        //return $wpdb->query( "DELETE FROM $table WHERE unique_id = '$unique_ids'" );

    }

    /**
    * Prints the front end scripts
    * @since 1.0
    */
    public function public_scripts() {

        /**
        * Enqueue Styles
        */
        wp_enqueue_style( PQC_NAME, PQC_URL . 'assets/css/public.css', array(), PQC_VERSION, 'all' );
        wp_enqueue_style( 'jquery-ui', PQC_URL . 'assets/css/jquery-ui-base/jquery-ui.min.css', array(), '1.12.1', 'all' );
        wp_enqueue_style( 'fontawesome', PQC_URL . 'assets/css/font-awesome.min.css', array(), '4.7.0', 'all' );


        /**
        * Enqueue Scripts
        */
        wp_enqueue_script( 'jquery-ui', PQC_URL . 'assets/js/jquery-ui.min.js', array( 'jquery' ), '1.12.1', true );
        wp_enqueue_script( PQC_NAME . '_JSC3D', PQC_URL . 'assets/js/jsc3d.js', array( 'jquery' ), PQC_VERSION, true );
        wp_enqueue_script( PQC_NAME . '_JSC3D-CONSOLE', PQC_URL . 'assets/js/jsc3d.console.js', array( PQC_NAME . '_JSC3D' ), PQC_VERSION, true );

        if ( is_page( pqc_page_exists( 'pqc-cart' ) ) )
            wp_enqueue_script( PQC_NAME . '_STL', PQC_URL . 'assets/js/stl.js', array( PQC_NAME . '_JSC3D-CONSOLE' ), PQC_VERSION, true );

        wp_enqueue_script( PQC_NAME . ' URL SCRIPT', PQC_URL . 'assets/js/uri.min.js', array(), PQC_VERSION, true );
        wp_enqueue_script( PQC_NAME, PQC_URL . 'assets/js/public.js', array( PQC_NAME . '_JSC3D-CONSOLE' ), PQC_VERSION, true );
        wp_localize_script(
            PQC_NAME, 'PQC_Ajax',
            array(
                'url'       => admin_url( 'admin-ajax.php' ),
                'action'    => $this->ajax_action,
                'nonce'     => wp_create_nonce( $this->ajax_action ),
                'err_msg'   => __( 'Error occurred. Please Try again', 'pqc' ),
            )
        );

    }

}

endif;

if ( ! is_admin() || ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX )  ) new PQC_Public();
