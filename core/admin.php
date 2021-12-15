<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) die;

if ( ! class_exists( 'PQC_Admin' ) ) :

final class PQC_Admin
{

    private $slug = 'pqc-settings-page';

    /**
    * The Constructor
    *
    */
    public function __construct()
	{

        global $pqc;

        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_action( 'admin_notices', array( $this, 'display_notices' ), 999 );
        add_action( 'admin_head', array( $this, 'admin_head' ) );
        add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );

        if ( $pqc->plugin_inactive ) return;

        add_action( 'admin_menu',  array( &$this, 'admin_menu' ), 5 );
        add_filter( 'custom_menu_order', array( &$this, 'submenu_order' ) );
        add_action( 'admin_enqueue_scripts',  array( &$this, 'admin_scripts' ), 10 );
    }

    /**
    * Admin head content
    *
    */
    public function admin_head()
	{
        $confirm = sprintf(
            __( 'Type "yes" if you will like %s to install default Materials after reseting.', 'pqc' ),
            PQC_NAME
        );

        $url1 = add_query_arg( array( 'pqc-setup' => 'setup' ) );
        $url2 = add_query_arg( array( 'pqc-setup' => 'setup', 'pqc-add-default-materials' => 1 ) );
        ?>
        <style>
        .pqc-update-nag {
            display: block !important;
            padding: 1px 12px !important;
            border-left-color: #0288d1;
        }
        </style>

        <script>
        if ( typeof jQuery != "undefined" ) {
            jQuery(document).ready( function () {
                jQuery( 'a#pqc-reset-btn' ).click( function(event) {

                    event.preventDefault();

                    var confirm = window.prompt( '<?php echo $confirm; ?>', 'no' );

                    if ( confirm == null ) return false;

                    window.location.href = confirm.toLowerCase() == "yes" ? "<?php echo $url2; ?>" : "<?php echo $url1; ?>";

                } );
            })
        }
        </script>
        <?php
    }

    /**
    * Admin init content
    *
    */
    public function admin_init()
	{
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

        if ( isset( $_GET['page'] ) && in_array( $_GET['page'], ['pqc-start', 'pqc-about' ] ) ) return;

        if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'checkout' ) return;

        global $pqc;

        if ( ! $pqc->has_valid_license() && ($_GET['page'] ?? '') != 'pqc-license-page') {
            $donate_url = "https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=donation@phanes.co&currency_code=USD";

            $pqc->add_notice(
                sprintf(
                    __( '<strong>%s</strong> require your License code to be fully activated. <a href="%s">Add License code</a> or <a href="%s" target="_blank">donate with PayPal</a>', 'pqc' ),
                    PQC_NAME,
                    admin_url( 'admin.php?page=pqc-license-page' ),
                    $donate_url
                ), 'error', false
            );
            $pqc->add_notice(
                sprintf(
                    __( '<div style="display:flex;justify-content: space-between;align-items: center;"><span>Make a paypal donation today to appreciate our work.</span> <a href="%s" target="_blank"><img src="%s" alt="Donate with PayPal" /></a></div>', 'pqc' ),
                    $donate_url,
                    "https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif",
                ), 'pqc-update-nag update-nag', false
            );
        } elseif (
			$pqc->has_valid_license() &&
			isset( $_GET['page'] ) &&
			$_GET['page'] == 'pqc-license-page'
		) {
			$license	= get_option( 'pqc-license', false );
			$code		= $license['code'];
			$chunks		= explode( '-', $code );
			$code		= "$chunks[0]-XXXX-XXXX-XXXX-XXXX-$chunks[5]-$chunks[6]";

			$pqc->add_notice(
				sprintf(
					__( '<strong>%s</strong> is fully activated. <br/> License code: <strong>%s</strong>', 'pqc' ),
					PQC_NAME, $code
				), 'updated-nag pqc-update-nag'
			);

		}

        if ( ! pqc_is_paypal_ready() ) {

            $pqc->add_notice(
                sprintf(
                    __( '<strong>%s</strong> needs your PayPal Credentials. <a href="%s">Add Credentials</a>', 'pqc' ),
                    PQC_NAME,
                    admin_url( 'admin.php?page=pqc-settings-page&tab=checkout&section=paypal' )
                ), 'pqc-update-nag update-nag'
            );

        }

        if ( ! pqc_is_stripe_ready() ) {

            $pqc->add_notice(
                sprintf(
                    __( '<strong>%s</strong> needs your Stripe API Keys. <a href="%s">Add API Keys</a>', 'pqc' ),
                    PQC_NAME,
                    admin_url( 'admin.php?page=pqc-settings-page&tab=checkout&section=stripe' )
                ), 'pqc-update-nag update-nag'
            );

        }

    }

    /**
    * Display registered notices
    *
    */
    public function display_notices()
	{
        echo pqc::$_notice;
    }

    /**
    * Set Screen
    *
    * @param mixed $status
    * @param mixed $option
    * @param mixed $value
    */
    public static function set_screen( $status, $option, $value )
	{
        return $value;
    }

    /**
    * Admin Menu
    *
    */
	public function admin_menu()
	{
        // Include/Require files
        $this->includes();

        $settings = add_menu_page(
            PQC_NAME,
            'Phanes Legacy',
            'manage_options',
            $this->slug,
            array( $this, 'settings' ),
            PQC_URL . 'assets/images/icon.png',
            42.28473
        );

        $pqc_submenus = apply_filters( 'pqc_admin_submenus', array(
            'materials_load'    => array( 'Materials', 'Materials', 'manage_options', 'pqc-materials-page', array( 'PQC_Materials', 'materials_page' ) ),
            'license_load'      => array( 'License code', 'License code', 'manage_options', 'pqc-license-page', array( $this, 'license_page' ) ),
        ) );

        $pqc_load_submenus = apply_filters( 'pqc_admin_load_submenus', array(
            'materials_load'    => array( 'PQC_Materials', 'materials_screen_option' ),
        ) );

        foreach( $pqc_submenus as $key => $pqc_submenu ) {

            if ( ! is_array( $pqc_submenu ) || count( $pqc_submenu ) != 5 ) continue;

            array_unshift( $pqc_submenu, $this->slug );

            $load = call_user_func_array( 'add_submenu_page', $pqc_submenu );

            if ( isset( $pqc_load_submenus[$key] ) ) add_action( "load-$load", $pqc_load_submenus[$key] );

        }

        add_submenu_page(
			'_pqc_start_doesnt_exist',
			__( 'Getting Started | ', 'pqc' ) . PQC_NAME,
			'',
			'manage_options',
			'pqc-start',
			[$this, 'getting_started']
		);

        add_submenu_page(
			'_pqc_about_doesnt_exist',
			__( 'Getting Started | ', 'pqc' ) . PQC_NAME,
			'',
			'manage_options',
			'pqc-about',
			[$this, 'getting_started']
		);

	}

    /**
    * Loads the License page
    *
    */
    public function license_page()
	{
        if ( isset( $_POST['pqc-apply-license-code'] ) ) {

            global $pqc;

            $error = false;

            foreach( $_POST['pqc-license-code'] as $chunk ) {

                $chunk = esc_attr( $chunk );

                if ( ! empty( $chunk ) && strlen( $chunk ) === 4 ) continue;

                $error = true;

                break;

            }

            if ( ! $error ) {

                $code = 'WOOS-' . implode( '-', $_POST['pqc-license-code'] ) . '-KEY';

                $check = $pqc->check_license( $code );

                if ( $check ) {

                    $args = array(
                        'code'          => $code,
                        'time'          => $pqc->license_response['expires'],
                        'check_time'    => strtotime( "+1 month" ),
                    );

                    update_option( 'pqc-license', $args );

                    $pqc->add_notice( __( '<strong> Done! </strong> License code updated successfully. ', 'pqc' ) . $pqc->license_response['msg'], 'updated', true, true );

                } else {

                    $pqc->add_notice(
						$pqc->license_response['msg'],
						'error', true, true
					);

                    update_option( 'pqc-license', false );

                }

            } else {
                $pqc->add_notice(
					__(
						'<strong> Error! </strong> License code is incorrect.',
						'pqc'
					), 'error', true, true
				);
            }

        }

        require_once PQC_PATH . 'admin/templates/license/license.php';

    }

    /**
    * Reorder submenus
    *
    * @param mixed $menu_order
    */
    public function submenu_order( $menu_order )
	{
        global $submenu;

        $slug = $this->slug;

        $submenu[$slug][0][0] = 'Settings'; // Replace The Default Name

        $new_orders = pqc_new_orders();

        if ( $new_orders && ! empty( $new_orders ) ) {
            $total = count( pqc_new_orders() );
            $class = 'awaiting-mod update-plugins count-' . $total;
            $submenu[$slug][3][0] .= ' <span class="' . $class . '"><span class="processing-count">' . $total . '</span></span>'; // Add tag if we have new order
        }

        // Reorder Submenus
        $args = array(
            $submenu[$slug][3], // Orders
            $submenu[$slug][4], // Coupons
            $submenu[$slug][1], // Materials
            $submenu[$slug][5], // Shipping Options
            $submenu[$slug][0], // Settings
            $submenu[$slug][2], // License Key
        );

        $submenu[$slug] = $args + $submenu[$slug];

        return $submenu[$slug];

    }

    /**
    * Include/Require files in the admin page
    *
    */
    private function includes()
	{
        require_once PQC_PATH . 'admin/templates/materials/materials.php';
        require_once PQC_PATH . 'admin/templates/orders/orders.php';
        require_once PQC_PATH . 'admin/templates/coupons/coupons.php';
        require_once PQC_PATH . 'admin/templates/shipping/shipping.php';
    }

    /**
    * Load the getting Started template
    *
    */
    public function getting_started()
	{
        global $pqc_getting_started_tabs;

        if ( isset( $_GET['page'] ) && substr( $_GET['page'], 0, 3 ) == 'pqc' ) {

            $options = maybe_unserialize( get_option( PQC_SETTING_OPTIONS, false ) );
            $string = substr( $_GET['page'], 4 );

            if ( ! array_key_exists( $string, $pqc_getting_started_tabs ) ) return;

            include_once PQC_PATH . 'admin/templates/welcome/header.php';
            include_once PQC_PATH . 'admin/templates/welcome/' . $string . '.php';
            include_once PQC_PATH . 'admin/templates/welcome/footer.php';

        }

    }

    /**
    * Load the Settings Page
    *
    */
    public function settings()
	{

        global $pqc_settings_tabs;

        $current = ( ! isset( $_GET['tab'] ) && empty( $_GET['tab'] ) ) ? 'general' : esc_attr( $_GET['tab'] );
        $section = ( ! isset( $_GET['section'] ) && empty( $_GET['section'] ) ) ? '' : esc_attr( $_GET['section'] );
        ?>
        <div class="wrap pqc-wrapper">

            <h2 style="margin: 0; padding: 0;"></h2>
            <?php
            // Display Settings Tabs
            $this->settings_tabs( $current, $section );

            // Run Callback function
            $tab = $pqc_settings_tabs[$current];

            // Check if we have section and load callback
            if (
				! empty( $section ) &&
				isset( $tab['sections'] ) &&
				array_key_exists( $section, $tab['sections'] )
			) {

                $callback = $tab['sections'][$section]['callback'];

            } else {

                $callback = $tab['callback'];

            }

            if ( is_array( $callback ) ) {

                if ( is_callable( $callback, true ) ) call_user_func( array( $callback[0], $callback[1] ) );
            }
            else {

                if ( is_callable( $callback, true ) ) call_user_func( $callback );
            }
            ?>

        </div>
        <?php
    }

    /**
    * Prepare and Load the settings tabs and sections
    *
    * @param mixed $current
    * @param mixed $section
    */
    public function settings_tabs( $current = 'general', $section = '' )
	{
        global $pqc_settings_tabs;

        if ( ! array_key_exists( $current, $pqc_settings_tabs ) ) $current = 'general';

        $self = admin_url() . 'admin.php?page=pqc-settings-page';
        $nav = '';

        foreach ( $pqc_settings_tabs as $key => $data ) {

            $label = $data['label'];
            $class = ( $key == $current ) ? 'nav-tab nav-tab-active' : 'nav-tab';

			$checkout_options_section = (
				! isset( $_GET['section'] ) &&
				$current != 'checkout' &&
				$key == 'checkout'
			) ? '&section=checkout_options' : '';

            $link = '&tab=' . $key . $checkout_options_section;
            $nav .= '<a href="' . $self . $link . '" class="' . $class .'">' . $label . '</a>';

        }

        $content = '<nav class="nav-tab-wrapper pqc-nav-tab-wrapper">' . $nav . '</nav>';

        echo $content;

        // If there're subsections, let's display them.
        if ( ! empty( $pqc_settings_tabs[$current]['sections'] ) ) {

            $nav = '';
            $last_key = array_keys( $pqc_settings_tabs[$current]['sections'] );
            $last_key = end( $last_key );

            foreach( $pqc_settings_tabs[$current]['sections'] as $key => $data ) {

                $label = $data['label'];
                $class = ( $key == $section ) ? 'current' : '';
                $link = '&tab=' . $current . '&section=' . $key;
                $pipe = ( $last_key == $key ) ? '' : '&nbsp;|&nbsp;';
                $nav .= '<li><a href="' . $self . $link . '" class="' . $class .'">' . $label . '</a>' . $pipe . '</li>';

            }

            $content = '<ul class="subsubsub">' . $nav . '</ul>';

            echo $content;

            $sub = 1;

        }

        echo isset( $sub ) ? '<br class="clear">' : '';

    }

    /**
    * Load the General Tab content
    *
    */
    public function general_tab()
	{
        global $pqc;

        $settings = get_option( PQC_SETTING_OPTIONS, array() );
        $options = $settings['pqc_general_settings'];
        $currencies = pqc_currencies();

        if ( isset( $_POST['pqc_save_general_settings'] ) ) {

            check_admin_referer( 'pqc_save_general_settings' );

            $error = false;

            foreach ( $_POST as $name => $value ) {

                if ( strpos( $name, 'pqc_', 0 ) === false ) continue;

                if ( $value == '' ) {
                    $error = true;
                    break;
                }

            }

            if ( ! $error ) {

                $max_filesize   = intval( $_POST['pqc_max_file_size'] );
                $max_filestay   = intval( $_POST['pqc_max_file_stay'] );
                $max_fileupload = intval( $_POST['pqc_max_file_upload'] );
                $min_filevolume = floatval( $_POST['pqc_min_file_volume'] );
                $initial_price  = floatval( $_POST['pqc_initial_price'] );
                $currency       = sanitize_text_field( strtoupper( $_POST['pqc_currency'] ) );
                $currency_pos   = sanitize_text_field( $_POST['pqc_currency_pos'] );
                $density_charge = isset( $_POST['pqc_density_charge'] ) ? 1 : 0;

                // Validation
                if ( ! in_array( $currency_pos, [ 'left', 'left_space', 'right', 'right_space' ] ) ) {

                    $error = true;

                    $pqc->add_notice(
                        sprintf(
                            __( 'Invalid currency position selected.', 'pqc' ),
                            PQC_NAME
                        ),
                    'error' );

                }

                if ( ! array_key_exists( $currency, $currencies ) ) {

                    $error = true;

                    $pqc->add_notice(
                        sprintf(
                            __( 'Invalid currency selected.', 'pqc' ),
                            PQC_NAME
                        ),
                    'error' );

                }

                if ( ! $error ) {

                    $args = wp_parse_args( array(
                        'max_file_size'     => $max_filesize,
                        'max_file_stay'     => $max_filestay,
                        'max_file_upload'   => $max_fileupload,
                        'min_file_volume'   => $min_filevolume,
                        'initial_price'     => $initial_price,
                        'currency'          => $currency,
                        'currency_pos'      => $currency_pos,
                        'density_charge'    => $density_charge,
                    ), $options );

                    $settings['pqc_general_settings'] = $args;

                    $update = update_option( PQC_SETTING_OPTIONS, $settings );

                    if ( $update || pqc_is_array_equal( $args, $options ) ) {

                        $pqc->add_notice( __( '<strong> Done! </strong> Settings saved.', 'pqc' ), 'updated', true, true );

                        $options = $args;

                    } else {

                        $pqc->add_notice( __( '<strong> Failed! </strong> Error occurred.', 'pqc' ), 'error', true, true );

                    }

                }

            }
            else {

                $pqc->add_notice( __( '<strong> Doing wrong! </strong> All fields are required.', 'pqc' ), 'error', true, true );

            }

        }

        extract( $options );

        require_once PQC_PATH . 'admin/templates/settings/general.php';

    }

    /**
    * Load the Checkout Tab content
    *
    */
    public function checkout_section()
	{
        global $pqc;

        $settings = get_option( PQC_SETTING_OPTIONS, array() );
        $options = $settings['pqc_checkout_settings'];

        if ( isset( $_POST['pqc_save_checkout_settings'] ) ) {

            check_admin_referer( 'pqc_save_checkout_settings' );

            $error = false;

            foreach ( $_POST as $name => $value ) {

                if ( strpos( $name, 'pqc_', 0 ) === false ) continue;

                if ( $value == '' ) {
                    $error = true;
                    break;
                }

            }

            if ( ! $error ) {

                $args = wp_parse_args( array(
                    'checkout_option' =>
                        isset( $_POST['pqc_checkout_option'] )
                        && ( $_POST['pqc_checkout_option'] == 1 || $_POST['pqc_checkout_option'] == 2 ) ? intval( $_POST['pqc_checkout_option'] ) : 1,
                    'shop_location' =>
                        isset( $_POST['pqc_shop_location'] )
                        && ( $_POST['pqc_shop_location'] == 1 || $_POST['pqc_shop_location'] == 2 ) ? intval( $_POST['pqc_shop_location'] ) : 1,
                ), $options );

                $settings['pqc_checkout_settings'] = $args;

                $update = update_option( PQC_SETTING_OPTIONS, $settings );

                if ( $update || pqc_is_array_equal( $args, $options ) ) {

                    $pqc->add_notice( __( '<strong> Done! </strong> Settings saved.', 'pqc' ), 'updated', true, true );

                    $options = $args;

                } else {

                    $pqc->add_notice( __( '<strong> Failed! </strong> Error occurred.', 'pqc' ), 'error', true, true );

                }

            } else {

                $pqc->add_notice( __( '<strong> Doing wrong! </strong> All fields are required.', 'pqc' ), 'error', true, true );

            }
        }

        extract( $options );

        require_once PQC_PATH . 'admin/templates/settings/checkout.php';
    }

    /**
    * Load the PayPal Tab content
    *
    */
    public function paypal_section()
	{
        global $pqc;

        $settings = get_option( PQC_SETTING_OPTIONS, array() );

        $options = $settings['pqc_checkout_settings'];

        if ( isset( $_POST['pqc_save_paypal_settings'] ) ) {

            check_admin_referer( 'pqc_save_paypal_settings' );

            $error = false;

            foreach ( $_POST as $name => $value ) {

                if ( strpos( $name, 'pqc_', 0 ) === false ) continue;

                if ( $value == '' ) {
                    $error = true;
                    break;
                }

            }

            if ( ! $error ) {

                $paypal_active          = isset( $_POST['pqc_paypal_active'] ) ? 1 : 0;
                $paypal_email           = sanitize_email( $_POST['pqc_paypal_email'] );
                $paypal_client_id       = sanitize_text_field( $_POST['pqc_paypal_client_id'] );
                $paypal_secret_key      = sanitize_text_field( $_POST['pqc_paypal_client_secret_key'] );
                $paypal_sandbox         = isset( $_POST['pqc_paypal_sandbox'] ) ? 1 : 0;

                $args = wp_parse_args( array(
                    'paypal_active'             => $paypal_active,
                    'paypal_client_id'          => $paypal_client_id,
                    'paypal_client_secret_key'  => $paypal_secret_key,
                    'paypal_email'              => $paypal_email,
                    'paypal_sandbox'            => $paypal_sandbox,
                ), $options );

                $settings['pqc_checkout_settings'] = $args;

                $update = update_option( PQC_SETTING_OPTIONS, $settings );

                if ( $update || pqc_is_array_equal( $args, $options ) ) {

                    $pqc->add_notice( __( '<strong> Done! </strong> Settings saved.', 'pqc' ), 'updated', true, true );

                    $options = $args;

                } else {

                    $pqc->add_notice( __( '<strong> Failed! </strong> Error occurred.', 'pqc' ), 'error', true, true );

                }

            } else {

                $pqc->add_notice( __( '<strong> Doing wrong! </strong> All fields are required.', 'pqc' ), 'error', true, true );

            }
        }

        extract( $options );

        require_once PQC_PATH . 'admin/templates/settings/paypal.php';
    }

    /**
    * Load the Stripe Tab content
    *
    */
    public function stripe_section()
	{
        global $pqc;

        $settings = get_option( PQC_SETTING_OPTIONS, array() );

        $options = $settings['pqc_checkout_settings'];

        if ( isset( $_POST['pqc_save_stripe_settings'] ) ) {

            check_admin_referer( 'pqc_save_stripe_settings' );

            $error = false;

            foreach ( $_POST as $name => $value ) {

                if ( strpos( $name, 'pqc_', 0 ) === false ) continue;

                if ( $value == '' ) {

                    $error = true;

                    break;

                }

            }

            if ( ! $error ) {

                $stripe_active          = isset( $_POST['pqc_stripe_active'] ) ? 1 : 0;
                $stripe_secret_key      = sanitize_text_field( $_POST['pqc_stripe_secret_key'] );
                $stripe_publishable_key = sanitize_text_field( $_POST['pqc_stripe_publishable_key'] );

                $args = wp_parse_args( array(
                    'stripe_active'             => $stripe_active,
                    'stripe_secret_key'         => $stripe_secret_key,
                    'stripe_publishable_key'    => $stripe_publishable_key,
                ), $options );

                $settings['pqc_checkout_settings'] = $args;

                $update = update_option( PQC_SETTING_OPTIONS, $settings );

                if ( $update || pqc_is_array_equal( $args, $options ) ) {

                    $pqc->add_notice( __( '<strong> Done! </strong> Settings saved.', 'pqc' ), 'updated', true, true );

                    $options = $args;

                } else {

                    $pqc->add_notice( __( '<strong> Failed! </strong> Error occurred.', 'pqc' ), 'error', true, true );

                }

            } else {

                $pqc->add_notice( __( '<strong> Doing wrong! </strong> All fields are required.', 'pqc' ), 'error', true, true );

            }
        }

        extract( $options );

        require_once PQC_PATH . 'admin/templates/settings/stripe.php';
    }

    /**
    * Enqueue Admin Scripts
    *
    */
    public function admin_scripts()
	{
        $screen = get_current_screen();

        if ( ! isset( $screen->id ) ) return;

        if ( strstr( $screen->id, 'pqc' ) == false ) return;

        /**
        * Enqueue Styles
        */
        wp_enqueue_style( PQC_NAME, PQC_URL . 'assets/css/admin.css', array(), PQC_VERSION, 'all' );
        wp_enqueue_style( 'jquery-ui', PQC_URL . 'assets/css/jquery-ui-base/jquery-ui.min.css', array(), '1.12.1', 'all' );

        /**
        * Enqueue Scripts
        */
        // wp_enqueue_media();

        wp_enqueue_script( 'jquery-ui', PQC_URL . 'assets/js/jquery-ui.min.js', array( 'jquery' ), '1.12.1', true );
        wp_enqueue_script( PQC_NAME . ' URL SCRIPT', PQC_URL . 'assets/js/uri.min.js', array(), PQC_VERSION, true );
        wp_enqueue_script( PQC_NAME . ' URL MOD', PQC_URL . 'assets/js/urlmod.js', array( PQC_NAME . ' URL SCRIPT' ), PQC_VERSION, true );
        wp_enqueue_script( PQC_NAME, PQC_URL . 'assets/js/admin.js', array(), PQC_VERSION, true );
        $params = array( 'action', 'quote', 'order', 'material', '_wpnonce', 'pqc-setup' );

        /**
        * Whether to do the the url modification or not
        *
        * @var boolean
        */
        $do_url_mod = apply_filters( 'pqc_do_url_mod', true );

        /**
        * Add url parameters to remove
        *
        * @var array
        */
        array_push( $params, apply_filters( 'pqc_mod_params', array() ) );

        wp_localize_script( PQC_NAME . ' URL MOD', 'PQC_Admin', array(
                'do_url_mod' => $do_url_mod === false ? false : true,
                'url_params' => $params,
            )
        );
    }
}

endif;

if ( is_admin() ) new PQC_Admin();
