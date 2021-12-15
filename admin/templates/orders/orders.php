<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) die;

class PQC_Orders
{

    private $key = 'pqc_order';

    private $default_data = array(
        'actions'           => array( 'new', 'cancelled', 'processing', 'completed', 'refunded' ),
        'status'            => array( 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed' ),
        'items'             => array(),
        'payment_method'    => 'PayPal',
        'firstname'         => '',
        'lastname'          => '',
        'email'             => '',
        'payer_email'       => '',
        'address'           => '',
        'city'              => '',
        'zipcode'           => '',
        'state'             => '',
        'note'              => '',
        'order_action'      => '',
        'order_status'      => '',
        'coupon'            => '',
        'shipping_option'   => '',
        'shipping_cost'     => 0.00,
        'allow_payment'     => 0,
        'cart_total'        => '',
        'subtotal'          => '',
        'total'             => '',
        'currency'          => '',
        'currency_pos'      => null,
        'user_ip'           => '',
        'user_id'           => '',
        'date'              => '',
    );

    /**
    * The Constructor
    *
    */
    public function __construct()
	{
        // Action Before initialization
        do_action( 'pqc_order_before_init' );

        $this->init();

        // Action After initialization
        do_action( 'pqc_order_after_init' );
    }

    /**
    * Initialize Order
    *
    */
    private function init()
	{
        // Register Order Post Type
        $this->register_order();

        add_action( 'admin_head', array( $this, 'admin_head' ) );
        add_action( 'save_post_' . $this->key, array( $this, 'metabox_save' ), 10, 3 );
        add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );
        add_filter( 'bulk_post_updated_messages', array( $this, 'bulk_updated_messages' ), 10, 2 );
        add_filter( 'bulk_actions-edit-' . $this->key, array( $this, 'bulk_actions' ) );
        add_filter( 'manage_edit-' . $this->key . '_columns', array( $this, 'edit_columns' ) );
        add_filter( 'manage_edit-' . $this->key . '_sortable_columns', array( $this, 'sortable_columns' ) );
        add_action( 'manage_' . $this->key . '_posts_custom_column', array( $this, 'custom_columns' ) );
        add_action( 'add_meta_boxes_' . $this->key, array( $this, 'metabox' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 999 );
    }

    /**
    * Register Order Post type
    *
    */
    private function register_order()
	{
        $labels = array(
            'name'                  => _x( 'Orders', 'Post Type General Name', 'pqc' ),
            'singular_name'         => _x( 'Order', 'Post Type Singular Name', 'pqc' ),
            'menu_name'             => __( 'Orders', 'pqc' ),
            'name_admin_bar'        => __( 'Orders', 'pqc' ),
            'parent_item_colon'     => __( 'Parent Orders:', 'pqc' ),
            'all_items'             => __( 'Orders', 'pqc' ),
            'add_new_item'          => __( 'Add New Order', 'pqc' ),
            'add_new'               => __( 'Add Order', 'pqc' ),
            'new_item'              => __( 'New Order', 'pqc' ),
            'edit_item'             => __( 'Edit Order', 'pqc' ),
            'update_item'           => __( 'Update Order', 'pqc' ),
            'view_item'             => __( 'View Order', 'pqc' ),
            'view_items'            => __( 'View Orders', 'pqc' ),
            'search_items'          => __( 'Search Order', 'pqc' ),
            'not_found'             => __( 'No Order found', 'pqc' ),
            'not_found_in_trash'    => __( 'No Order found in Trash', 'pqc' ),
        );

        $args = array(
            'label'                 => __( 'Orders', 'pqc' ),
            'description'           => __( 'Order used in 3DPC Quote Calculator', 'pqc' ),
            'labels'                => $labels,
            'supports'              => array( 'title' ),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => 'pqc-settings-page',
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'rewrite'               => false,
            'map_meta_cap'          => true,
            'capabilities'          => array(
                'create_posts'      => 'do_not_allow',
            )
        );

        register_post_type( $this->key, $args );

        remove_post_type_support( $this->key, 'title' );

        // Action after Registering the Order Post Type
        do_action( 'pqc_after_register_order' );

    }

    public function admin_head()
	{
        if ( get_post_type() != $this->key ) return;
        ?>
        <style>
        div#order_data .postbox-header,
        div#order_data.postbox button.handlediv,
        div#order_data.postbox h2.hndle,
        div#submitdiv.postbox,
        div#post-body-content {
            display: none;
        }
        </style>
        <?php

    }

    public function bulk_actions( $actions )
	{
        unset( $actions[ 'edit' ] );
        return $actions;
    }

    /**
     * Generates and displays row action links.
     *
     * @since 2.1.1
     * @access protected
     *
     * @param object $post        Post being acted upon.
     * @param string $column_name Current column name.
     * @param string $primary     Primary column name.
     * @return string Row actions output for posts.
     */
    private function handle_row_actions( $post )
	{
        $post_type_object = get_post_type_object( $post->post_type );
        $can_edit_post = current_user_can( 'edit_post', $post->ID );
        $actions = array();
        $title = _draft_or_post_title();

        if ( $can_edit_post && 'trash' != $post->post_status ) {
            $actions['edit'] = sprintf(
                '<a href="%s" aria-label="%s">%s</a>',
                get_edit_post_link( $post->ID ),
                /* translators: %s: post title */
                esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'pqc' ), $title ) ),
                __( 'Edit', 'pqc' )
            );
        }

        if ( current_user_can( 'delete_post', $post->ID ) ) {
            if ( 'trash' === $post->post_status ) {
                $actions['untrash'] = sprintf(
                    '<a href="%s" aria-label="%s">%s</a>',
                    wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ),
                    /* translators: %s: post title */
                    esc_attr( sprintf( __( 'Restore &#8220;%s&#8221; from the Trash', 'pqc' ), $title ) ),
                    __( 'Restore', 'pqc' )
                );
            } elseif ( EMPTY_TRASH_DAYS ) {
                $actions['trash'] = sprintf(
                    '<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
                    get_delete_post_link( $post->ID ),
                    /* translators: %s: post title */
                    esc_attr( sprintf( __( 'Move &#8220;%s&#8221; to the Trash', 'pqc' ), $title ) ),
                    _x( 'Trash', 'verb', 'pqc' )
                );
            }
            if ( 'trash' === $post->post_status || ! EMPTY_TRASH_DAYS ) {
                $actions['delete'] = sprintf(
                    '<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
                    get_delete_post_link( $post->ID, '', true ),
                    /* translators: %s: post title */
                    esc_attr( sprintf( __( 'Delete &#8220;%s&#8221; permanently', 'pqc' ), $title ) ),
                    __( 'Delete Permanently', 'pqc' )
                );
            }
        }

        if ( is_post_type_viewable( $post_type_object ) ) {
            if ( in_array( $post->post_status, array( 'pending', 'draft', 'future', ) ) ) {
                if ( $can_edit_post ) {
                    $preview_link = get_preview_post_link( $post );
                    $actions['view'] = sprintf(
                        '<a href="%s" rel="permalink" aria-label="%s">%s</a>',
                        esc_url( $preview_link ),
                        /* translators: %s: post title */
                        esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'pqc' ), $title ) ),
                        __( 'Preview', 'pqc' )
                    );
                }
            } elseif ( 'trash' != $post->post_status ) {
                $actions['view'] = sprintf(
                    '<a href="%s" rel="permalink" aria-label="%s">%s</a>',
                    get_permalink( $post->ID ),
                    /* translators: %s: post title */
                    esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'pqc' ), $title ) ),
                    __( 'View', 'pqc' )
                );
            }
        }

        return $this->row_actions( $actions );
    }

    /**
    * Prepare the row actions
    *
    * @param array $actions
    */
    private function row_actions( $actions )
	{
        if ( ! is_array( $actions ) ) return;

        $total = count( $actions );
        $string = '<div class="row-actions">';
        $i = 1;

        foreach( $actions as $key => $value ) {

            $string .= '<span class="' . $key . '">' . $value . '</span>';

            if ( $i != $total ) $string .= ' | ';

            $i++;

        }

        $string .= '</div>';

        return $string;
    }

    /**
    * Sets Messages for update
    *
    * @param mixed $messages
    */
    public function updated_messages( $messages )
	{
        if ( $this->key != get_current_screen()->post_type ) return $messages;

        $data       = get_post_custom();
        $data       = isset( $data[$this->key . '_data'] ) ? maybe_unserialize( maybe_unserialize( $data[$this->key . '_data'][0] ) ) : $this->default_data;
        $status     = isset( $data['order_status'] ) ? $data['order_status'] : $this->default_data['order_status'];
        $currency   = isset( $data['currency'] ) ? $data['currency'] : $this->default_data['currency'];
        $c_currency = pqc_money_format_control()['currency'];
        $c_c_name = pqc_money_format_control()['currencies'][$c_currency]['name'];

        if ( $status == 'pending' && $currency != $c_currency ) {

            global $pqc;

            $pqc->add_notice(
                sprintf(
                    __( 'Sorry you can edit cost/price for this order. This order currency does not match your preferred currency <strong>%s (%s)</strong>.', 'pqc' ),
                    $c_c_name, $c_currency
                ), 'pqc-update-nag'
            );

        }

        $post = get_post();

        $messages[$this->key] = array(
            '',
            __( 'Order updated.', 'pqc' ),
            false,
            false,
            __( 'Order updated.', 'pqc' ),
            false,
            __( 'Order published.', 'pqc' ),
            __( 'Order saved.', 'pqc' ),
            __( 'Order submitted.', 'pqc' ),
            sprintf(
                __( 'Order scheduled for: <strong>%1$s</strong>.', 'pqc' ),
                date_i18n( 'M j, Y @ G:i', strtotime( $post->post_date ) )
            ),
            __( 'Order draft updated.', 'pqc' ),
        );

        return $messages;
    }

    /**
    * Sets Messages for Bulk Update
    *
    * @param mixed $bulk_messages
    * @param mixed $bulk_counts
    */
    public function bulk_updated_messages( $bulk_messages, $bulk_counts )
	{
        if ( $this->key != get_current_screen()->post_type ) return $bulk_messages;

        $bulk_messages[$this->key] = array(
            'updated'   => _n( '%s order updated.', '%s orders updated.', $bulk_counts['updated'] ),
            'locked'    => _n( '%s order not updated, somebody is editing it.', '%s orders not updated, somebody is editing them.', $bulk_counts['locked'] ),
            'deleted'   => _n( '%s order permanently deleted.', '%s orders permanently deleted.', $bulk_counts['deleted'] ),
            'trashed'   => _n( '%s order moved to the Trash.', '%s orders moved to the Trash.', $bulk_counts['trashed'] ),
            'untrashed' => _n( '%s order restored from the Trash.', '%s orders restored from the Trash.', $bulk_counts['untrashed'] ),
        );

        return $bulk_messages;

    }

    /**
    * Modify colums
    *
    * @param mixed $columns
    */
    public function edit_columns( $columns )
	{
        $columns = array(
            'cb'                => '<input type="checkbox">',
            'order_title'       => __( 'Order', 'pqc' ),
            // 'order_items'       => __( 'Purchased', 'pqc' ),
            'shipping_address'  => __( 'Ship to', 'pqc' ),
            'order_status'      => __( 'Payment Status', 'pqc' ),
            'order_total'       => __( 'Total', 'pqc' ),
            'order_date'        => __( 'Date', 'pqc' ),
        );

        return $columns;
    }

    /**
     * Make columns sortable.
     *
     * @param  array $columns
     * @return array
     */
    public function sortable_columns( $columns )
	{
        $custom = array(
            'order_title' => 'ID',
            'order_total' => 'total',
            'order_date'  => 'date',
        );
        unset( $columns['comments'] );

        return wp_parse_args( $custom, $columns );
    }

    /**
    * Sets custom columns for order
    *
    * @param mixed $column
    */
    public function custom_columns( $column )
	{
        global $post;

        $data = get_post_custom();

        $data               = isset( $data[$this->key . '_data'] ) ? maybe_unserialize( maybe_unserialize( $data[$this->key . '_data'][0] ) ) : $this->default_data;
        $items              = isset( $data['items'] ) ? $data['items'] : $this->default_data['items'];
        $payment_method     = isset( $data['payment_method'] ) ? $data['payment_method'] : $this->default_data['payment_method'];
        $firstname          = isset( $data['firstname'] ) ? $data['firstname'] : $this->default_data['firstname'];
        $lastname           = isset( $data['lastname'] ) ? $data['lastname'] : $this->default_data['lastname'];
        $email              = isset( $data['email'] ) ? $data['email'] : $this->default_data['email'];
        $payer_email        = isset( $data['payer_email'] ) ? $data['payer_email'] : $this->default_data['payer_email'];
        $address            = isset( $data['address'] ) ? $data['address'] : $this->default_data['address'];
        $note               = isset( $data['note'] ) ? $data['note'] : $this->default_data['note'];
        $status             = isset( $data['order_status'] ) ? $data['order_status'] : $this->default_data['order_status'];
        $coupon             = isset( $data['coupon'] ) ? $data['coupon'] : $this->default_data['coupon'];
        $shipping_option    = isset( $data['shipping_option'] ) ? $data['shipping_option'] : $this->default_data['shipping_option'];
        $cart_total         = isset( $data['cart_total'] ) ? $data['cart_total'] : $this->default_data['cart_total'];
        $subtotal           = isset( $data['subtotal'] ) ? $data['subtotal'] : $this->default_data['subtotal'];
        $total              = isset( $data['total'] ) ? $data['total'] : $this->default_data['total'];
        $currency           = isset( $data['currency'] ) ? $data['currency'] : $this->default_data['currency'];
        $currency_pos       = isset( $data['currency_pos'] ) ? $data['currency_pos'] : $this->default_data['currency_pos'];
        $user_id            = isset( $data['user_id'] ) ? $data['user_id'] : $this->default_data['user_id'];
        $date               = isset( $data['date'] ) ? $data['date'] : $this->default_data['date'];

        switch ( $column ) {
            case 'order_title':
                if ( ! empty( $user_id ) ) {

                    $user = get_user_by( 'id', $user_id );
                    $username = '<a href="user-edit.php?user_id=' . absint( $user_id ) . '">';
                    $username .= esc_html( ucwords( $user->display_name ) );
                    $username .= '</a>';

                }
                else {
                    $username = trim( sprintf( _x( '%1$s %2$s', 'full name', 'pqc' ), $firstname, $lastname ) );
                }
                printf(
                    __( '%1$s by %2$s', 'pqc' ),
                    '<a href="' . admin_url( 'post.php?post=' . absint( $post->ID ) . '&action=edit' ) . '" class="row-title"><strong>#' . esc_attr( $post->ID ) . '</strong></a>',
                    $username
                );
                if ( $email ) {
                    echo '<small class="meta email"><a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a></small>';
                }

                echo count( $items ) . ' items';

                echo $this->handle_row_actions( $post );
                echo '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __( 'Show more details', 'pqc' ) . '</span></button>';
                break;
            case 'shipping_address':
                echo $address . '<small class="meta">Via ' . $shipping_option . '</small>';
                break;
            case 'order_total':
                echo pqc_money_format( $total, $currency, true, $currency_pos ) . '<small class="meta">Via ' . $payment_method . '</small>';
                break;
            case 'order_status':
                echo $status;
                break;
            case 'order_date':
                $date = empty( $date ) ? $post->post_modified : $date;
                return printf( '<time datetime="%s">%s</time> <br> %s', mysql2date( 'c', $date ), mysql2date( 'Y-m-d', $date ), mysql2date( 'h:i:s a', $date ) );
                break;
        }
    }

    /**
    * Order Meta Box to the Edit Screen
    *
    */
    public function metabox()
	{
        if ( $this->key != get_current_screen()->post_type ) return;

        add_meta_box( 'order_data', 'Order Data', array( $this, 'metabox_html' ), $this->key, 'normal', 'high' );
        add_meta_box( 'order_actions', 'Order Actions', array( $this, 'metabox_order_actions_html' ), $this->key, 'side', 'high' );
        add_meta_box( 'order_meta', 'Order Meta', array( $this, 'metabox_order_meta_html' ), $this->key, 'side', 'high' );

        // Remove the Slug Meta Box
        remove_meta_box( 'slugdiv', $this->key, 'normal' );

    }

    /**
    * HTML content to add to order metabox
    *
    */
    public function metabox_html()
	{
        if ( $this->key != get_current_screen()->post_type ) return;

        $post = get_post();
        $data = get_post_custom();

        $data               = isset( $data[$this->key . '_data'] ) ? maybe_unserialize( maybe_unserialize( $data[$this->key . '_data'][0] ) ) : $this->default_data;
        $items              = isset( $data['items'] ) ? $data['items'] : $this->default_data['items'];
        $payment_method     = isset( $data['payment_method'] ) ? $data['payment_method'] : $this->default_data['payment_method'];
        $firstname          = isset( $data['firstname'] ) ? $data['firstname'] : $this->default_data['firstname'];
        $lastname           = isset( $data['lastname'] ) ? $data['lastname'] : $this->default_data['lastname'];
        $email              = isset( $data['email'] ) ? $data['email'] : $this->default_data['email'];
        $address            = isset( $data['address'] ) ? $data['address'] : $this->default_data['address'];
        $city               = isset( $data['city'] ) ? $data['city'] : $this->default_data['city'];
        $zipcode            = isset( $data['zipcode'] ) ? $data['zipcode'] : $this->default_data['zipcode'];
        $state              = isset( $data['state'] ) ? $data['state'] : $this->default_data['state'];
        $note               = isset( $data['note'] ) ? $data['note'] : $this->default_data['note'];
        $status             = isset( $data['order_status'] ) ? $data['order_status'] : $this->default_data['order_status'];
        $coupon             = isset( $data['coupon'] ) ? $data['coupon'] : $this->default_data['coupon'];
        $shipping_option    = isset( $data['shipping_option'] ) ? $data['shipping_option'] : $this->default_data['shipping_option'];
        $cart_total         = isset( $data['cart_total'] ) ? $data['cart_total'] : $this->default_data['cart_total'];
        $subtotal           = isset( $data['subtotal'] ) ? $data['subtotal'] : $this->default_data['subtotal'];
        $total              = isset( $data['total'] ) ? $data['total'] : $this->default_data['total'];
        $currency           = isset( $data['currency'] ) ? $data['currency'] : $this->default_data['currency'];
        $currency_pos       = isset( $data['currency_pos'] ) ? $data['currency_pos'] : $this->default_data['currency_pos'];
        $pay_for_order_id   = isset( $data['pay_for_order_id'] ) ? $data['pay_for_order_id'] : '';
        ?>
        <div class="order_options pqc_options_panel" style="padding: 1.5%;">

            <?php wp_nonce_field( $this->key . '_nonce', $this->key . '_data' ); ?>

            <input name="post_title" value="<?php echo $post->post_title; ?>" type="hidden">

            <h2>
                <?php printf( __( 'Order %s details', 'pqc' ), $post->post_title ); ?>
                <span style="float: right;color: #8BC34A;font-weight: 600;font-size: 30px;">
                <?php echo pqc_money_format( $total, $currency, true, $currency_pos ); ?>
                </span>
            </h2>
            <p class="order_number" style="clear: both"><?php printf( __( 'Payment via %s - %s', 'pqc' ), $payment_method, $status ); ?>
            <?php if ( $status == 'pending' && $currency == pqc_money_format_control()['currency'] ) : ?>
            <?php
            $title = ! isset( $pay_for_order_id ) || empty( $pay_for_order_id ) ?
                'title="' . __( 'Save this order to get a valid payment url.', 'pqc' ) . '"' : '';

            $href = ! isset( $pay_for_order_id ) || empty( $pay_for_order_id ) ? '' :
                sprintf(
                    'href="%s?pay_for_order=true&pay_for_order_id=%s&order_id=%s"',
                    get_permalink( pqc_page_exists( 'pqc-checkout' ) ),
                    $pay_for_order_id, $post->ID
                );

            printf(
                '<a style="float: right; font-size: 13px;" target="_blank" %s %s>Customer payment page</a>',
                $href, $title
            );
            ?>
            <?php endif; ?>
            </p>

            <fieldset>
                <legend><?php _e( '<h1>Buyer Details</h1>', 'pqc' ); ?></legend>
                <ul style="font-size: 15px; list-style-type: circle; margin: 0 0 0 2em;">
                    <li style="margin: 0;"><p style="line-height: 1.2;"><?php echo 'First Name: ' . $firstname; ?></p></li>
                    <li style="margin: 0;"><p style="line-height: 1.2;"><?php echo 'Last Name: ' . $lastname; ?></p></li>
                    <li style="margin: 0;"><p style="line-height: 1.2;"><?php echo 'Email Address: ' . $email; ?></p></li>
                    <li style="margin: 0;"><p style="line-height: 1.2;"><?php echo 'Shipping Address: ' . $address; ?></p></li>
                    <li style="margin: 0;"><p style="line-height: 1.2;"><?php echo 'City/Zipcode: ' . "$city / $zipcode"; ?></p></li>
                    <li style="margin: 0;"><p style="line-height: 1.2;"><?php echo 'State: ' . $state; ?></p></li>
                    <li style="margin: 0;"><p style="line-height: 1.2;"><?php echo 'Shipping Option: ' . $shipping_option; ?></p></li>
                    <li style="margin: 0;"><p style="line-height: 1.2;"><?php echo 'Customer Note: ' . $note; ?></p></li>
                </ul>
            </fieldset>

            <fieldset>
                <legend><?php _e( '<h1>Item Data</h1>', 'pqc' ); ?></legend>

				<select name="order_items_bulk_actions">
					<option value="-1"><?php _e( 'Bulk Actions', 'pqc' ); ?></option>
				</select>
				<input name="send_bulk_action" class="button button-primary" value="Apply" type="submit">
                <table class="cart">
                    <thead>
                        <tr>
                            <th id="cb" style="width: 5%;"><input type="checkbox"></th>
                            <th id="item"><?php _e( 'Item', 'pqc' ); ?></th>
                            <th id="material"><?php _e( 'Materials', 'pqc' ); ?></th>
                            <!--<th id="infill"><?php _e( 'Infill Rate', 'pqc' ); ?></th>-->
                            <th id="item"><?php _e( 'Unit Price x Qty', 'pqc' ); ?></th>
                            <th id="total"><?php _e( 'Total', 'pqc' ); ?></th>
                            <th id="total"><?php _e( 'File Url', 'pqc' ); ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $i = 0; foreach( $items as $item ) : ?>
                        <tr id="<?php echo $item['unique_id']; ?>" <?php if ( $i % 2 ) { echo 'class="even"'; } ?>>
                            <td class="cb">
                                <input id="cb-select-<?php echo $item['unique_id']; ?>"
									type="checkbox" class="cb-select" value=""
									name="items_cb[<?php echo $item['unique_id']; ?>]">
                            </td>
                            <td class="item">
                                <p><strong><?php echo $item['name']; ?></strong></p>
                            </td>
                            <td class="item">
                                <div id="materials"><?php echo $item['material']; ?></div>
                            </td>
                            <!--<td class="item">
                                <div id="infill"><?php echo empty( $item['infill'] ) ? 100 : $item['infill']; ?>%</div>
                            </td>-->
                            <td class="item">
                                <?php if ( $status == 'pending' && $currency == pqc_money_format_control()['currency'] ) : ?>
                                <input type="text" autocomplete="off" name="item_price[<?php echo $item['unique_id']; ?>]" style="width: 50%;" value="<?php echo pqc_number_format( $item['price'] ); ?>"> x <?php echo $item['quantity']; ?>
                                <?php else: ?>
                                <p><strong><?php echo pqc_money_format( $item['price'], $currency, true, $currency_pos ); ?></strong> x <?php echo $item['quantity']; ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="item"><p><strong><?php echo pqc_money_format( $item['amount'], $currency, true, $currency_pos ); ?></strong></p></td>
                            <td class="item"><p><a href="<?php echo $item['url']; ?>"><?php _e( 'Download file', 'pqc' ); ?></a></p></td>
                        </tr>
                        <?php $i++; endforeach; ?>
                    </tbody>
                </table>


                <p style="font-size: 15px; margin: 0; width: 17em; clear: both; float: right; border-bottom: 1px solid silver;">
                    <?php printf( 'Cart Total: <span style="font-weight: 600; float: right;">%s</span>', pqc_money_format( $cart_total, $currency, true, $currency_pos ) ); ?>
                </p>
                <?php if ( ! empty( $coupon ) ) : ?>
                <p style="font-size: 15px; margin: 0; width: 17em; clear: both; float: right; border-bottom: 1px solid silver;">
                    <?php printf( 'Coupon Used: <span style="font-weight: 600; float: right;">%s</span>', $coupon ); ?>
                </p>
                <?php endif; ?>
            </fieldset>
        </div>
        <?php

    }

    /**
    * HTML content to add to order metabox
    *
    */
    public function metabox_order_actions_html()
	{
        if ( $this->key != get_current_screen()->post_type ) return;

        $post = get_post();

        $data = get_post_custom();

        $data      = isset( $data[$this->key . '_data'] ) ? maybe_unserialize( maybe_unserialize( $data[$this->key . '_data'][0] ) ) : $this->default_data;
        $action    = isset( $data['order_action'] ) ? $data['order_action'] : $this->default_data['order_action'];
        $a_payment = isset( $data['allow_payment'] ) ? $data['allow_payment'] : $this->default_data['allow_payment'];
        $status    = isset( $data['order_status'] ) ? $data['order_status'] : $this->default_data['order_status'];
        $currency  = isset( $data['currency'] ) ? $data['currency'] : $this->default_data['currency'];
        ?>
        <div class="order_options pqc_options_panel" style="padding: 1.5%;">

            <div class="form-field type_field" style="margin: 1em 0;">

                <select id="order_action" name="order_action" style="width: 100%;">
                    <?php foreach( $this->default_data['actions'] as $key ) : ?>
                    <option value="<?php echo $key; ?>" <?php selected( $key, $action ); ?>><?php echo ucfirst( $key ) . ' Order'; ?></option>
                    <?php endforeach; ?>
                </select>

            </div>

            <?php if ( $status == 'pending' && $currency == pqc_money_format_control()['currency'] ) : ?>
            <div class="form-field allow_payment_field" style="margin: 1em 0;">
                <label for="allow_payment">
                    <input type="checkbox" id="allow_payment" name="allow_payment" <?php checked( $a_payment, 1 ); ?>>
                    <?php _e( 'Allow customer complete order' ); ?>
                </label>
            </div>
            <?php endif; ?>

            <div id="delete-action">
                <a style="color: #a00;" class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php _e( 'Move to Trash' ); ?></a>
            </div>

            <div id="publishing-action">
                <input name="save" class="button button-primary button-large" value="Save Order" type="submit">
            </div>

            <div class="clear"></div>
        </div>
        <?php

    }

    /**
    * HTML content to add to order metabox
    *
    */
    public function metabox_order_meta_html()
	{
        if ( $this->key != get_current_screen()->post_type ) return;

        $post = get_post();

        $data = get_post_custom();

        $data               = isset( $data[$this->key . '_data'] ) ? maybe_unserialize( maybe_unserialize( $data[$this->key . '_data'][0] ) ) : $this->default_data;
        $coupon             = isset( $data['coupon'] ) ? $data['coupon'] : $this->default_data['coupon'];
        $shipping_option    = isset( $data['shipping_option'] ) ? $data['shipping_option'] : $this->default_data['shipping_option'];
        $shipping_cost      = isset( $data['shipping_cost'] ) ? $data['shipping_cost'] : $this->default_data['shipping_cost'];
        $cart_total         = isset( $data['cart_total'] ) ? $data['cart_total'] : $this->default_data['cart_total'];
        $subtotal           = isset( $data['subtotal'] ) ? $data['subtotal'] : $this->default_data['subtotal'];
        $total              = isset( $data['total'] ) ? $data['total'] : $this->default_data['total'];
        $currency           = isset( $data['currency'] ) ? $data['currency'] : $this->default_data['currency'];
        $currency_pos       = isset( $data['currency_pos'] ) ? $data['currency_pos'] : $this->default_data['currency_pos'];
        ?>
        <div class="order_options pqc_options_panel" style="padding: 1.5%;">
            <ul style="font-size: 12px; margin: 0%;">
                <li style="margin: 2% 0;">
                <?php
                printf(
                    'Subtotal: <span style="float: right;">%s</span>',
                    pqc_money_format( $subtotal, $currency, true, $currency_pos )
                );
                ?>
                </li>
                <li style="margin: 2% 0;">
                <?php
                printf(
                    'Shipping Cost: <span style="float: right;">%s</span>',
                    pqc_money_format( $shipping_cost, $currency, true, $currency_pos )
                );
                ?>
                </li>
                <li style="margin: 2% 0;">
                <?php
                printf(
                    '<h3>Total: <span style="float: right;">%s</span></h3>',
                    pqc_money_format( $total, $currency, true, $currency_pos )
                );
                ?>
                </li>
            </ul>
        </div>
        <?php

    }

    /**
    * Save post metadata when a post is saved
    * @param int     $post_ID Post ID.
    * @param WP_Post $post    Post object.
    * @param bool    $update  Whether this is an existing post being updated or not.
    */
    public function metabox_save( $post_id, $post, $update )
	{
        if ( $post->post_type != $this->key ) return;
        if ( ! current_user_can( 'manage_options' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || $post->post_status == 'auto-draft' ) return;
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;
        if ( ! isset( $_POST[$this->key . '_data'] ) ) return;
        if ( wp_verify_nonce( $_POST[$this->key . '_data'], $this->key . '_nonce' ) == false ) return;

        $order_action   = sanitize_text_field( $_POST['order_action'] );

        // Validate
        if ( ! in_array( $order_action, $this->default_data['actions'] ) ) return;

        $data = get_post_custom();
        $data = isset( $data[$this->key . '_data'] ) ?
			maybe_unserialize( maybe_unserialize( $data[$this->key . '_data'][0] ) ) : $this->default_data;
        $data = wp_parse_args( array( 'order_action' => $order_action ), $data );

		if (
			$data['order_status'] == 'pending' &&
			$data['currency'] == pqc_money_format_control()['currency']
		) {
            // Update item price, subtotal, cart total and total if payment status is pending
            for( $i = 0; $i < count( $data['items'] ); $i++ ) {

                $current = $data['items'][$i];

                if ( ! array_key_exists( $current['unique_id'], $_POST['item_price'] ) ) continue;

                $price = pqc_number_format_raw( $_POST['item_price'][$current['unique_id']] );

                if ( ! isset( $data['items'][$i]['infill'] ) || empty( $data['items'][$i]['infill'] ) )
                    $data['items'][$i]['infill'] = 100;

                $data['items'][$i]['price'] = $price;
                $data['items'][$i]['amount'] = floatval( $price * $data['items'][$i]['quantity'] );
                $subtotal[] = $data['items'][$i]['amount'];
            }

            $subtotal = array_sum( $subtotal );
            $cart_total = $subtotal;

            if ( isset( $data['coupon'] ) && ! empty( $data['coupon'] ) ) {

                $coupon_id = (int) pqc_get_coupon_id( esc_attr( $data['coupon'] ) );
                $old_subtotal = $subtotal;
                $subtotal = pqc_apply_coupon( $coupon_id, $subtotal );

                if ( $subtotal === false ) $subtotal = $old_subtotal;

            }

            $total = $subtotal;

            if ( isset( $data['shipping_cost'] ) && ! empty( $data['shipping_cost'] ) ) {

                $shipping_option_cost = floatval( $data['shipping_cost'] );
                $total = $subtotal + $shipping_option_cost;

            }

            $data['pay_for_order_id']   = uniqid( pqc_get_random_string() );
            $data['allow_payment']      = isset( $_POST['allow_payment'] ) ? 1 : 0;
            $data['cart_total']         = $cart_total;
            $data['subtotal']           = $subtotal;
            $data['total']              = $total;

            $send_email                 = $data['allow_payment'] == 1 ? true : false;

        }

        update_post_meta( $post_id, $this->key . '_data', $data );

        if ( isset( $send_email ) && $send_email ) pqc_send_order_email( $post_id );
    }

    /**
    * Enqueue/Dequeue Admin Scripts
    *
    */
    public function admin_scripts()
	{
        if ( $this->key != get_current_screen()->post_type ) return;

        wp_dequeue_script( PQC_NAME . ' URL MOD' );
    }

}

new PQC_Orders();
