<?php
/**
 * Plugin Name: AFCGlide Listings
 * Description: Modular real estate listings plugin with frontend submission and authentication.
 * Version: 2.3.0
 * Author: AFCGlide
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
    if ( class_exists( __NAMESPACE__ . '\AFCGlide_Submission_Auth' ) ) {
        AFCGlide_Submission_Auth::init();
    }
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\bootstrap_afcglide_listings' );

/* --------------------------------------------------
⚠️ COMMENTED OUT — DO NOT USE
-----------------------------------------------------
Reason: Duplicate template loader caused conflicts.
Handled properly in AFCGlide_Templates class.

add_filter( 'template_include', function( $template ) {
    // ❌ REMOVED — handled by class-afcglide-templates.php
    return $template;
});
-------------------------------------------------- */
