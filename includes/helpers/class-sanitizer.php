<?php
/**
 * Sanitizer Helper
 * Cleans user input using WordPress sanitization functions
 *
 * @package AFCGlide\Listings\Helpers
 */

namespace AFCGlide\Listings\Helpers;

if ( ! defined( 'ABSPATH' ) ) exit;

class Sanitizer {

    /**
     * Sanitize text field
     */
    public static function text( $value ) {
        return sanitize_text_field( $value );
    }

    /**
     * Sanitize email
     */
    public static function email( $value ) {
        return sanitize_email( $value );
    }

    /**
     * Sanitize textarea (preserves line breaks)
     */
    public static function textarea( $value ) {
        return sanitize_textarea_field( $value );
    }

    /**
     * Sanitize HTML content (allows safe HTML tags)
     */
    public static function html( $value ) {
        return wp_kses_post( $value );
    }

    /**
     * Sanitize URL
     */
    public static function url( $value ) {
        return esc_url_raw( $value );
    }

    /**
     * Sanitize integer
     */
    public static function int( $value ) {
        return absint( $value );
    }

    /**
     * Sanitize float/decimal
     */
    public static function float( $value ) {
        return floatval( $value );
    }

    /**
     * Sanitize price (removes non-numeric except decimal)
     */
    public static function price( $value ) {
        // Remove all except numbers, decimal, comma
        $cleaned = preg_replace( '/[^0-9.,]/', '', $value );
        // Convert to float
        return floatval( str_replace( ',', '', $cleaned ) );
    }

    /**
     * Sanitize phone number
     */
    public static function phone( $value ) {
        // Keep only numbers, spaces, dashes, parentheses, plus
        return preg_replace( '/[^0-9\s\-\(\)\+]/', '', $value );
    }

    /**
     * Sanitize slug/key
     */
    public static function key( $value ) {
        return sanitize_key( $value );
    }

    /**
     * Sanitize title
     */
    public static function title( $value ) {
        return sanitize_title( $value );
    }

    /**
     * Sanitize file name
     */
    public static function file_name( $value ) {
        return sanitize_file_name( $value );
    }

    /**
     * Sanitize array of text fields
     */
    public static function text_array( $array ) {
        if ( ! is_array( $array ) ) {
            return [];
        }
        return array_map( 'sanitize_text_field', $array );
    }

    /**
     * Sanitize checkbox (returns 1 or 0)
     */
    public static function checkbox( $value ) {
        return $value ? 1 : 0;
    }

    /**
     * Sanitize boolean
     */
    public static function boolean( $value ) {
        return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
    }

    /**
     * Sanitize meta key
     */
    public static function meta_key( $value ) {
        return sanitize_key( $value );
    }

    /**
     * Sanitize hex color
     */
    public static function hex_color( $value ) {
        return sanitize_hex_color( $value );
    }

    /**
     * Sanitize class name
     */
    public static function html_class( $value ) {
        return sanitize_html_class( $value );
    }

    /**
     * Strip all tags
     */
    public static function strip_tags( $value ) {
        return wp_strip_all_tags( $value );
    }

    /**
     * Sanitize for database storage
     */
    public static function for_db( $value ) {
        global $wpdb;
        return $wpdb->prepare( '%s', $value );
    }

    /**
     * Sanitize array recursively
     */
    public static function array_recursive( $array, $sanitize_function = 'sanitize_text_field' ) {
        if ( ! is_array( $array ) ) {
            return call_user_func( $sanitize_function, $array );
        }

        foreach ( $array as $key => $value ) {
            if ( is_array( $value ) ) {
                $array[ $key ] = self::array_recursive( $value, $sanitize_function );
            } else {
                $array[ $key ] = call_user_func( $sanitize_function, $value );
            }
        }

        return $array;
    }

    /**
     * Get sanitized POST value
     */
    public static function post( $key, $default = '', $sanitize_function = 'sanitize_text_field' ) {
        if ( ! isset( $_POST[ $key ] ) ) {
            return $default;
        }
        return call_user_func( $sanitize_function, $_POST[ $key ] );
    }

    /**
     * Get sanitized GET value
     */
    public static function get( $key, $default = '', $sanitize_function = 'sanitize_text_field' ) {
        if ( ! isset( $_GET[ $key ] ) ) {
            return $default;
        }
        return call_user_func( $sanitize_function, $_GET[ $key ] );
    }

    /**
     * Sanitize JSON string
     */
    public static function json( $value ) {
        $decoded = json_decode( $value, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return null;
        }
        return $decoded;
    }
}