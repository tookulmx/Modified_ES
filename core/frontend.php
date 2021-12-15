<?php

namespace PQC;

defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * PQC Frontend
 *
 * @package PQC
 */
class Frontend
{
    private static $shortcode_render_counter = 1;
    /**
     * Constructor
     */
    public function __construct()
    {
        if ( PQC()->inactive ) return;

        add_shortcode( 'phanes3dp', [ $this, 'shortcode' ] );
        add_shortcode( 'phanes_3dp', [ $this, 'shortcode' ] );
    }

    public function shortcode( $args = [] )
    {
		if ( self::$shortcode_render_counter > 1 ) return;

		++self::$shortcode_render_counter;

        $args = (object) shortcode_atts( [
            'not_logged_in' => __( 'You are not logged in. Please log in to view your dashboard.', 'bryllup' ),
        ], $args );

        // error_log($this->prepare_shortcode('phanes3dp', $args));

        ob_start();

        require_once PQC()->path . 'templates/frontend/widget.php';

        return $this->send_response( ob_get_clean() );
    }

    private function prepare_shortcode( $tag, $args )
    {
        $args = (array) $args;

        $content = join(' ', array_map(function($key, $value) {
            return "$key=\"$value\"";
        }, array_keys($args), $args));

        return "[$tag $content]";
    }

    public function send_response( $content )
    {
        // Remove newline whitespaces
        return preg_replace('~>\s*\n\s*<~', '><', $content);
    }
}