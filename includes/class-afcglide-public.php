<?php
namespace AFCGlide\Listings;

/**
 * AFCGlide Public Logic
 * Handles WhatsApp, Language Switcher, and Asset Loading
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AFCGlide_Public {

    public static function init() {
        add_action( 'wp_footer', [ __CLASS__, 'render_whatsapp_button' ] );
        add_action( 'wp_footer', [ __CLASS__, 'render_footer_ui' ] );
        add_action( 'template_redirect', [ __CLASS__, 'track_listing_views' ] );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_public_styles' ] );
    }

    public static function enqueue_public_styles() {
        // 1. Load Media Uploader for Listing Submission Pages
        if ( is_page(['add-new-listing', 'submit-listing', 'agent-hub']) ) {
            wp_enqueue_media();
        }

        // 2. High-End WhatsApp Styling
        $wa_color = get_option('afc_whatsapp_color', '#25D366');
        $custom_css = "
            .afcglide-whatsapp-float {
                position: fixed;
                bottom: 30px;
                right: 30px;
                background-color: $wa_color;
                color: #fff;
                border-radius: 50px;
                text-align: center;
                font-size: 14px;
                font-weight: 800;
                box-shadow: 0 10px 25px rgba(0,0,0,0.15);
                z-index: 9999;
                display: flex;
                align-items: center;
                padding: 12px 22px;
                text-decoration: none;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .afcglide-whatsapp-float:hover {
                transform: translateY(-5px);
                box-shadow: 0 15px 30px rgba(0,0,0,0.2);
                color: #fff;
            }
            .afcglide-whatsapp-icon { margin-right: 10px; font-size: 18px; }
        ";
        wp_add_inline_style( 'afc-single-listing', $custom_css );
    }

    public static function render_footer_ui() {
        $current_lang = 'en';
        if ( function_exists('afcglide_get_current_lang') ) {
            $current_lang = afcglide_get_current_lang();
        }
        
        $target_url = is_user_logged_in() ? home_url('/agent-hub/') : wp_login_url();
        ?>
        <style>
            .afc-lang-switcher {
                position: fixed; bottom: 30px; left: 30px;
                background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);
                padding: 10px 18px; border-radius: 50px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1); z-index: 9999;
                display: flex; gap: 15px; font-weight: 800; font-size: 11px;
                border: 1px solid rgba(0,0,0,0.05);
            }
            .afc-lang-switcher a { text-decoration: none; color: #94a3b8; }
            .afc-lang-switcher a.active { color: #0f172a; }
            
            .afc-agent-access-wrap { position: fixed; bottom: 30px; left: 130px; z-index: 9999; }
            .afc-agent-access-btn {
                background: #ff5a2d; color: #fff; padding: 10px 20px;
                border-radius: 50px; font-size: 11px; font-weight: 900;
                text-decoration: none; text-transform: uppercase;
                box-shadow: 0 10px 25px rgba(255, 90, 45, 0.2);
                border: 2px solid #fff;
            }
        </style>

        <div class="afc-lang-switcher">
            <a href="<?php echo esc_url( add_query_arg('lang', 'en') ); ?>" class="<?php echo $current_lang === 'en' ? 'active' : ''; ?>">EN</a>
            <span style="color: #e2e8f0;">|</span>
            <a href="<?php echo esc_url( add_query_arg('lang', 'es') ); ?>" class="<?php echo $current_lang === 'es' ? 'active' : ''; ?>">ES</a>
        </div>

        <div class="afc-agent-access-wrap">
            <a href="<?php echo esc_url($target_url); ?>" class="afc-agent-access-btn">🚀 Agent Access</a>
        </div>
        <?php
    }

    public static function render_whatsapp_button() {
        if ( ! is_singular( 'afcglide_listing' ) ) return;

        $agent_id = get_post_meta( get_the_ID(), '_property_agent', true ) ?: get_the_author_meta('ID');
        $phone = get_user_meta( $agent_id, 'agent_phone', true );
        
        if ( empty( $phone ) ) return;

        $clean_phone = preg_replace('/[^0-9]/', '', $phone);
        $message = rawurlencode( "Pura Vida! I'm interested in " . get_the_title() . ". Is it available?" );
        $wa_url = "https://wa.me/" . $clean_phone . "?text=" . $message;

        echo '<a href="'.esc_url($wa_url).'" class="afcglide-whatsapp-float" target="_blank" rel="nofollow">';
        echo '<span class="afcglide-whatsapp-icon">💬</span> WhatsApp Agent</a>';
    }

    public static function track_listing_views() {
        if ( ! is_singular( 'afcglide_listing' ) || current_user_can('manage_options') ) return;

        $post_id = get_the_ID();
        $cookie_name = 'afc_viewed_' . $post_id;
        
        if ( ! isset( $_COOKIE[$cookie_name] ) ) {
            setcookie( $cookie_name, '1', time() + 86400, COOKIEPATH, COOKIE_DOMAIN );
            $views = (int) get_post_meta( $post_id, '_listing_views_count', true );
            update_post_meta( $post_id, '_listing_views_count', $views + 1 );
        }
    }
}