<?php
/**
 * Submission Auth
 * Handles user authentication (login/logout/registration)
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
     * The single entry point called by the Master File
     */
    public static function init() {
        $instance = new self();
        
        // Hooks for logic
        add_action( 'init', [ $instance, 'handle_login' ] );
        add_action( 'init', [ $instance, 'handle_registration' ] );
        add_action( 'init', [ $instance, 'handle_logout' ] );
        
        // Hooks for shortcodes
        add_shortcode( 'afcglide_login', [ $instance, 'display_login_form' ] );
        add_shortcode( 'afcglide_register', [ $instance, 'display_register_form' ] );
        add_shortcode( 'afcglide_logout_link', [ $instance, 'display_logout_link' ] );
        add_shortcode( 'afcglide_user_status', [ $instance, 'display_user_status' ] );
    }

    /**
     * Display login form
     */
    public function display_login_form() {
        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            return sprintf(
                '<div class="afcglide-logged-in-message" style="background:#d4edda; padding:20px; border-radius:5px;">
                    <p>You are logged in as <strong>%s</strong>.</p>
                    <p><a href="%s">Logout</a> | <a href="%s">Submit a Listing</a></p>
                </div>',
                esc_html( $current_user->display_name ),
                esc_url( self::get_logout_url() ),
                esc_url( home_url( '/submit-listing/' ) )
            );
        }

        ob_start(); ?>
        <div class="afcglide-auth-form">
            <h3>Agent Login</h3>
            <?php if ( class_exists( 'AFCGlide\Listings\Helpers\Message_Helper' ) ) echo Message_Helper::display(); ?>
            <form method="post" action="">
                <?php wp_nonce_field( 'afcglide_agent_login_action', 'afcglide_agent_login_nonce' ); ?>
                <p><input type="email" name="afc_email" placeholder="Email Address" required style="width:100%; padding:10px; margin-bottom:10px;"></p>
                <p><input type="password" name="afc_password" placeholder="Password" required style="width:100%; padding:10px; margin-bottom:10px;"></p>
                <button type="submit" class="afcglide-button" style="width:100%; background:#0073aa; color:white; padding:10px; border:none; border-radius:3px; cursor:pointer;">Login</button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle login form submission
     */
    public function handle_login() {
        if ( ! isset( $_POST['afcglide_agent_login_nonce'] ) ) return;

        if ( ! wp_verify_nonce( $_POST['afcglide_agent_login_nonce'], 'afcglide_agent_login_action' ) ) {
            Message_Helper::error( __( 'Security check failed.', 'afcglide' ) );
            return;
        }

        $email    = sanitize_email( $_POST['afc_email'] ?? '' );
        $password = $_POST['afc_password'] ?? '';

        $user = get_user_by( 'email', $email );

        if ( $user && wp_check_password( $password, $user->data->user_pass, $user->ID ) ) {
            wp_set_current_user( $user->ID );
            wp_set_auth_cookie( $user->ID, true );
            wp_safe_redirect( home_url( '/submit-listing/' ) );
            exit;
        } else {
            Message_Helper::error( __( 'Invalid email or password.', 'afcglide' ) );
        }
    }

    public function display_register_form() {
        return '<h3>Register as an Agent</h3><p>Registration form goes here.</p>';
    }

    public function handle_registration() { /* Your registration logic */ }

    public function handle_logout() {
        if ( isset( $_GET['afcglide_logout'] ) && $_GET['afcglide_logout'] === '1' ) {
            wp_logout();
            wp_safe_redirect( home_url( '/listing-login/' ) );
            exit;
        }
    }

    public static function get_logout_url() {
        return add_query_arg( 'afcglide_logout', '1', home_url() );
    }

    public function display_logout_link() { /* Logic for logout link */ }
    public function display_user_status() { /* Logic for user status */ }
}