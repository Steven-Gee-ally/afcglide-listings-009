<?php
/**
 * Plugin Name: AFCGlide Listings
 * Description: Modular real estate listings plugin with frontend submission and authentication.
 * Version: 2.3.0
 * Author: AFCGlide
 * Text Domain: afcglide
 * Domain Path: /languages
 */

namespace AFCGlide\Listings;

// --------------------------------------------------
// Safety First
// --------------------------------------------------
if ( ! defined( 'ABSPATH' ) ) exit;

// --------------------------------------------------
// Core Constants (KEEP AT TOP)
// --------------------------------------------------
define( 'AFCG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AFCG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AFCG_VERSION', '2.3.0' );

// --------------------------------------------------
// Safe Require Helper (Prevents Double Loads)
// --------------------------------------------------
if ( ! function_exists( __NAMESPACE__ . '\afcg_require_once_safe' ) ) {
    function afcg_require_once_safe( $relative_path ) {
        $full_path = AFCG_PLUGIN_DIR . ltrim( $relative_path, '/' );
        if ( file_exists( $full_path ) ) {
            require_once $full_path;
        }
    }
}

// --------------------------------------------------
// Helpers (NO LOGIC — DEFINITIONS ONLY)
// --------------------------------------------------
afcg_require_once_safe( 'includes/helpers/helpers.php' );
afcg_require_once_safe( 'includes/helpers/class-validator.php' );
afcg_require_once_safe( 'includes/helpers/class-sanitizer.php' );
afcg_require_once_safe( 'includes/helpers/class-message-helper.php' );
afcg_require_once_safe( 'includes/helpers/class-upload-helper.php' );

// --------------------------------------------------
// Submission System (Auth, Listings, Files)
// --------------------------------------------------
afcg_require_once_safe( 'includes/submission/class-submission-auth.php' );
afcg_require_once_safe( 'includes/submission/class-submission-listing.php' );
afcg_require_once_safe( 'includes/submission/class-submission-files.php' );

// --------------------------------------------------
// Core Plugin Classes
// --------------------------------------------------
afcg_require_once_safe( 'includes/class-cpt-tax.php' );
afcg_require_once_safe( 'includes/class-afcglide-public.php' );
afcg_require_once_safe( 'includes/class-afcglide-admin-assets.php' );
afcg_require_once_safe( 'includes/class-afcglide-templates.php' );
afcg_require_once_safe( 'includes/class-afcglide-shortcodes.php' );
afcg_require_once_safe( 'includes/class-afcglide-block-manager.php' );

// --------------------------------------------------
// Plugin Bootstrap (INITIALIZATION ONLY)
// --------------------------------------------------
function bootstrap_afcglide_listings() {

    // CPTs & Taxonomies
    if ( class_exists( __NAMESPACE__ . '\AFCGlide_CPT_Tax' ) ) {
        AFCGlide_CPT_Tax::init();
    }

    // Public Assets
    if ( class_exists( __NAMESPACE__ . '\AFCGlide_Public' ) ) {
        AFCGlide_Public::init();
    }

    // Admin Assets
    if ( class_exists( __NAMESPACE__ . '\AFCGlide_Admin_Assets' ) ) {
        AFCGlide_Admin_Assets::init();
    }

    // Templates (KEEP THIS — REMOVE ANY DUPLICATES)
    if ( class_exists( __NAMESPACE__ . '\AFCGlide_Templates' ) ) {
        AFCGlide_Templates::init();
    }

    // Shortcodes
    if ( class_exists( __NAMESPACE__ . '\AFCGlide_Shortcodes' ) ) {
        AFCGlide_Shortcodes::init();
    }

    // Gutenberg Blocks
    if ( class_exists( __NAMESPACE__ . '\AFCGlide_Block_Manager' ) ) {
        AFCGlide_Block_Manager::init();
    }

    // Submission Auth (LOGIN PRIORITY)
    if ( class_exists( __NAMESPACE__ . '\Submission\Submission_Auth' ) ) {
        Submission\Submission_Auth::init();
    }

    // Submission Listing Handler
    if ( class_exists( __NAMESPACE__ . '\Submission\Submission_Listing' ) ) {
        Submission\Submission_Listing::init();
    }

    // File Upload Handler
    if ( class_exists( __NAMESPACE__ . '\Submission\Submission_Files' ) ) {
        Submission\Submission_Files::init();
    }
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\bootstrap_afcglide_listings' );

// --------------------------------------------------
// Plugin Activation Hook
// --------------------------------------------------
/**
 * Create required pages and flush rewrite rules on activation
 */
function afcglide_activate() {
    
    // Create Submit Listing page
    $submit_page = get_page_by_path( 'submit-listing' );
    if ( ! $submit_page ) {
        wp_insert_post([
            'post_title'   => __( 'Submit Listing', 'afcglide' ),
            'post_name'    => 'submit-listing',
            'post_content' => '[afcglide_submit_listing]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
    }

    // Create Login page
    $login_page = get_page_by_path( 'listing-login' );
    if ( ! $login_page ) {
        wp_insert_post([
            'post_title'   => __( 'Login', 'afcglide' ),
            'post_name'    => 'listing-login',
            'post_content' => '[afcglide_login]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
    }

    // Create Register page
    $register_page = get_page_by_path( 'listing-register' );
    if ( ! $register_page ) {
        wp_insert_post([
            'post_title'   => __( 'Register', 'afcglide' ),
            'post_name'    => 'listing-register',
            'post_content' => '[afcglide_register]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
    }

    // Create My Listings page (dashboard for users)
    $dashboard_page = get_page_by_path( 'my-listings' );
    if ( ! $dashboard_page ) {
        wp_insert_post([
            'post_title'   => __( 'My Listings', 'afcglide' ),
            'post_name'    => 'my-listings',
            'post_content' => '[afcglide_my_listings]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
    }

    // Register CPT first (needed for flush_rewrite_rules to work)
    if ( class_exists( __NAMESPACE__ . '\AFCGlide_CPT_Tax' ) ) {
        AFCGlide_CPT_Tax::register_post_type();
        AFCGlide_CPT_Tax::register_taxonomies();
    }

    // Flush rewrite rules to register custom post type permalinks
    flush_rewrite_rules();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\afcglide_activate' );

// --------------------------------------------------
// Plugin Deactivation Hook
// --------------------------------------------------
/**
 * Clean up on plugin deactivation
 */
function afcglide_deactivate() {
    // Flush rewrite rules to clean up custom post type permalinks
    flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, __NAMESPACE__ . '\afcglide_deactivate' );

// --------------------------------------------------
// Plugin Uninstall Hook (Optional)
// --------------------------------------------------
/**
 * Clean up on plugin uninstall
 * This should be in a separate uninstall.php file, but included here for reference
 */
function afcglide_uninstall() {
    // Delete plugin pages (optional - you may want to keep them)
    // $pages = ['submit-listing', 'listing-login', 'listing-register', 'my-listings'];
    // foreach ( $pages as $page_slug ) {
    //     $page = get_page_by_path( $page_slug );
    //     if ( $page ) {
    //         wp_delete_post( $page->ID, true );
    //     }
    // }

    // Flush rewrite rules
    flush_rewrite_rules();
}

// Uncomment if you want to use this:
// register_uninstall_hook( __FILE__, __NAMESPACE__ . '\afcglide_uninstall' );

/* --------------------------------------------------
✅ NOTES & DOCUMENTATION
-----------------------------------------------------

FILE STRUCTURE:
- All classes use static ::init() methods
- Namespaces properly organized
- No duplicate template loaders

ACTIVATION CREATES:
1. /submit-listing/ page with [afcglide_submit_listing]
2. /listing-login/ page with [afcglide_login]
3. /listing-register/ page with [afcglide_register]
4. /my-listings/ page with [afcglide_my_listings]

REQUIRED SHORTCODES (Must be registered in AFCGlide_Shortcodes):
- [afcglide_submit_listing]
- [afcglide_login] (handled by Submission_Auth)
- [afcglide_register] (handled by Submission_Auth)
- [afcglide_my_listings]

NEXT STEPS:
1. Deactivate and reactivate plugin
2. Go to Settings → Permalinks and save
3. Test pages at /listing-login/ and /listing-register/
4. Ensure submission shortcode exists

-------------------------------------------------- */