<?php

namespace PQC;

defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

use WP_Error;

/**
 * PQC Backend
 *
 * @package PQC
 */
class Backend
{
    /**
    * The Constructor
    */
    public function __construct()
    {
        if ( ! is_admin() ) return;

        add_action( 'admin_head', [ $this, 'admin_head' ], 9999 );
        add_action( 'admin_notices', [ $this, 'display_notices' ], 9999 );
        add_filter( 'plugin_action_links_' . PQC()->basename, [ $this, 'plugin_links' ] );

        if ( PQC()->inactive ) return;

        add_action( 'admin_menu', [ $this, 'admin_menu' ], 9 );
        add_action( 'admin_enqueue_scripts',  [ $this, 'admin_scripts' ], 10 );
        add_action( 'enqueue_block_editor_assets',  [ $this, 'enqueue_block' ], 10 );

        add_filter( 'mce_buttons',  function( $buttons ) {
            array_push( $buttons, 'separator', PQC()->slug );
            return $buttons;
        } );

        add_filter( 'mce_external_plugins',  function( $plugins ) {
            $plugins[ PQC()->slug ] = PQC()->uri . 'assets/js/editor-classic.js';
            return $plugins;
        } );
    }

    /**
    * Display registered notices
    */
    public function display_notices()
    {
        echo PQC()::$_notice;
    }

    /**
    * Admin Head content
    */
    public function admin_head()
    {
        ?>
        <style>
        .pqc-update-nag {
            display: block !important;
            padding: 1px 12px !important;
            border-left-color: #0288d1;
        }
        </style>
        <?php

		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) return;
    }

    /**
    * Add Extra Links for Plugin
    *
    * @param mixed $links
    */
    public function plugin_links( $links )
	{
        $settings_link = '
			<a href="' . admin_url( 'admin.php?page=pqc' ) . '">' .
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
    
    /**
    * Admin Menu
    */
	public function admin_menu()
    {
        add_menu_page(
            'Phanes 3DP',
            'Phanes 3DP',
            'manage_options',
            PQC()->slug,
            [ $this, 'settings' ],
            PQC()->uri . 'assets/images/icon.png',
            42.28473
        );
	}

    /**
    * Load Admin page
    */
    public function settings()
    {
        if ( isset( $_POST['pqc_submit'] ) ) {

            if ( ! check_admin_referer( 'pqc_nonce', 'pqc_settings' ) ) return;

            $error = false;

            foreach ( $_POST as $name => $value ) {

                if ( strpos( $name, 'pqc_' ) === false ) continue;

                if ( $value == '' ) {
                    $error = true;

                    break;
                }
            }

            if ( ! $error ) {

                $args = wp_parse_args( [
                    'merchant_id'   => sanitize_text_field( $_POST['pqc_merchant_id'] ),
                    'access_token'  => sanitize_text_field( $_POST['pqc_access_token'] ),
                ], to_array( get_option( 'pqc-options' ) ) );

                update_option( 'pqc-options', $args );

                PQC()->add_notice( __( '<strong> Done! </strong> Settings saved.', 'pqc' ), 'updated', true, true );

            } else {

                PQC()->add_notice( __( '<strong> Doing wrong! </strong> All fields are required.', 'pqc' ), 'error', true, true );

            }

        }

        require_once PQC()->path . 'templates/backend/settings.php';
    }

    /**
     * Add new Guternberg block for phanes3dp shortcode
     */
    public function enqueue_block()
    {
        wp_enqueue_script( PQC()->name . ' block', PQC()->uri . 'assets/js/editor-block.js', [ 'wp-blocks', 'wp-editor' ], PQC()->version, true );
    }

    /**
    * Enqueue Admin Scripts
    */
    public function admin_scripts()
    {
        $screen = get_current_screen();

        if ( ! isset( $screen->id ) || strstr( $screen->id, 'pqc' ) == false ) return;

        /**
        * Enqueue Styles
        */
        wp_enqueue_style( PQC()->name, PQC()->uri . 'assets/css/backend.css', [], PQC()->version, 'all' );

        /**
        * Enqueue Scripts
        */
        // wp_enqueue_media();

        wp_enqueue_script( PQC()->name . ' URL SCRIPT', PQC()->uri . 'assets/js/uri.min.js', [], PQC()->version, true );
        wp_enqueue_script( PQC()->name . ' URL MOD', PQC()->uri . 'assets/js/urlmod.js', [ PQC()->name . ' URL SCRIPT' ], PQC()->version, true );
        wp_enqueue_script( PQC()->name, PQC()->uri . 'assets/js/backend.js', [], PQC()->version, true );
        $params = [ 'action', '_wpnonce', 'pqc-setup' ];

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
        array_push( $params, apply_filters( 'pqc_mod_params', [] ) );

        wp_localize_script( PQC()->name . ' URL MOD', 'PQC_Backend', [
                'do_url_mod' => $do_url_mod === false ? false : true,
                'url_params' => $params,
            ]
        );
    }
}