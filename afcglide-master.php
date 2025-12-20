<?php
/**
 * Plugin Name: AFCGlide Listings
 * Plugin URI: https://example.com/
 * Description: Modular real estate listings plugin.
 * Version: 3.0.0
 * Author: Stevo
 * Text Domain: afcglide
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load the shortcodes class (keeps class definition in one place)
require_once __DIR__ . '/includes/class-afcglide-shortcodes.php';

// Register shortcodes on init
add_action( 'init', function() {
    if ( class_exists( 'AFCGlide\\Listings\\AFCGlide_Shortcodes' ) ) {
        \AFCGlide\Listings\AFCGlide_Shortcodes::init();
    }
}, 10 );