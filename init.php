<?php
/*
Plugin Name:    Phanes 3DP - Legacy
Plugin URI:     https://phanes.co/
Description:    Start Your own 3D Printing Service business with Phanes 3DP. Customers can upload their STL files, get instant quotes, and order online seamlessly by paying with PayPal.
Version:        1.1.2
Author:         Phanes & Chukwudiebube Joseph Nwakpaka
Author URI:     https://phanes.co/
Text Domain:    pqc
Domain Path:    /i18n/languages/
*/

// If this file is called directly, abort.
defined( 'ABSPATH' ) OR exit;

if ( ! class_exists( 'PQC' ) ) :

final class PQC
{

    /**
     * The single instance of the class.
     *
     * @var PQC
     * @since 1.6
     */
    protected static $_instance = null;

    /**
     * Check if plugin is running correctly.
     * @access public
     * @since 1.6
     */
    public $plugin_inactive = false;

    /**
    * Holds all notice
    * @var string
    * @access public
    * @since 2.0.3
    */
    public static $_notice = '';

    /**
     * Main PQC Instance.
     *
     * Ensures only one instance of PQC is loaded or can be loaded.
     *
     * @since 1.6
     * @static
     * @see PQC()
     * @return PQC - Main instance.
     */
    public static function instance()
	{
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Cloning is forbidden.
     * @since 1.6
     */
    public function __clone()
	{
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'pqc' ), PQC_VERSION );
    }

    /**
     * Unserializing instances of this class is forbidden.
     * @since 1.6
     */
    public function __wakeup()
	{
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'pqc' ), PQC_VERSION );
    }

    /**
    * PQC Constructor
    * @since 1.0
    */
    public function __construct()
	{
        $this->define_constants();
        $this->hooks();
        do_action( 'pqc_loaded' );
    }

    /**
     * Define constant if not already set.
     *
     * @param  string $name
     * @param  string|bool $value
     * @since 1.6
     */
    private function define( $name, $value )
	{
        if ( ! defined( $name ) ) define( $name, $value );
    }

    /**
     * Define PQC Constants.
     */
    private function define_constants()
	{
        require_once( ABSPATH.'wp-admin/includes/plugin.php' );
        global $wpdb;

        $plugin_data = get_plugin_data( __FILE__ );

        $this->define( 'PQC_URL', plugin_dir_url( __FILE__ ) );
        $this->define( 'PQC_PATH', plugin_dir_path( __FILE__ ) );
        $this->define( 'PQC_PLUGIN', plugin_basename( __FILE__ ) );
        $this->define( 'PQC_NAME', $plugin_data['Name'] );
        $this->define( 'PQC_VERSION', $plugin_data['Version'] );
        $this->define( 'PQC_AUTHOR', $plugin_data['Author'] );
        $this->define( 'PQC_AUTHOR_URI', $plugin_data['AuthorURI'] );
        $this->define( 'PQC_PHANES_API_URI', 'https://phanes.co/' );
        $this->define( 'PQC_API_PHANES_URI', 'https://api.phanes.co/' );
		$this->define( 'PQC_SETTING_PAGE', 'admin.php?page=pqc-settings-page' );
        $this->define( 'PQC_SETTING_OPTIONS', 'pqc-options' );
        $this->define( 'PQC_MATERIALS_TABLE', $wpdb->prefix . 'pqc_materials' );
		$this->define( 'PQC_ORDERS_TABLE', $wpdb->prefix . 'pqc_orders' );
        $this->define( 'PQC_DATA_TABLE', $wpdb->prefix . 'pqc_data' );
        $this->define( 'PQC_UPLOAD_SHORTCODE', 'pqc_upload' );
        $this->define( 'PQC_CART_SHORTCODE', 'pqc_cart' );
        $this->define( 'PQC_CHECKOUT_SHORTCODE', 'pqc_checkout' );
        $this->define( 'PQC_ORDERS_SHORTCODE', 'pqc_orders' );
        $this->define( 'PQC_CONTENT_DIR', WP_CONTENT_DIR . "/uploads/pqc/" );
        $this->define( 'PQC_CONTENT_URL', WP_CONTENT_URL . "/uploads/pqc/" );
        $this->define( 'PQC_REQUIRES_WP', '4.1' );
    }

    /**
     * Hook into actions and filters.
     * @since  1.6
     */
    private function hooks()
	{
        add_action( 'init', array( $this, 'plugin_check' ), 1);
        add_action( 'init', array( $this, 'init' ), 1);
        add_action( 'plugins_loaded', array( $this, 'i18n' ), 0 );
        add_filter( "plugin_action_links_" . PQC_PLUGIN, array( $this, 'plugin_links' ) );
        add_action( 'wp_head', array( $this, 'js_detection' ), 0 );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
        add_filter( 'rewrite_rules_array', array( $this, 'add_rewrite_rules' ) );
    }

    /**
    * Check Plugin Requirements
    * @since 1.0
    */
    public function plugin_check()
	{
        if ( ! version_compare( $GLOBALS['wp_version'], PQC_REQUIRES_WP, '>=' ) ) {
            $this->add_notice(
                sprintf(
                    __( '<strong>%s</strong> plugin requires a
						<a href="http://wordpress.org/latest.zip">newer version</a>
						of WordPress to work properly.', 'pqc'
					),
                    PQC_NAME
                ), 'error', false
            );
            $this->plugin_inactive = true;
        }

        if ( version_compare( get_option( 'pqc-version' ), PQC_VERSION, '<' ) )
			$this->run_setup();

        if (
			! get_option( PQC_SETTING_OPTIONS, false ) ||
			! $this->table_exist( PQC_DATA_TABLE ) ||
			! $this->table_exist( PQC_MATERIALS_TABLE )
		) {
            if (
				get_option( 'pqc-version', 'not exist' ) == 'not exist' ||
				get_option( 'pqc-version' ) != PQC_VERSION
			) {
                if ( is_admin() && current_user_can( 'manage_options' ) ) $this->run_setup();
                $this->plugin_inactive = false;
            } else {
                $notice = $this->add_notice(
                    sprintf(
                        __( '<strong>%s</strong> needs some setup.
							<a href="%s">Run setup</a>', 'pqc'
						),
                        PQC_NAME,
                        add_query_arg( 'pqc-setup', 'setup' )
                    ), 'error', false
                );

                $this->plugin_inactive = true;
            }
        }
    }

    /**
    * Initialize the plugin
    * @since 1.0
    */
    public function init()
	{
        // Before init action.
        do_action( 'before_pqc_init' );

        // Init check
        $this->init_check();

        // Load GLOBAL Variables
        $this->globals();

        // Add Default Materials, if no material
        $this->insert_default_materials();

        // Remove Expired Files if we have any
        $this->remove_expired_files();

        // Include/Require Files
        $this->includes();

        // Init action.
        do_action( 'pqc_init' );
    }

    /**
    * Do the Init method check
    * @since 1.6
    *
    */
    private function init_check()
	{
        if (
			isset( $_REQUEST['pqc-setup'] ) &&
			$_REQUEST['pqc-setup'] == 'setup' &&
			is_admin() &&
			current_user_can( 'manage_options' )
		) { $this->run_setup(); }

        if ( $this->plugin_inactive ) return;

        if (
			isset( $_REQUEST['pqc-setup'] ) &&
			$_REQUEST['pqc-setup'] == 'complete' &&
			is_admin() &&
			current_user_can( 'manage_options' )
		) {
            $this->add_notice(
                sprintf(
                    __( 'Thank you for using <strong>%s</strong>.
						Setup is complete.', 'pqc'
					),
                    PQC_NAME
                ), 'updated pqc-inner-notice'
            );
        }

    }

    /**
    * PQC GLOBAL Variables
    * @since 1.1.1
    */
    private function globals()
	{
        $sections = array(
            'checkout_options' => array(
                'label' => 'Checkout Options',
                'callback' => array( 'PQC_Admin', 'checkout_section' ),
            ),
            'paypal' => array(
                'label' => 'PayPal',
                'callback' => array( 'PQC_Admin', 'paypal_section' ),
            ),
            'stripe' => array(
                'label' => 'Stripe',
                'callback' => array( 'PQC_Admin', 'stripe_section' ),
            ),

        );

        $payment_options = array();

        if ( pqc_is_paypal_ready( true ) ) {

            $payment_options['paypal'] = array(
                'label' => '<span>PayPal &nbsp; &nbsp;</span> <img style="width: 100px; height: 35px; vertical-align: middle;" src="' . PQC_URL . 'assets/images/paypal.png" alt="PayPal Standard">',
                'callback' => array(
                    'url'   => PQC_PATH . 'core/paypal.php',
                    'start' => array( 'PQC_PayPal', 'payment_start' ),
                    'end'   => array( 'PQC_PayPal', 'payment_end' )
                ),
                'desc' => __( 'Pay via PayPal; you can pay with your credit card if you don’t have a PayPal account.', 'pqc' ),
            );

        }

        if ( pqc_is_stripe_ready( true ) ) {

            $payment_options['stripe'] = array(
                'label' => '<span>Stripe &nbsp; &nbsp;</span> <img style="width: 100px; height: 35px; vertical-align: middle;" src="' . PQC_URL . 'assets/images/stripe.png" alt="Stripe Payment">',
                'callback' => array(
                    'url'   => PQC_PATH . 'core/stripe.php',
                    'start' => array( 'PQC_Stripe', 'payment_start' ),
                    'end'   => array( 'PQC_Stripe', 'payment_end' )
                ),
                'desc'      => __( 'Pay via Stripe; Pay with your credit card.', 'pqc' ),
            );

        }

        $tabs = array(
            // General Tab
            'general' => array(
                'label'     => __( 'General', 'pqc' ),
                'callback'  => array( 'PQC_Admin', 'general_tab' ),
            ),

            // Checkout Tab
            'checkout' => array(
                'label'     => __( 'Checkout', 'pqc' ),
                'callback'  => array( 'PQC_Admin', 'checkout_section' ),
                'sections'  => $sections + apply_filters( 'pqc_checkout_settings_sections', array() ),
            ),

        );

        $GLOBALS['pqc_settings_tabs'] = $tabs + apply_filters( 'pqc_settings_tabs', array() );
        $GLOBALS['pqc_payment_options'] = $payment_options + apply_filters( 'pqc_payment_options', array() );
        $GLOBALS['pqc_getting_started_tabs'] = array(
            'start'     => __( 'Getting Started', 'pqc' ),
            'about'     => __( 'About', 'pqc' ),
        );

    }

    /**
    * Loads reqiured files
    * @since 1.6
    */
    protected function includes()
	{
        require_once PQC_PATH . 'core/admin.php';

        if ( $this->plugin_inactive ) return;

        require_once PQC_PATH . 'core/public.php';
    }

    /**
    * Add query vars
    * @since 1.0
    * @param mixed $args
    */
    public function add_query_vars( array $args ) : array
	{
        array_push( $args, 'order_id', 'order_status', 'payment_method'/*, 'order_action'*/ );

        $flush = get_option( 'pqc-rewrite-flush' );

        if ( $flush != 1 ) {
            $this->flush_rewrite_rules();
            update_option( 'pqc-rewrite-flush', 1 );
        }

        return $args;
    }

    /**
    * Add rewrite rules
    * @since 1.0
    * @param mixed $rules
    */
    public function add_rewrite_rules( array $rules ) : array
	{
        $new_rules = array(
            'pqc-checkout/(processing|complete)/([a-z]+)/?$' => 'index.php?pagename=pqc-checkout&order_status=$matches[1]&payment_method=$matches[2]',
            // 'pqc-orders/(view-order)/([0-9]+)/?$' => 'index.php?pagename=pqc-orders&order_action=$matches[1]&order_id=$matches[2]',
        );

        $rules = $new_rules + $rules;
        return $rules;

    }

    /**
    * Add Notice
    * @since 1.0
    * @param mixed $msg The message to display
    * @param mixed $class The css class of notice, Accepts "updated and error".
    */
    public function add_notice( string $msg, string $class, bool $dismiss = true, bool $echo = false )
	{
        if ( ! is_admin() ) return;

        $dismiss_icon = $dismiss ?
            '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>' : '';

        $notice = '<div id="message" class="' . $class . ' notice' . ($dismiss ? ' is-dismissible' : '') . '"><p>' . $msg . '</p>' . $dismiss_icon . '</div>';

        if ( $echo === true )
            echo $notice;
        else
            self::$_notice .= $notice;
    }

    /**
    * Prepare/Set the default PQC option
    * @since 1.0
    */
    private function prepare_options()
	{
        $options = maybe_unserialize( get_option( PQC_SETTING_OPTIONS, false ) );

        $pqc_general_settings = array(
            'max_file_size'     => 50,
            'max_file_stay'     => 3,
            'max_file_upload'   => 10,
            'min_file_volume'   => 1,
            'initial_price'     => 1.00,
            'currency'          => 'USD',
            'currency_pos'      => 'left',
            'density_charge'    => 0,
        );

        $pqc_checkout_settings = array(
            'shop_location'             => 1,
            'checkout_option'           => 1,

            'paypal_active'             => 1,
            'paypal_client_id'          => '',
            'paypal_client_secret_key'  => '',
            'paypal_email'              => get_bloginfo( 'admin_email' ),
            'paypal_sandbox'            => 0,

            'stripe_active'             => 1,
            'stripe_secret_key'         => '',
            'stripe_publishable_key'    => '',
        );

		if ( ! $options ) {

            $data = array(
                'pqc_general_settings'		=> $pqc_general_settings,
                'pqc_checkout_settings'		=> $pqc_checkout_settings,
            );

            update_option( PQC_SETTING_OPTIONS, $data );

        } else {

            $data = array(
                'pqc_general_settings'  => wp_parse_args(
                    $options['pqc_general_settings'],
                    $pqc_general_settings
                ),
                'pqc_checkout_settings' => wp_parse_args(
                    $options['pqc_checkout_settings'],
                    $pqc_checkout_settings
                ),
            );

			update_option( PQC_SETTING_OPTIONS, $data );

            $count_generals		= count( $options['pqc_general_settings'] );
            $count_checkout		= count( $options['pqc_checkout_settings'] );

            if (
				$count_generals < count( $pqc_general_settings ) ||
				$count_checkout < count( $pqc_checkout_settings )
			) {
                $data = array(
                    'pqc_general_settings'  => wp_parse_args(
						$options['pqc_general_settings'],
						$pqc_general_settings
					),
                    'pqc_checkout_settings' => wp_parse_args(
						$options['pqc_checkout_settings'],
						$pqc_checkout_settings
					),
                );

                update_option( PQC_SETTING_OPTIONS, $data );
            }
        }

        update_option( 'pqc-rewrite-flush', 0 );

    }

    /**
    * Add default pages
    * @since 1.0
    */
    private function add_default_pages()
	{
        $pages = array(
            'upload'    => array( 'Upload', PQC_UPLOAD_SHORTCODE ),
            'cart'      => array( 'Cart', PQC_CART_SHORTCODE ),
            'checkout'  => array( 'Checkout', PQC_CHECKOUT_SHORTCODE ),
            'orders'    => array( 'Orders', PQC_ORDERS_SHORTCODE ),
        );

        // Create pages
        foreach( $pages as $name => $args ) {

            $name = 'pqc-' . sanitize_title( $name );

            if ( pqc_page_exists( $name ) !== 0 ) continue;

            wp_insert_post( array(
                'post_content'      => '[' . $args[1] . ']',
                'post_title'        => $args[0],
                'post_name'         => $name,
                'post_status'       => 'publish',
                'post_type'         => 'page',
                'comment_status'    => 'closed',
            ) );
        }

    }

    /**
    * Perform database table setup
    * @since 1.0
    */
    private function setup_db_tables()
	{
        global $wpdb;

        $current_time = current_time( 'mysql' );
        $table1 = PQC_DATA_TABLE;
        $table2 = PQC_MATERIALS_TABLE;
        $queries[] = "CREATE TABLE IF NOT EXISTS $table1 (
            ID bigint(255) unsigned NOT NULL AUTO_INCREMENT,
            unique_id varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            item_name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            item_data longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            user_ip varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            status varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
            date_created datetime NOT NULL DEFAULT '$current_time',
            expiry_date datetime NOT NULL DEFAULT '$current_time',
            PRIMARY KEY (ID)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;";

        $queries[] = "CREATE TABLE IF NOT EXISTS $table2 (
            ID bigint(255) unsigned NOT NULL AUTO_INCREMENT,
            author bigint(255) unsigned NOT NULL,
            material_name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            material_description longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            material_cost longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            material_density longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            date_created datetime NOT NULL DEFAULT '$current_time',
            date_modified datetime NOT NULL DEFAULT '$current_time',
            PRIMARY KEY (ID)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;";

        foreach ( $queries as $query ) {
            $result = $wpdb->query( $query );

            if ( $result ) continue;

            $this->add_notice( sprintf(
                __( '<strong>%s</strong> failed to run correctly. Please, contact the <a href="%s">Developer</a>.', 'pqc' ),
                PQC_NAME, PQC_AUTHOR_URI )
            );

            $this->plugin_inactive = true;

            break;
        }
    }

    /**
    * Insert Default materials if no material exists
    * @param bool $force Add materials whether there's result or no result
    * @since 1.0
    */
    private function insert_default_materials( $force = false )
	{
        $table = PQC_MATERIALS_TABLE;

        if ( ! $this->table_exist( $table ) ) return;

        global $wpdb;

        // Let's check if there's data in the quotes table and add data if there's nothing
        $result = $wpdb->get_var( "SELECT ID FROM $table" );

        if ( $result && ! $force ) return;

        $author = get_current_user_id();

        $materials = array(
            array( 'PP (poly propylene)', 0.90, '0.90' ),
            array( 'HiPS (polystyrene)', 1.03, '1.03' ),
            array( 'ABS', 1.04, '1.04' ),
            array( 'BendLay', 1.05, '1.05' ),
            array( 'E.P', 1.10, '1.10' ),
            array( 'Proto-Pasta Conductive', 1.15, '1.15' ),
            array( 'FilaFlex', 1.21, '1.21' ),
            array( 'PLA', 1.24, '1.24' ),
            array( 'PETG (Polyethylene)', 1.27, '1.27' ),
            array( 'Timberfill - Wood', 1.28, '1.28' ),
            array( 'Taulman Nylon 680 FDA', 1.28, '1.28' ),
            array( 'Proto-Pasta Carbon Fiber', 1.30, '1.30' ),
            array( 'PC (Polycarbonte)', 1.30, '1.30' ),
            array( 'PETT (T-Glase)', 1.45, '1.45' ),
            array( 'NylonStrong', 1.52, '1.52' ),
            array( 'Proto-Pasta Magnetic', 2.00, '2.00' ),
            array( 'Reflect-O-Lay', 2.31, '2.31' ),
            array( 'Proto-Pasta Stainless Steel', 2.70, '2.70' ),
        );

        $date = current_time( 'mysql' );

        foreach( $materials as $material ) {

            $name = $material[0];
            $cost = $material[1];
            $dens = $material[2];
            $desc = "Material Density: {$dens}g/cubic cm";

            $data[] = "( $author, '$name', '$desc', '$cost', '$dens', '$date', '$date' )";
        }

        $data = implode( ', ', $data );

        $sql = "INSERT INTO $table
        ( author, material_name, material_description, material_cost, material_density, date_created, date_modified )
        VALUES $data";

        $add = $wpdb->query( $sql );
    }

    /**
    * Check if database table exists
    * @since 1.0
    * @param mixed $table_name
    */
    private function table_exist( string $table_name ) : bool
	{
        global $wpdb;
        $check = "SHOW TABLES LIKE '$table_name'";
        return ! empty( $wpdb->get_var( $check ) );
    }

    /**
    * Check if database colum exist in table
    * @since 1.6
    * @param mixed $table_name
    * @param mixed $column_name
    */
    private function column_exist( $table_name, $column_name )
	{
        global $wpdb;

        if ( ! $this->table_exist( $table_name ) ) return false;

        $check = "SHOW COLUMNS FROM `$table_name` LIKE '$column_name';";

        return ! empty( $wpdb->get_var( $check ) );
    }

    /**
    * Run Setup
    * @since 1.0
    */
    public function run_setup()
	{
        // Setup Database Tables
        $this->setup_db_tables();

        // Prepare options
        $this->prepare_options();

        // Add default pages
        $this->add_default_pages();

        if (
			isset( $_REQUEST['pqc-add-default-materials'] ) &&
			$_REQUEST['pqc-add-default-materials'] == 1
		) {
            // Add Default Materials, if no material
            $this->insert_default_materials( true );
        }

        $license = trim(get_option( 'pqc-license' ));

        if ( empty( $license ) ) {
            $license = array(
                'check_time'    => strtotime( "+1 week" ),
            );
            update_option( 'pqc-license', $license );
        }

        if (
			get_option( 'pqc-version', 'not exist' ) == 'not exist' ||
			get_option( 'pqc-version' ) != PQC_VERSION
		) {
            if (
				! get_option( 'pqc-version' ) ||
				get_option( 'pqc-version', 'not exist' ) == 'not exist'
			) {

                $url = 'admin.php?page=pqc-start';

			} elseif (
				version_compare( get_option( 'pqc-version' ), PQC_VERSION, '<' )
			) {

                $url = 'admin.php?page=pqc-start&pqc-upgrade=1';

            }

            update_option( 'pqc-version', PQC_VERSION );

            exit( wp_redirect( admin_url( $url ) ) );

        } else {
            wp_redirect(
				admin_url(
					'admin.php?page=pqc-settings-page&pqc-setup=complete'
				)
			);
			exit;
        }

    }

    /**
    * Flushes Rewrite rules
    * @since 1.0
    *
    */
    private function flush_rewrite_rules()
	{
        // Let's flush rewrite rules
        flush_rewrite_rules( true );
    }

    /**
    * Check and remove expired files and data
    * @since 1.1.1
    */
    private function remove_expired_files()
	{
        global $wpdb;

        $table = PQC_DATA_TABLE;

        if ( ! $this->table_exist( $table ) ) return;

        $current_time = current_time( 'mysql' );
		$query = "
		SELECT unique_id FROM $table
		WHERE expiry_date <= STR_TO_DATE( '$current_time', '%Y-%m-%d %H:%i:%s' );
		";
        $results = $wpdb->get_results( $query );

        if ( ! $results || empty( $results ) ) return;

        foreach( $results as $result ) {
            $file = PQC_CONTENT_DIR . $result->unique_id . ".stl";
            if ( file_exists( $file ) ) unlink( $file );
        }

		$query = "
		DELETE FROM $table
		WHERE expiry_date <= STR_TO_DATE( '$current_time', '%Y-%m-%d %H:%i:%s' );
		";
        $wpdb->query( $query );
    }

    /**
    * Check if we have License
    * @return bool
    */
    public function has_valid_license() : bool
	{
        $license = get_option( 'pqc-license', false );

        if ( ! $license || empty( $license ) )
			return false;

        if ( empty( $license['code'] ) || strlen( $license['code'] ) === 20 )
			return false;

        if ( empty( $license['time'] ) || ! is_int( $license['time'] ) || $license['time'] < strtotime( current_time( 'mysql' ) ) )
			return false;

        if ( empty( $license['check_time'] ) || ! is_int( $license['check_time'] ) )
			return false;

        if ( $license['check_time'] <= strtotime( current_time( 'mysql' ) ) && ! $this->check_license( $license['code'] ) ) return false;

        return true;
    }

    /**
    * Checks for License
    *
    * @param string $code The License code
    * @return bool
    */
    public function check_license( string $code ) : bool
	{
        require_once PQC_PATH . 'core/lib/wooskey-manager/wooskey-manager.php';

        $wooskey = new WoosKey_Manager( PQC_PHANES_API_URI, $code );
        $response = $wooskey->check_license();

        if ( $response === true ) {
            $this->license_response = array(
                'code'      => $code,
                'msg'       => $wooskey->response_msg,
                'expires'   => $wooskey->response_expires,
            );
            return true;

        } elseif ( $response == null ) {
            $this->license_response = array(
                'code'  => $code,
                'msg'   => $wooskey->response_msg,
            );
            return false;
        }

        $this->license_response = array(
            'code'  => $code,
            'msg'   => $wooskey->response_msg,
        );

        return false;
    }

    /**
    * Handles JavaScript detection.
    *
    * Adds a `js` class to the root `<html>` element when JavaScript is detected.
    */
    public function js_detection()
	{
        echo '
		<script>
		let stateCheck=setInterval(()=>{
			if(document.readyState===\'complete\'){
				clearInterval(stateCheck);
				document.documentElement.className+=\' js\';
			}
		},1);
		</script>
		<noscript>
			<style>
				.inputfile + label{
					display:none;
				}
			</style>
		</noscript>
		' . "\n" ;
    }

    /**
    * Internationalization
    *
    */
    public function i18n()
	{
        load_plugin_textdomain( 'pqc', false, dirname( PQC_PLUGIN ) . '/languages/' );
    }

    /**
    * Add Extra Links for Plugin
    *
    * @param mixed $links
    */
    public function plugin_links( array $links ) : array
	{
        $settings_link = '
			<a href="' . PQC_SETTING_PAGE . '">' .
				__( 'Settings', 'pqc' ) .
			'</a>';
        $refresh_link = '
			<a id="pqc-reset-btn" style="color:#b66517;" href="' .
				add_query_arg( 'pqc-setup', 'setup' ) . '">' .
				__( 'Reset Plugin', 'pqc' ) .
			'</a>';

        array_unshift( $links, $settings_link );
        array_push( $links, $refresh_link );

        return $links;
    }

}

endif;

require_once 'functions.php';

$GLOBALS['pqc'] = PQC();
