<?php

// Modificación Phanes 3

/**
* Functions for 3DPC Quote Calculator
* @package 3DPC Quote Calculator
* @since 1.6
*/

if ( ! function_exists( 'pqc_time_difference' ) ) :
/**
* Returns the number of seconds/minutes/hour/days/weeks/months/year ago
*
* @param string $time_1 The datetime to parse
* @param string $time_2 The datetime to parse
* @param boolean $complete Whether to return the full time ago. Default is false.
* @param boolean $past Whether to return time ago or remaining time. Default is true.
*
* @since 1.1.0
* @since 1.6 The 'past' argument is now used
*/
function pqc_time_difference( $time_1, $time_2, $complete = false, $past = true ) {

    $time_1 = new DateTime( $time_1 ); // Initiate the DateInterval Class

    $time_2 = new DateTime( $time_2 ); // Initiate the DateInterval Class

    $diff = $time_1->diff( $time_2 );

    $diff->w = floor( $diff->d / 7 );

    $diff->d -= $diff->w * 7;

    $args = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );

    foreach ( $args as $key => &$value ) {

        if ( $diff->$key ) {

            $value = $diff->$key . ' ' . $value . ( $diff->$key > 1 ? 's' : '' );

        } else {

            unset( $args[$key] );
        }
    }

    if ( ! $complete ) $args = array_slice( $args, 0, 1 );

    if ( $past === true )
        $return = ( $args ) ? implode( ', ', $args ) . ' ago' : 'just now';
    else
        $return = ( $args ) ? implode( ', ', $args ) : 'a moment';

    return $return;
}
endif;

if ( ! function_exists( 'pqc_get_random_string' ) ) :
/**
* Create random string
*
* @param int $length The length of random string to get. Default is 20
*/
function pqc_get_random_string( $length = 20 ) {

    $characters = '56789abcdefghijklmABCDEFGHIJKLM01234nopqrstuvwxyzNOPQRSTUVWXYZ';

    $random_string = '';

    for ( $i = 0; $i < $length; $i++ ) {

        $random_string .= $characters[rand( 0, strlen( $characters ) - 1 )];

    }

    return $random_string;
}
endif;

if ( ! function_exists( 'pqc_page_exists' ) ) :
/**
* Check if page exist given the page name.
*
* Page must be published
*
* @param mixed $pagename. The page name to search for
* @return int The page ID if exist or 0 if not exist
*/
function pqc_page_exists( $pagename ) {

    global $wpdb;

    $sql = $wpdb->prepare( "
        SELECT ID
        FROM $wpdb->posts
        WHERE post_name = %s
        AND post_type = 'page'
        AND post_status = 'publish'
    ", $pagename );

    $page = (int) $wpdb->get_var( $sql );

    return ( $page === 0 ) ? 0 : $page;

}
endif;

if ( ! function_exists( 'pqc_force_redirect' ) ) :
/**
* Redirect to a webpage using javascript instead of wp_redirect
*
* @param mixed $url Url to redirect to
*/
function pqc_force_redirect( $url ) {

    echo "<script>window.location='$url'</script>";

}
endif;

if ( ! function_exists( 'pqc_real_ip' ) ) :
/**
* Get IP Address
*/
function pqc_real_ip() {

    $header_checks = array(
        'HTTP_CLIENT_IP',
        'HTTP_PRAGMA',
        'HTTP_XONNECTION',
        'HTTP_CACHE_INFO',
        'HTTP_XPROXY',
        'HTTP_PROXY',
        'HTTP_PROXY_CONNECTION',
        'HTTP_VIA',
        'HTTP_X_COMING_FROM',
        'HTTP_COMING_FROM',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'ZHTTP_CACHE_CONTROL',
        'REMOTE_ADDR'
    );

    foreach ( $header_checks as $key ) {

        if ( array_key_exists( $key, $_SERVER ) === true ) {

            foreach ( explode( ',', $_SERVER[$key] ) as $ip ) {

                $ip = trim( $ip );

                // Filter the ip with filter functions
                if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) !== false )
                    return $ip;
                else
                    return '127.0.0.1';
            }

        }

    }

}
endif;

if ( ! function_exists( 'pqc_number_format' ) ) :
/**
* Format a number with grouped thousands
*
* @param int|float(double) $number The number to be formatted
*/
function pqc_number_format( $number ) {

    $control = pqc_money_format_control();

    $number = empty( $number ) ? 0.00 : $number;

    $number = number_format( floatval( $number ), $control['decimals'], $control['decimal_sep'], $control['thousand_sep'] );

    return $number;

}
endif;

if ( ! function_exists( 'pqc_number_format_raw' ) ) :
/**
* Format a number without grouped thousands
*
* @param int|float(double) $number The number to be formatted
*/
function pqc_number_format_raw( $number ) {

    $control = pqc_money_format_control();

    $number = empty( $number ) ? 0.00 : $number;

    if ( $control['thousand_sep'] == '.' ) {

        if ( $control['decimal_sep'] == ',' && ( strpos( $number, ',' ) !== false || substr_count( $number, '.' ) > 1 ) ) {

            $number = str_replace( [ '.', ',' ], [ ',', '.' ], $number );

        }
        elseif ( $control['decimal_sep'] == '' ) {

            $number = str_replace( [ '.' ], [ ',' ], $number );

        }

    }
    else {

        $number = str_replace( [ $control['thousand_sep'], $control['decimal_sep'] ], [ '', '.' ], $number );

    }

    // $number = $control['decimals'] == 0 ? substr( $number, 0, strpos( $number, '.' ) ) : $number;
    $number = $control['decimals'] == 0 ? ceil( $number ) : $number;

    return floatval( $number );

}
endif;

if ( ! function_exists( 'pqc_money_format' ) ) :
/**
* Formats a number as a currency string
*
* @param int|float(double) $number The number to be formatted
* @param string $currency Optional Currency to use( Alpha ISO 4217 Code) , will be replaced with symbol if symbol is true
* @param bool $symbol Whether to use the currency symbol or not
* @param string $currency_pos Where to place the currency/symbol. Accepts 'left', 'right', 'left_space', 'right_space'
* @return string|bool Returns the formatted string or false $currency is not a string
*/
function pqc_money_format( $number, $currency = '', $symbol = false, $currency_pos = null ) {

    if ( ! empty( $currency ) && ! is_string( $currency ) ) return false;

    $control    = pqc_money_format_control();
    $number     = empty( $number ) ? 0.00 : floatval( $number );
    $currency   = empty( $currency ) ? $control['currency'] : $currency;
    $position   = empty( $currency_pos ) ? $control['currency_pos'] : $currency_pos;
    $symbol     = $symbol ? $control['currencies'][$currency]['symbol'] : $currency;

    $decimals       = $control['currencies'][$currency]['decimals'];
    $decimal_sep    = $control['currencies'][$currency]['decimal_sep'];
    $thousand_sep   = $control['currencies'][$currency]['thousand_sep'];
    $number         = number_format( $number, $decimals, $decimal_sep, $thousand_sep );

    switch ( $position ) {
        case 'left' :           $money = "$symbol$number"; break;
        case 'left_space' :     $money = "$symbol $number"; break;
        case 'right' :          $money = "$number$symbol"; break;
        case 'right_space' :    $money = "$number $symbol"; break;
        default :               $money = "$symbol$number"; break;

    }

    return $money;

}
endif;

if ( ! function_exists( 'pqc_money_format_control' ) ) :
/**
* Return PQC Money formating strings
*
*/
function pqc_money_format_control() {

    $settings = get_option( PQC_SETTING_OPTIONS, array() );

    $options = $settings['pqc_general_settings'];

    extract( $options );

    $currencies = pqc_currencies();

    $symbol = $currencies[$currency]['symbol'];

    return array(
        'decimals'          => $currencies[$currency]['decimals'],
        'decimal_sep'       => $currencies[$currency]['decimal_sep'],
        'thousand_sep'      => $currencies[$currency]['thousand_sep'],
        'currency'          => $currency,
        'currency_symbol'   => $symbol,
        'currency_pos'      => $currency_pos,
        'currencies'        => $currencies,
    );

}
endif;

if ( ! function_exists( 'pqc_currencies' ) ) :
/**
* Returns PQC Supported currencies
* @since 2.1
* @return array
*/
function pqc_currencies() {

    /**
    * List of support currencies for
    * 1 - This type of currency does not support decimals.
    *
    * @var array
    */
    $currencies = array(
        'AUD' => [
            'name'          => 'Australian Dollar',
            'major'         => 'dollar',
            'minor'         => 'cent',
            'symbol'        => '$',
            'decimals'      => 2,
            'decimal_sep'   => '.',
            'thousand_sep'  => ' ',
        ],

        'BRL' => [
            'name'          => 'Brazilian Real',
            'major'         => 'real',
            'minor'         => 'centavo',
            'symbol'        => 'R$',
            'decimals'      => 2,
            'decimal_sep'   => ',',
            'thousand_sep'  => '.',
        ],

        'CAD' => [
            'name'          => 'Canadian Dollar',
            'major'         => 'dollar',
            'minor'         => 'cent',
            'symbol'        => '$',
            'decimals'      => 2,
            'decimal_sep'   => '.',
            'thousand_sep'  => ',',
        ],

        'CNY' => [
            'name'          => 'Chinese Yuan Renminbi',
            'major'         => 'yuan renminbi',
            'minor'         => 'jiao',
            'symbol'        => '¥',
            'decimals'      => 2,
            'decimal_sep'   => '.',
            'thousand_sep'  => ',',
        ],

        'CZK' => [
            'name'          => 'Czech Koruna',
            'major'         => 'koruna',
            'minor'         => 'haler',
            'symbol'        => 'Kč',
            'decimals'      => 2,
            'decimal_sep'   => ',',
            'thousand_sep'  => '.',
        ],

        'DKK' => [
            'name'          => 'Danish Krone',
            'major'         => 'krone',
            'minor'         => 'øre',
            'symbol'        => 'kr',
            'decimals'      => 2,
            'decimal_sep'   => ',',
            'thousand_sep'  => '.',
        ],

        'EUR' => [
            'name'          => 'Euro',
            'major'         => 'euro',
            'minor'         => 'cent',
            'symbol'        => '€',
            'decimals'      => 2,
            'decimal_sep'   => '.',
            'thousand_sep'  => ',',
        ],

        'HKD' => [
            'name'          => 'Hong Kong Dollar',
            'major'         => 'dollar',
            'minor'         => 'cent',
            'symbol'        => '$',
            'decimals'      => 2,
            'decimal_sep'   => '.',
            'thousand_sep'  => ',',
        ],

        'HUF' => [
            'name'          => 'Hungarian Forint',
            'major'         => 'forint',
            'minor'         => '',
            'symbol'        => 'Ft',
            'decimals'      => 0,
            'decimal_sep'   => '',
            'thousand_sep'  => '.',
        ], // 1

        'ISL' => [
            'name'          => 'Israeli New Shekel',
            'major'         => 'new shekel',
            'minor'         => 'agora',
            'symbol'        => '₪',
            'decimals'      => 2,
            'decimal_sep'   => '.',
            'thousand_sep'  => ',',
        ],

        'JPY' => [
            'name'          => 'Japanese Yen',
            'major'         => 'yen',
            'minor'         => 'sen',
            'symbol'        => '¥',
            'decimals'      => 0,
            'decimal_sep'   => '',
            'thousand_sep'  => ',',
        ], // 1

        'MYR' => [
            'name'          => 'Malaysian Ringgit',
            'major'         => 'ringgit',
            'minor'         => 'sen',
            'symbol'        => 'RM',
            'decimals'      => 2,
            'decimal_sep'   => '.',
            'thousand_sep'  => ',',
        ],

        'MXN' => [
            'name'          => 'Mexican Peso',
            'major'         => 'peso',
            'minor'         => 'centavo',
            'symbol'        => '$',
            'decimals'      => 2,
            'decimal_sep'   => '.',
            'thousand_sep'  => ',',
        ],

        'NZD' => [
            'name'          => 'New Zealand Dollar',
            'major'         => 'dollar',
            'minor'         => 'cent',
            'symbol'        => '$',
            'decimals'      => 2,
            'decimal_sep'   => '.',
            'thousand_sep'  => ',',
        ],

        'NOK' => [
            'name'          => 'Norwegian Krone',
            'major'         => 'krone',
            'minor'         => 'øre',
            'symbol'        => 'kr',
            'decimals'      => 2,
            'decimal_sep'   => ',',
            'thousand_sep'  => '.',
        ],

        'PHP' => [
            'name'          => 'Philippine Peso',
            'major'         => 'peso',
            'minor'         => 'centavo',
            'symbol'        => '₱',
            'decimals'      => 2,
            'decimal_sep'   => '.',
            'thousand_sep'  => ',',
        ],

        'PLN' => [
            'name'          => 'Polish Zloty',
            'major'         => 'zloty',
            'minor'         => 'grosz',
            'symbol'        => 'zł',
            'decimals'      => 2,
            'decimal_sep'   => ',',
            'thousand_sep'  => ' ',
        ],

        'GBP' => [
            'name'          => 'Pound Sterling',
            'major'         => 'pound',
            'minor'         => 'pence',
            'symbol'        => '£',
            'decimals'      => 2,
            'decimal_sep'   => '.',
            'thousand_sep'  => ',',
        ],

        'RUB' => [
            'name'          => 'Russian Ruble',
            'major'         => 'ruble',
            'minor'         => 'kopeck',
            'symbol'        => '₽',
            'decimals'      => 2,
            'decimal_sep'   => ',',
            'thousand_sep'  => '.',
        ],

        'SGD' => [
            'name'          => 'Singapore Dollar',
            'major'         => 'dollar',
            'minor'         => 'cent',
            'symbol'        => '$',
            'decimals'      => 2,
            'decimal_sep'   => '.',
            'thousand_sep'  => ',',
        ],

        'SEK' => [
            'name'          => 'Swedish Krona',
            'major'         => 'krona',
            'minor'         => 'öre',
            'symbol'        => 'kr',
            'decimals'      => 2,
            'decimal_sep'   => ',',
            'thousand_sep'  => ' ',
        ],

        'CHF' => [
            'name'          => 'Swiss Franc',
            'major'         => 'franken',
            'minor'         => 'rappen',
            'symbol'        => 'CHF',
            'decimals'      => 2,
            'decimal_sep'   => '.',
            'thousand_sep'  => "'",
        ],

        'TWD' => [
            'name'          => 'Taiwan New Dollar',
            'major'         => 'new dollar',
            'minor'         => 'cent',
            'symbol'        => 'NT$',
            'decimals'      => 0,
            'decimal_sep'   => '',
            'thousand_sep'  => ',',
        ], // 1

        'THB' => [
            'name'          => 'Thai baht',
            'major'         => 'baht',
            'minor'         => 'satang',
            'symbol'        => '฿',
            'decimals'      => 2,
            'decimal_sep'   => '.',
            'thousand_sep'  => ',',
        ],

        'USD' => [
            'name'          => 'United States Dollar',
            'major'         => 'dollar',
            'minor'         => 'cent',
            'symbol'        => '$',
            'decimals'      => 2,
            'decimal_sep'   => '.',
            'thousand_sep'  => ',',
        ],
    );

    return $currencies;

}
endif;

if ( ! function_exists( 'pqc_is_paypal_ready' ) ) :
/**
 * Check if PayPal has been setup
 * @param bool $is_active To check if is active
 * @return bool true if ready and false on failure or PayPal is not ready
 */
function pqc_is_paypal_ready( $is_active = false ) {

    $options = maybe_unserialize( get_option( PQC_SETTING_OPTIONS, false ) );

    if ( ! $options || ( $options['pqc_checkout_settings']['paypal_active'] == 0 && $is_active ) ) return false;

    $client_id = $options['pqc_checkout_settings']['paypal_client_id'];
    $client_secret_key = $options['pqc_checkout_settings']['paypal_client_secret_key'];

    return (
    !isset( $client_id )
    || empty( $client_id )
    || !isset( $client_secret_key )
    || empty( $client_secret_key )
    ) ? false : true;

}
endif;

if ( ! function_exists( 'pqc_is_stripe_ready' ) ) :
/**
 * Check if Stripe has been setup
 * @param bool $is_active To check if is active
 * @return bool true if ready and false on failure or Stripe is not ready
 */
function pqc_is_stripe_ready( $is_active = false ) {

    $options = maybe_unserialize( get_option( PQC_SETTING_OPTIONS, false ) );

    if ( ! $options || ( $options['pqc_checkout_settings']['stripe_active'] == 0 && $is_active ) ) return false;

    $secret_key = $options['pqc_checkout_settings']['stripe_secret_key'];
    $publishable_key = $options['pqc_checkout_settings']['stripe_publishable_key'];

    return (
    !isset( $publishable_key )
    || empty( $publishable_key )
    || !isset( $secret_key )
    || empty( $secret_key )
    ) ? false : true;

}
endif;

if ( ! function_exists( 'pqc_apply_coupon' ) ) :
/**
* Apply given coupon to cart subtotal
*
* @param mixed $coupon_id
* @param mixed $subtotal
*
* @return bool|double false if not applied and new subtotal if applied
*/
function pqc_apply_coupon( $coupon_id, $subtotal ) {

    if ( ! is_int( $coupon_id ) ) return $subtotal;

    $coupon_data = maybe_unserialize( get_post_meta( $coupon_id, 'pqc_coupon_data' ) );

    if ( ! $coupon_data || empty( $coupon_data ) ) return $subtotal;

    $coupon_data = $coupon_data[0];

    $type = $coupon_data['type'];

    $expiry_date = $coupon_data['expiry_date'];

    $update = false;

    // Let's check if coupon have expire
    if ( strtotime( $expiry_date ) < strtotime( current_time( 'Y-m-d' ) ) ) return false;

    if ( $type == 'fixed_cart' ) {

        $subtotal = floatval( $subtotal - floatval( $coupon_data['amount'] ) );

        $update = pqc_get_current_user_coupon() == $coupon_id ? $subtotal : pqc_update_current_user_coupon( $coupon_id );

    }
    elseif( $type == 'percent' ) {

        $percent = ( floatval( $coupon_data['amount'] ) / 100 ) * $subtotal;

        $subtotal = floatval( $subtotal - floatval( $percent ) );

        $update = pqc_get_current_user_coupon() == $coupon_id ? $subtotal : pqc_update_current_user_coupon( $coupon_id );

    }

    return $update !== false ? $subtotal : false;

}
endif;

if ( ! function_exists( 'pqc_get_coupon_details' ) ) :
/**
* Return the coupon details
*
* @param mixed $coupon_id
*
* @return bool|double false if error occurred, no coupon found
*/
function pqc_get_coupon_details( $coupon_id ) {

    if ( ! is_int( $coupon_id ) ) return false;

    $coupon_data = maybe_unserialize( get_post_meta( $coupon_id, 'pqc_coupon_data' ) );

    if ( ! $coupon_data || empty( $coupon_data ) ) return false;

    $coupon_data = $coupon_data[0];

    $type = $coupon_data['type'];

    if ( $type == 'fixed_cart' ) {

        $amount = floatval( $coupon_data['amount'] );

    }
    elseif( $type == 'percent' ) {

        $amount = floatval( $coupon_data['amount'] ) / 100;

    }

    return isset( $amount ) ? array(
        'amount'    => $amount,
        'type'      => $type,
    ) : false;

}
endif;

if ( ! function_exists( 'pqc_get_current_user_coupon' ) ) :
/**
* Return the current user coupon id
*/
function pqc_get_current_user_coupon() {

    $buyer_data = pqc_get_buyer_data();

    $coupon = $buyer_data['coupon'];

    if ( ! $coupon ) return false;

    // Let's check if coupon have expire
    $post_meta = get_post_meta( absint( $coupon ), 'pqc_coupon_data' );

    if ( ! $post_meta || empty( $post_meta ) ) { pqc_update_current_user_coupon( absint( $coupon ) ); return false; }

    $expiry_date = $post_meta[0]['expiry_date'];

    pqc_update_current_user_coupon( absint( $coupon ) );

    return ( strtotime( $expiry_date ) > strtotime( current_time( 'Y-m-d' ) ) ) ? absint( $coupon ) : false;

}
endif;

if ( ! function_exists( 'pqc_update_current_user_coupon' ) ) :
/**
* Updates the current user coupon id
*
* @param int $coupon_id
*/
function pqc_update_current_user_coupon( $coupon_id ) {

    $coupon_id = (int) $coupon_id;

    // Let's check if coupon have expire
    $post_meta = get_post_meta( $coupon_id, 'pqc_coupon_data' );

    if ( ! $post_meta || empty( $post_meta ) ) $coupon_id = '';

    $expiry_date = $post_meta[0]['expiry_date'];

    $coupon_id = ( strtotime( $expiry_date ) > strtotime( current_time( 'Y-m-d' ) ) ) ? $coupon_id : '';

    $buyer_data = array( 'coupon' => $coupon_id );

    return ( empty( $coupon_id ) ) ? false : pqc_update_buyer_data( $buyer_data );

}
endif;

if ( ! function_exists( 'pqc_get_buyer_data' ) ) :
/**
* Returns Current Buyer Data
*
* @return array The current user pqc data
*/
function pqc_get_buyer_data() {

    $default = array(
        'coupon'            => '',
        'shipping_option'   => '',
        'first_name'        => '',
        'last_name'         => '',
        'shipping_address'  => '',
        'city'              => '',
        'zipcode'           => '',
        'state'             => '',
    );

    $data = get_option( 'pqc_buyer_data_' . pqc_real_ip(), array() );

    if ( is_user_logged_in() ) {

        $data = get_user_meta( get_current_user_id(), 'pqc_buyer_data', false );
        $data = ( $data || ! empty( $data ) ) ? $data[0] : array();

    }

    return ( ! $data || empty( $data ) ) ? $default : $data;

}
endif;

if ( ! function_exists( 'pqc_update_buyer_data' ) ) :
/**
* Updates Buyer data
*
* @param array $data
*/
function pqc_update_buyer_data( $data ) {

    if ( ! is_array( $data ) ) return false;

    $default = pqc_get_buyer_data();

    $args = wp_parse_args( $data, $default );

    if ( pqc_is_array_equal( $args, $default ) ) return true;

    if ( is_user_logged_in() )
        return update_user_meta( get_current_user_id(), 'pqc_buyer_data', $args );
    else
        return update_option( 'pqc_buyer_data_' . pqc_real_ip(), $args );

}
endif;

if ( ! function_exists( 'pqc_is_array_equal' ) ) :
/**
* Check if array two arrays are equal without considering the key order
*
* @param array $array1
* @param array $array2
*
* @return bool
*/
function pqc_is_array_equal( $array1, $array2 ) {

    if ( ! is_array( $array1 ) || ! is_array( $array2 ) ) return false;

    if ( array_diff_assoc( $array1, $array2 ) === array_diff_assoc( $array2, $array1 ) )
        return true;
    else
        return false;

}
endif;

if ( ! function_exists( 'pqc_get_coupon_id' ) ) :
/**
* Return coupon id
*
* @param string $coupon The Coupon
* @return int|bool The ID on success and false on failure
*/
function pqc_get_coupon_id( $coupon ) {

    global $wpdb;

    $coupon_sql = $wpdb->prepare(
        "SELECT ID FROM $wpdb->posts
        WHERE post_title = %s
        AND post_type = 'pqc_coupon'
        AND post_status = 'publish' LIMIT 1
        ", $coupon
    );

    $coupon_id = $wpdb->get_var( $coupon_sql );

    return $coupon_id ? absint( $coupon_id ) : false;

}
endif;

if ( ! function_exists( 'pqc_get_coupon_name' ) ) :
/**
* Return coupon name|title
*
* @param string $coupon The Coupon ID
* @return string|bool The title|name on success and false on failure
*/
function pqc_get_coupon_name( $coupon_id ) {

    global $wpdb;

    $coupon_sql = $wpdb->prepare(
        "SELECT post_title FROM $wpdb->posts
        WHERE ID = %d
        AND post_type = 'pqc_coupon'
        AND post_status = 'publish' LIMIT 1
        ", $coupon_id
    );

    $coupon = $wpdb->get_var( $coupon_sql );

    return $coupon ? $coupon : false;

}
endif;

if ( ! function_exists( 'pqc_get_order' ) ) :
function pqc_get_order( $order_id = 0 ) {

    global $wpdb;

    $group = "
    GROUP BY $wpdb->postmeta.post_id
    ORDER BY $wpdb->postmeta.post_id DESC
    ";

    $sql = "SELECT meta_value FROM $wpdb->postmeta
    INNER JOIN $wpdb->posts
    ON $wpdb->postmeta.post_id = $wpdb->posts.ID
    WHERE $wpdb->posts.post_type = 'pqc_order'
    AND $wpdb->posts.post_status = 'publish'
    AND $wpdb->postmeta.meta_key = 'pqc_order_data'
    ";

    if ( $order_id || ! empty( $order_id ) ) {

        $join = "AND $wpdb->postmeta.post_id = $order_id";

        $sql .= $join;

    }

    $sql .= $group;

    $results = $wpdb->get_results( $sql );

    return ! $results ? false : $results;

}
endif;

if ( ! function_exists( 'pqc_new_orders' ) ) :
function pqc_new_orders() {

    if ( ! pqc_get_order() ) return false;

    $args = array();

    foreach( pqc_get_order() as $result ) {

        $data = maybe_unserialize( $result->meta_value );

        if ( $data['order_action'] != 'new' ) continue;

        $args[] = $data;

    }

    return $args;

}
endif;

if ( ! function_exists( 'pqc_send_order_email' ) ) :
/**
* The Order ID
*
* @param int $order_id
*/
function pqc_send_order_email( $order_id ) {

    $results = pqc_get_order( $order_id );

    if ( $results ) {

        foreach( $results as $result ) {

            $data = maybe_unserialize( $result->meta_value );

            extract( $data );

            $currency_pos = isset( $currency_pos ) ? $currency_pos : null;

            $fields['firstname']    = $firstname;
            $fields['lastname']     = $lastname;
            $fields['email']        = $email;
            $fields['address']      = "$address, $city, $state $zipcode";

            $blog_name  = get_bloginfo( 'name', 'display' );
            $blog_url   = get_bloginfo( 'url', 'display' );
            $from       = get_bloginfo( 'admin_email' );
            $subject    = sprintf( __( 'Your %s order receipt from %s', 'pqc' ), $blog_name, date( 'F j, Y', strtotime( $date ) ) );

            ob_start();

            require_once PQC_PATH . 'templates/email/header.php';

            printf( __( "<h3>Hi %s,</h3>Thank you for your order. Your order details are shown below for your reference:", 'pqc' ), "$firstname $lastname" );

            require_once PQC_PATH . 'templates/email/order-details.php';

            require_once PQC_PATH . 'templates/email/footer.php';

            $template   = ob_get_clean();
            $message    = html_entity_decode( wordwrap( $template, 70, "\r\n" ) );

            $headers    = "From: $blog_name <$from>" . "\r\n";
            $headers    .= "Reply-To: noreply@$blog_url" . "\r\n";
            $headers    .= "Content-type: text/html; charset=UTF-8" . "\r\n";

            wp_mail( $email, $subject, $message, $headers );

            break;
        }

    }

}
endif;

if ( ! function_exists( 'pqc_pagination' ) ) :
/**
* Return pagination for orders page
*
* @param mixed $query
* @param mixed $per_page
* @param mixed $page
* @param mixed $page_text
*/
function pqc_orders_pagination( $per_page = 5, $page = 1, $page_text = null, $url = null ) {

    global $wpdb;

    $user_ip = pqc_real_ip();

    $sql = "
    SELECT * FROM $wpdb->posts
    WHERE post_type = 'pqc_order'
    AND post_status = 'publish'
    ";
    $results = $wpdb->get_results( $sql );

    $total = count( $results );

    if ( $results ) {

        foreach( $results as $result ) {

            $id = $result->ID;

            $post_meta = get_post_meta( $id, 'pqc_order_data' )[0];

            if ( ! $post_meta || ! isset( $post_meta['user_ip'] ) || $post_meta['user_ip'] != $user_ip ) $total -= 1;

        }

    }

    $adjacents = "2";

    $prevlabel = "&lang;";
    $nextlabel = "&rang;";
    $lastlabel = "&rsaquo;";

    $page = $page == 0 ? 1 : $page;
    $start = ($page - 1) * $per_page;

    $prev = $page - 1;
    $next = $page + 1;

    $lastpage = ceil( $total / $per_page );

    $lpm1 = $lastpage - 1;

    $pagination = "";

    $url = empty( $url ) ? $_SERVER['REQUEST_URI'] : $url;

    $page_text = empty( $page_text ) ? 'page_num' : $page_text;

    if ( $lastpage > 1 ) {

        $pagination .= "<ul>";
        $pagination .= "<li class='page_info'>Page {$page} of {$lastpage}</li>";

        if ( $page > 1 )
            $pagination .= "<li><a href='" . esc_url( add_query_arg( $page_text, $prev, $url ) ) . "'>$prevlabel</a></li>";

        if ( $lastpage < 7 + ( $adjacents * 2 ) ) {

            for ( $counter = 1; $counter <= $lastpage; $counter++ ) {

                if ( $counter == $page )
                    $pagination .= "<li><a class='active'>$counter</a></li>";
                else
                    $pagination .= "<li><a href='" . esc_url( add_query_arg( $page_text, $counter, $url ) ) . "'>$counter</a></li>";
            }

        } elseif ( $lastpage > 5 + ( $adjacents * 2 ) ) {

            if ( $page < 1 + ( $adjacents * 2 ) ) {

                for ( $counter = 1; $counter < 4 + ( $adjacents * 2 ); $counter++ ){

                    if ( $counter == $page )
                        $pagination .= "<li><a class='active'>$counter</a></li>";
                    else
                        $pagination .= "<li><a href='" . esc_url( add_query_arg( $page_text, $counter, $url ) ) . "'>$counter</a></li>";
                }

                $pagination .= "<li><a class='more'>&hellip;</a></li>";
                $pagination .= "<li><a href='" . esc_url( add_query_arg( $page_text, $lpm1, $url ) ) . "'>$lpm1</a></li>";
                $pagination .= "<li><a href='" . esc_url( add_query_arg( $page_text, $lastpage, $url ) ) . "'>$lastpage</a></li>";

            } elseif( $lastpage - ( $adjacents * 2 ) > $page && $page > ( $adjacents * 2 ) ) {

                $pagination .= "<li><a href='" . esc_url( add_query_arg( $page_text, 1, $url ) ) . "'>1</a></li>";
                $pagination .= "<li><a href='" . esc_url( add_query_arg( $page_text, 2, $url ) ) . "'>2</a></li>";
                $pagination .= "<li><a class='more'>&hellip;</a></li>";

                for ( $counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++ ) {

                    if ( $counter == $page )
                        $pagination .= "<li><a class='active'>$counter</a></li>";
                    else
                        $pagination .= "<li><a href='" . esc_url( add_query_arg( $page_text, $counter, $url ) ) . "'>$counter</a></li>";
                }

                $pagination.= "<li><a class='more'>..</a></li>";
                $pagination.= "<li><a href='" . esc_url( add_query_arg( $page_text, $lpm1, $url ) ) . "'>$lpm1</a></li>";
                $pagination.= "<li><a href='" . esc_url( add_query_arg( $page_text, $lastpage, $url ) ) . "'>$lastpage</a></li>";

            } else {

                $pagination .= "<li><a href='" . esc_url( add_query_arg( $page_text, 1, $url ) ) . "'>1</a></li>";
                $pagination .= "<li><a href='" . esc_url( add_query_arg( $page_text, 2, $url ) ) . "'>2</a></li>";
                $pagination .= "<li><a class='more'>..</a></li>";

                for ( $counter = $lastpage - ( 2 + ( $adjacents * 2 ) ); $counter <= $lastpage; $counter++ ) {
                    if ( $counter == $page )
                        $pagination .= "<li><a class='active'>$counter</a></li>";
                    else
                        $pagination .= "<li><a href='" . esc_url( add_query_arg( $page_text, $counter, $url ) ) . "'>$counter</a></li>";
                }
            }
        }

            if ( $page < $counter - 1 ) {

                $pagination .= "<li><a href='" . esc_url( add_query_arg( $page_text, $next, $url ) ) . "'>$nextlabel</a></li>";

                // $pagination .= "<li><a href='{$url}page=$lastpage'>{$lastlabel}</a></li>";
            }

        $pagination .= "</ul>";
    }

    return $pagination;
}
endif;

if ( ! function_exists( 'pqc_session_start' ) ) :
/**
 * Starts a php session
 * @return boolean|void
 */
function pqc_session_start()
{
	if (php_sapi_name() === 'cli') return false;

	if (version_compare(phpversion(), '5.4.0', '>=')) {
		$started = session_status() === PHP_SESSION_ACTIVE ? true : false;
	} else {
		$started = session_id() === '' ? false : true;
	}

	if ($started === false) {
		ob_start();
		session_start();
	}
}
endif;

if ( ! function_exists( 'PQC' ) ) :
/**
 * Main instance of PQC.
 *
 * Returns the main instance of PQC to prevent the need to use globals.
 *
 * @since  1.6
 * @return PQC
 */
function PQC() {
    if ( method_exists( 'PQC', 'instance' ) ) return PQC::instance();
}
endif;
