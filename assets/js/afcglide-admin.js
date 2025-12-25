<?php
/**
 * AFCGlide Admin Assets Loader
 * Handles all admin-side CSS and JavaScript
 *
 * @package AFCGlide\Listings
 * @since 3.6.6
 */

namespace AFCGlide\Listings;

if ( ! defined( 'ABSPATH' ) ) exit;

class AFCGlide_Admin_Assets {

    /**
     * Initialize the admin assets loader
     */
    public static function init() {
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );
    }

    /**
     * Enqueue admin scripts and styles
     * 
     * @param string $hook Current admin page hook
     */
    public static function enqueue_admin_assets( $hook ) {
        global $post_type;

        // Only load on AFCGlide listing pages and settings
        $allowed_hooks = [ 'post.php', 'post-new.php', 'edit.php' ];
        $is_listing_page = ( 'afcglide_listing' === $post_type );
        $is_settings_page = ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'afcglide' ) !== false );

        if ( ! $is_listing_page && ! $is_settings_page && ! in_array( $hook, $allowed_hooks ) ) {
            return;
        }

        // Load WordPress media uploader
        if ( $is_listing_page ) {
            wp_enqueue_media();
            wp_enqueue_style( 'wp-color-picker' );
        }

        // Enqueue admin CSS
        if ( file_exists( AFCG_PATH . 'assets/css/admin.css' ) ) {
            wp_enqueue_style(
                'afcglide-admin-style',
                AFCG_URL . 'assets/css/admin.css',
                [],
                AFCG_VERSION
            );
        }

        // Enqueue admin JavaScript (only on listing pages)
        if ( $is_listing_page ) {
            if ( file_exists( AFCG_PATH . 'assets/js/afcglide-admin.js' ) ) {
                wp_enqueue_script(
                    'afcglide-admin-js',
                    AFCG_URL . 'assets/js/afcglide-admin.js',
                    [ 'jquery', 'wp-color-picker' ],
                    AFCG_VERSION,
                    true
                );

                // Localize script with AJAX data
                wp_localize_script( 'afcglide-admin-js', 'afcglide_admin', [
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'afcglide_admin_nonce' ),
                    'strings'  => [
                        'confirm_delete' => __( 'Are you sure you want to delete this?', 'afcglide' ),
                        'error'          => __( 'An error occurred. Please try again.', 'afcglide' ),
                        'success'        => __( 'Success!', 'afcglide' ),
                    ]
                ]);
            }
        }

        // Enqueue settings upload script (only on settings pages)
        if ( $is_settings_page ) {
            if ( file_exists( AFCG_PATH . 'assets/js/settings-upload.js' ) ) {
                wp_enqueue_script(
                    'afcglide-settings-upload',
                    AFCG_URL . 'assets/js/settings-upload.js',
                    [ 'jquery' ],
                    AFCG_VERSION,
                    true
                );
            }
        }
    }
}