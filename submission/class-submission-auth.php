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
     * Initialize auth handlers
     */
    public function __construct() {
        add_action( 'init', [ $this, 'handle_login' ] );
        add_action( 'init', [ $this, 'handle_registration' ] );
        add_action( 'init', [ $this, 'handle_logout' ] );
        
        // Add shortcodes
        add_action( 'init', [ $this, 'register_shortcodes' ] );
    }

    /**
     * Register authentication shortcodes
     */
    public function register_shortcodes() {
        add_shortcode( 'afcg_login', [ $this, 'display_login_form' ] );
        add_shortcode( 'afcg_register', [ $this, 'display_register_form' ] );
        add_shortcode( 'afcg_logout_link', [ $this, 'display_logout_link' ] );
        add_shortcode( 'afcg_user_status', [ $this, 'display_user_status' ] );
    }

    /**
     * Display login form
     */
    public function display_login_form( $atts = [] ) {
        // If user is already logged in
        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            return sprintf(
                '<div class="afcglide-logged-in-message">
                    <p>%s</p>
                    <p><a href="%s" class="afcglide-button">%s</a> | <a href="%s" class="afcglide-button">%s</a></p>
                </div>',
                sprintf(
                    __( 'You are already logged in as <strong>%s</strong>.', 'afcglide' ),
                    esc_html( $current_user->display_name )
                ),
                esc_url( self::get_logout_url() ),
                __( 'Logout', 'afcglide' ),
                esc_url( home_url( '/submit-listing/' ) ),
                __( 'Submit a Listing', 'afcglide' )
            );
        }

        ob_start();
        ?>
        <div class="afcglide-auth-form afcglide-login-form">
            <h3><?php _e( 'Agent Login', 'afcglide' ); ?></h3>
            
            <?php echo Message_Helper::display(); ?>
            
            <form method="post" action="">
                <?php wp_nonce_field( 'afcglide_agent_login_action', 'afcglide_agent_login_nonce' ); ?>
                
                <div class="afcglide-form-group">
                    <label for="afc_email"><?php _e( 'Email Address', 'afcglide' ); ?> *</label>
                    <input type="email" id="afc_email" name="afc_email" required>
                </div>
                
                <div class="afcglide-form-group">
                    <label for="afc_password"><?php _e( 'Password', 'afcglide' ); ?> *</label>
                    <input type="password" id="afc_password" name="afc_password" required>
                </div>
                
                <div class="afcglide-form-group">
                    <label>
                        <input type="checkbox" name="remember_me" value="1">
                        <?php _e( 'Remember me', 'afcglide' ); ?>
                    </label>
                </div>
                
                <div class="afcglide-form-group">
                    <button type="submit" class="afcglide-button afcglide-button-primary">
                        <?php _e( 'Login', 'afcglide' ); ?>
                    </button>
                </div>
                
                <div class="afcglide-auth-links">
                    <p>
                        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
                            <?php _e( 'Lost your password?', 'afcglide' ); ?>
                        </a>
                    </p>
                    <?php if ( get_option( 'users_can_register' ) ) : ?>
                    <p>
                        <a href="<?php echo esc_url( home_url( '/register/' ) ); ?>">
                            <?php _e( 'Create an account', 'afcglide' ); ?>
                        </a>
                    </p>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <style>
        .afcglide-auth-form {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .afcglide-form-group {
            margin-bottom: 15px;
        }
        .afcglide-form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .afcglide-form-group input[type="email"],
        .afcglide-form-group input[type="password"],
        .afcglide-form-group input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .afcglide-button {
            padding: 10px 20px;
            background: #0073aa;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .afcglide-button-primary {
            background: #0073aa;
        }
        .afcglide-button:hover {
            background: #005a87;
        }
        .afcglide-auth-links {
            margin-top: 15px;
            font-size: 0.9em;
        }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Display registration form
     */
    public function display_register_form( $atts = [] ) {
        // Check if registration is allowed
        if ( ! get_option( 'users_can_register' ) ) {
            return '<p class="afcglide-error">' . __( 'User registration is currently disabled.', 'afcglide' ) . '</p>';
        }

        // If user is already logged in
        if ( is_user_logged_in() ) {
            return '<p>' . __( 'You are already logged in.', 'afcglide' ) . '</p>';
        }

        ob_start();
        ?>
        <div class="afcglide-auth-form afcglide-register-form">
            <h3><?php _e( 'Register as an Agent', 'afcglide' ); ?></h3>
            
            <?php echo Message_Helper::display(); ?>
            
            <form method="post" action="">
                <?php wp_nonce_field( 'afcglide_agent_register_action', 'afcglide_agent_register_nonce' ); ?>
                
                <div class="afcglide-form-group">
                    <label for="afc_reg_username"><?php _e( 'Username', 'afcglide' ); ?> *</label>
                    <input type="text" id="afc_reg_username" name="username" required>
                    <small><?php _e( 'This will be your login name', 'afcglide' ); ?></small>
                </div>
                
                <div class="afcglide-form-group">
                    <label for="afc_reg_email"><?php _e( 'Email Address', 'afcglide' ); ?> *</label>
                    <input type="email" id="afc_reg_email" name="email" required>
                </div>
                
                <div class="afcglide-form-group">
                    <label for="afc_reg_password"><?php _e( 'Password', 'afcglide' ); ?> *</label>
                    <input type="password" id="afc_reg_password" name="password" required>
                </div>
                
                <div class="afcglide-form-group">
                    <label for="afc_reg_password2"><?php _e( 'Confirm Password', 'afcglide' ); ?> *</label>
                    <input type="password" id="afc_reg_password2" name="password2" required>
                </div>
                
                <div class="afcglide-form-group">
                    <button type="submit" name="afcglide_register" class="afcglide-button afcglide-button-primary">
                        <?php _e( 'Register', 'afcglide' ); ?>
                    </button>
                </div>
                
                <div class="afcglide-auth-links">
                    <p>
                        <?php _e( 'Already have an account?', 'afcglide' ); ?>
                        <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>">
                            <?php _e( 'Login here', 'afcglide' ); ?>
                        </a>
                    </p>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Display logout link
     */
    public function display_logout_link( $atts = [] ) {
        if ( ! is_user_logged_in() ) {
            return '';
        }

        $atts = shortcode_atts( [
            'text' => __( 'Logout', 'afcglide' ),
            'class' => 'afcglide-button',
            'redirect' => home_url(),
        ], $atts );

        return sprintf(
            '<a href="%s" class="%s">%s</a>',
            esc_url( self::get_logout_url( $atts['redirect'] ) ),
            esc_attr( $atts['class'] ),
            esc_html( $atts['text'] )
        );
    }

    /**
     * Display user status
     */
    public function display_user_status( $atts = [] ) {
        if ( ! is_user_logged_in() ) {
            return '<p>' . __( 'You are not logged in.', 'afcglide' ) . '</p>';
        }

        $current_user = wp_get_current_user();
        return sprintf(
            '<div class="afcglide-user-status">
                <p>%s</p>
                <p>%s</p>
            </div>',
            sprintf(
                __( 'Logged in as: <strong>%s</strong> (%s)', 'afcglide' ),
                esc_html( $current_user->display_name ),
                esc_html( $current_user->user_email )
            ),
            sprintf(
                __( '<a href="%s">Logout</a> | <a href="%s">Submit Listing</a>', 'afcglide' ),
                esc_url( self::get_logout_url() ),
                esc_url( home_url( '/submit-listing/' ) )
            )
        );
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
     * Handle registration form submission
     */
    public function handle_registration() {
        // Check if registration form was submitted
        if ( ! isset( $_POST['afcglide_agent_register_nonce'] ) ) {
            return;
        }

        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['afcglide_agent_register_nonce'], 'afcglide_agent_register_action' ) ) {
            Message_Helper::error( __( 'Security check failed. Please try again.', 'afcglide' ) );
            return;
        }

        // Get and sanitize data
        $username = Sanitizer::username( $_POST['username'] ?? '' );
        $email    = Sanitizer::email( $_POST['email'] ?? '' );
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        // Validate required fields
        $errors = [];

        if ( ! Validator::required( $username ) ) {
            $errors[] = __( 'Username is required.', 'afcglide' );
        }

        if ( ! Validator::required( $email ) ) {
            $errors[] = __( 'Email is required.', 'afcglide' );
        } elseif ( ! Validator::email( $email ) ) {
            $errors[] = __( 'Invalid email format.', 'afcglide' );
        }

        if ( ! Validator::required( $password ) ) {
            $errors[] = __( 'Password is required.', 'afcglide' );
        }

        if ( $password !== $password2 ) {
            $errors[] = __( 'Passwords do not match.', 'afcglide' );
        }

        // Check if username exists
        if ( username_exists( $username ) ) {
            $errors[] = __( 'Username already exists.', 'afcglide' );
        }

        // Check if email exists
        if ( email_exists( $email ) ) {
            $errors[] = __( 'Email already registered.', 'afcglide' );
        }

        // If errors, display them
        if ( ! empty( $errors ) ) {
            foreach ( $errors as $error ) {
                Message_Helper::error( $error );
            }
            return;
        }

        // Create user
        $user_id = wp_create_user( $username, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            Message_Helper::error( $user_id->get_error_message() );
            return;
        }

        // Set user role (default is 'subscriber', change to 'author' for listing submission)
        $user = new \WP_User( $user_id );
        $user->set_role( 'author' );

        // Auto-login after registration
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );

        // Success message
        Message_Helper::success( __( 'Registration successful! You are now logged in.', 'afcglide' ) );

        // Redirect to listing submission page
        $redirect_url = home_url( '/submit-listing/' );
        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * Authenticate user with WordPress
     */
    private function authenticate_user( $email, $password ) {
        $user = get_user_by( 'email', $email );

        if ( ! $user ) {
            return new \WP_Error(
                'invalid_email',
                __( 'No account found with that email address.', 'afcglide' )
            );
        }

        $creds = [
            'user_login'    => $user->user_login,
            'user_password' => $password,
            'remember'      => isset( $_POST['remember_me'] ),
        ];

        $auth = wp_signon( $creds, is_ssl() );

        if ( is_wp_error( $auth ) ) {
            return new \WP_Error(
                'authentication_failed',
                __( 'Invalid email or password.', 'afcglide' )
            );
        }

        return $auth;
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
    public static function get_logout_url( $redirect_to = '' ) {
        $logout_url = add_query_arg( 'afcglide_logout', '1', home_url() );

        if ( $redirect_to ) {
            $logout_url = add_query_arg( 'redirect_to', urlencode( $redirect_to ), $logout_url );
        }

        return $logout_url;
    }

    /**
     * Initialize (called by main plugin)
     */
    public static function init() {
        new self();
    }
}