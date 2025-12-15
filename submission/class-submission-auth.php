<?php
/**
 * Submission Auth
 * Handles user authentication (login/logout)
 *
 * @package AFCGlide\Listings\Submission
 */

namespace AFCGlide\Listings\Submission;

use AFCGlide\Listings\Helpers\Validator;
use AFCGlide\Listings\Helpers\Sanitizer;
use AFCGlide\Listings\Helpers\Message_Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

class Submission_Auth {

    /**
     * Initialize auth handlers
     */
    public function __construct() {
        add_action( 'init', [ $this, 'handle_login' ] );
        add_action( 'init', [ $this, 'handle_logout' ] );
    }

    /**
     * Handle login form submission
     */
    public function handle_login() {
        // Check if login form was submitted
        if ( ! isset( $_POST['afcglide_agent_login_nonce'] ) ) {
            return;
        }

        // Verify nonce
        if ( ! Validator::nonce( $_POST['afcglide_agent_login_nonce'], 'afcglide_agent_login_action' ) ) {
            Message_Helper::error( __( 'Security check failed. Please try again.', 'afcglide' ) );
            return;
        }

        // Get and sanitize credentials
        $email    = Sanitizer::email( $_POST['afc_email'] ?? '' );
        $password = $_POST['afc_password'] ?? '';

        // Validate required fields
        if ( ! Validator::required( $email ) || ! Validator::required( $password ) ) {
            Message_Helper::error( __( 'Please enter both email and password.', 'afcglide' ) );
            return;
        }

        // Validate email format
        if ( ! Validator::email( $email ) ) {
            Message_Helper::error( __( 'Please enter a valid email address.', 'afcglide' ) );
            return;
        }

        // Attempt authentication
        $user = $this->authenticate_user( $email, $password );

        if ( is_wp_error( $user ) ) {
            Message_Helper::error( $user->get_error_message() );
            return;
        }

        // Check user capability (optional - uncomment if needed)
        // if ( ! $this->user_can_submit( $user ) ) {
        //     Message_Helper::error( __( 'You do not have permission to submit listings.', 'afcglide' ) );
        //     return;
        // }

        // Log the user in
        $this->login_user( $user );

        // Redirect after successful login
        $this->redirect_after_login( $user );
    }

    /**
     * Authenticate user with WordPress
     */
    private function authenticate_user( $email, $password ) {
        $user = wp_authenticate( $email, $password );

        if ( is_wp_error( $user ) ) {
            return new \WP_Error( 
                'authentication_failed', 
                __( 'Invalid email or password. Please try again.', 'afcglide' ) 
            );
        }

        return $user;
    }

    /**
     * Check if user has permission to submit listings
     */
    private function user_can_submit( $user ) {
        // Check if user has specific capability
        // Modify this based on your requirements
        return user_can( $user, 'edit_posts' );
    }

    /**
     * Log user in
     */
    private function login_user( $user ) {
        wp_set_current_user( $user->ID );
        wp_set_auth_cookie( $user->ID, true );
        
        // Trigger WordPress login action
        do_action( 'wp_login', $user->user_login, $user );
        do_action( 'afcglide_user_logged_in', $user );
    }

    /**
     * Redirect user after successful login
     */
    private function redirect_after_login( $user ) {
        // Default redirect URL
        $redirect_url = home_url( '/submit-listing/' );

        // Allow filtering of redirect URL
        $redirect_url = apply_filters( 'afcglide_login_redirect', $redirect_url, $user );

        // Check if there's a redirect_to parameter
        if ( isset( $_GET['redirect_to'] ) ) {
            $redirect_url = esc_url_raw( $_GET['redirect_to'] );
        }

        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * Handle logout
     */
    public function handle_logout() {
        // Check if logout was requested
        if ( ! isset( $_GET['afcglide_logout'] ) || $_GET['afcglide_logout'] !== '1' ) {
            return;
        }

        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            return;
        }

        // Perform logout
        wp_logout();

        // Set success message
        Message_Helper::success( __( 'You have been logged out successfully.', 'afcglide' ) );

        // Redirect to login page
        $redirect_url = home_url( '/agent-login/' );
        $redirect_url = apply_filters( 'afcglide_logout_redirect', $redirect_url );

        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * Check if current user is logged in
     */
    public static function is_logged_in() {
        return is_user_logged_in();
    }

    /**
     * Get current user
     */
    public static function get_current_user() {
        return wp_get_current_user();
    }

    /**
     * Get current user ID
     */
    public static function get_current_user_id() {
        return get_current_user_id();
    }

    /**
     * Check if current user can submit listings
     */
    public static function can_submit() {
        if ( ! self::is_logged_in() ) {
            return false;
        }

        // Add your capability check here
        return current_user_can( 'edit_posts' );
    }

    /**
     * Get login URL with optional redirect
     */
    public static function get_login_url( $redirect_to = '' ) {
        $login_url = home_url( '/agent-login/' );

        if ( $redirect_to ) {
            $login_url = add_query_arg( 'redirect_to', urlencode( $redirect_to ), $login_url );
        }

        return $login_url;
    }

    /**
     * Get logout URL
     */
    public static function get_logout_url() {
        $current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return add_query_arg( 'afcglide_logout', '1', $current_url );
    }

    /**
     * Initialize (called by main plugin)
     */
    public static function init() {
        new self();
    }
}