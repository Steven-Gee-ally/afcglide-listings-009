<?php
/**
 * AFCGlide Admin Assets
 * Enqueues admin scripts and styles
 *
 * Save as: includes/class-afcglide-admin-assets.php
 *
 * @package AFCGlide\Listings
 */

namespace AFCGlide\Listings;

if ( ! defined( 'ABSPATH' ) ) exit;

class AFCGlide_Admin_Assets {

    public function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_assets( $hook ) {
        global $post_type;

        // Only load on afcglide_listing post type screens
        if ( 'afcglide_listing' !== $post_type ) {
            return;
        }

        // Enqueue WordPress media uploader
        wp_enqueue_media();

        // Enqueue WordPress color picker
        wp_enqueue_style( 'wp-color-picker' );

        // Enqueue admin CSS
        wp_enqueue_style(
            'afcglide-admin',
            AFCG_PLUGIN_URL . 'assets/css/admin.css',
            [],
            AFCG_VERSION
        );

        // Enqueue admin JS (your media uploader script)
        wp_enqueue_script(
            'afcglide-admin',
            AFCG_PLUGIN_URL . 'assets/js/afcglide-admin.js',
            [ 'jquery', 'wp-color-picker' ],
            AFCG_VERSION,
            true
        );

        // Localize script (if needed for future AJAX in admin)
        wp_localize_script( 'afcglide-admin', 'afcglide_admin', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'afcglide_admin_nonce' ),
        ]);
    }

    /**
     * Static init method
     */
    public static function init() {
        new self();
    }
}

new AFCGlide_Admin_Assets();