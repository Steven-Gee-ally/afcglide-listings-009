<?php
/**
 * Message Helper
 * Handles success/error messages using transients (no sessions needed)
 *
 * @package AFCGlide\Listings\Helpers
 */

namespace AFCGlide\Listings\Helpers;

if ( ! defined( 'ABSPATH' ) ) exit;

class Message_Helper {

    const TRANSIENT_PREFIX = 'afcglide_msg_';
    const TRANSIENT_EXPIRY = 60; // 60 seconds

    /**
     * Set a message
     */
    public static function set( $message, $type = 'info' ) {
        $user_id = get_current_user_id();
        
        // Use user ID if logged in, otherwise use IP hash
        $key = $user_id ? $user_id : self::get_visitor_key();
        
        $transient_name = self::TRANSIENT_PREFIX . $key;
        
        set_transient( $transient_name, [
            'text' => $message,
            'type' => $type,
        ], self::TRANSIENT_EXPIRY );
    }

    /**
     * Get and delete message (one-time display)
     */
    public static function get() {
        $user_id = get_current_user_id();
        $key = $user_id ? $user_id : self::get_visitor_key();
        
        $transient_name = self::TRANSIENT_PREFIX . $key;
        $message = get_transient( $transient_name );
        
        if ( $message ) {
            delete_transient( $transient_name );
            return $message;
        }
        
        return null;
    }

    /**
     * Set success message
     */
    public static function success( $message ) {
        self::set( $message, 'success' );
    }

    /**
     * Set error message
     */
    public static function error( $message ) {
        self::set( $message, 'error' );
    }

    /**
     * Set warning message
     */
    public static function warning( $message ) {
        self::set( $message, 'warning' );
    }

    /**
     * Set info message
     */
    public static function info( $message ) {
        self::set( $message, 'info' );
    }

    /**
     * Display message HTML
     */
    public static function display() {
        $message = self::get();
        
        if ( ! $message ) {
            return '';
        }

        $type = esc_attr( $message['type'] );
        $text = esc_html( $message['text'] );

        return sprintf(
            '<div class="afcglide-message afcglide-message-%s">%s</div>',
            $type,
            $text
        );
    }

    /**
     * Check if message exists
     */
    public static function has_message() {
        $user_id = get_current_user_id();
        $key = $user_id ? $user_id : self::get_visitor_key();
        $transient_name = self::TRANSIENT_PREFIX . $key;
        
        return get_transient( $transient_name ) !== false;
    }

    /**
     * Clear message without displaying
     */
    public static function clear() {
        $user_id = get_current_user_id();
        $key = $user_id ? $user_id : self::get_visitor_key();
        $transient_name = self::TRANSIENT_PREFIX . $key;
        
        delete_transient( $transient_name );
    }

    /**
     * Get unique visitor key (for non-logged-in users)
     */
    private static function get_visitor_key() {
        $ip = self::get_client_ip();
        $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
        
        // Create a hash from IP + User Agent
        return md5( $ip . $user_agent );
    }

    /**
     * Get client IP address
     */
    private static function get_client_ip() {
        $ip = '';
        
        if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field( $ip );
    }

    /**
     * Render message from WP_Error object
     */
    public static function from_wp_error( $wp_error ) {
        if ( ! is_wp_error( $wp_error ) ) {
            return;
        }

        $message = $wp_error->get_error_message();
        self::error( $message );
    }
}