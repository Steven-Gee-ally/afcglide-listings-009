<?php
/**
 * Plugin Name: AFCGlide Listings v4 - Production Ready
 * Description: High-end Real Estate Asset Management System
 * Version: 4.0.0
 * Author: AFCGlide
 * Text Domain: afcglide
 */

if ( ! defined( 'ABSPATH' ) ) exit;

echo '<!-- AFCGLIDE-CORE-PULSE-ACTIVE -->';

/**
 * 1. DIRECTORY CONSTANTS
 */
define( 'AFCG_VERSION', '4.0.0' );
define( 'AFCG_PATH', plugin_dir_path( __FILE__ ) );
define( 'AFCG_URL', plugin_dir_url( __FILE__ ) );

/**
 * 2. AUTOLOADER - Load Constants First
 */
require_once AFCG_PATH . 'includes/class-afcglide-constants.php';

/**
 * 3. LOAD CORE FILES (Not Initialize Yet)
 */
$core_classes = [
    'includes/class-cpt-tax.php',
    'includes/class-afcglide-dashboard.php',
    'includes/class-afcglide-settings.php',
    'includes/class-afcglide-metaboxes.php',
    'includes/class-afcglide-ajax-handler.php',
    'includes/class-afcglide-shortcodes.php',
    'includes/class-afcglide-scoreboard.php',
    'includes/class-afcglide-table.php',
    'includes/class-afcglide-user-profile.php',
    'includes/class-afcglide-public.php',
    'includes/class-afcglide-admin-ui.php',
    'includes/class-afcglide-block-manager.php',
    'includes/class-afcglide-identity-shield.php',
    'includes/class-afcglide-inventory.php',
    'includes/class-afcglide-welcome.php',
    'includes/class-afcglide-seo.php',
    'includes/class-afcglide-leads.php',
];

foreach ( $core_classes as $file ) {
    $path = AFCG_PATH . $file;
    if ( file_exists( $path ) ) {
        require_once $path;
    } else {
        error_log( "AFCGlide Error: Missing file - {$file}" );
    }
}

/**
 * 4. INITIALIZE ON 'init' HOOK (Correct Timing for CPT)
 */
add_action( 'init', 'afcglide_register_cpt', 0 );

function afcglide_register_cpt() {
    if ( class_exists( '\AFCGlide\Listings\AFCGlide_CPT_Tax' ) ) {
        \AFCGlide\Listings\AFCGlide_CPT_Tax::init();
    }
}

/**
 * 5. INITIALIZE ADMIN COMPONENTS
 */
add_action( 'init', 'afcglide_init_admin', 10 );

function afcglide_init_admin() {
    
    if ( class_exists( '\AFCGlide\Admin\AFCGlide_Admin_UI' ) ) {
        \AFCGlide\Admin\AFCGlide_Admin_UI::init();
    }

    if ( class_exists( '\AFCGlide\Admin\AFCGlide_Identity_Shield' ) ) {
        \AFCGlide\Admin\AFCGlide_Identity_Shield::init();
    }
    
    if ( class_exists( '\AFCGlide\Admin\AFCGlide_Dashboard' ) ) {
        \AFCGlide\Admin\AFCGlide_Dashboard::init();
    }
    
    if ( class_exists( '\AFCGlide\Listings\AFCGlide_Metaboxes' ) ) {
        \AFCGlide\Listings\AFCGlide_Metaboxes::init();
    }
    
    if ( class_exists( '\AFCGlide\Listings\AFCGlide_Ajax_Handler' ) ) {
        \AFCGlide\Listings\AFCGlide_Ajax_Handler::init();
    }
    
    if ( class_exists( '\AFCGlide\Admin\AFCGlide_Shortcodes' ) ) {
        \AFCGlide\Admin\AFCGlide_Shortcodes::init();
    }
    
    if ( class_exists( '\AFCGlide\Admin\AFCGlide_Table' ) ) {
        \AFCGlide\Admin\AFCGlide_Table::init();
    }
    
    if ( class_exists( '\AFCGlide\Admin\AFCGlide_User_Profile' ) ) {
        \AFCGlide\Admin\AFCGlide_User_Profile::init();
    }
    
    if ( class_exists( '\AFCGlide\Listings\AFCGlide_Public' ) ) {
        \AFCGlide\Listings\AFCGlide_Public::init();
    }
    
    if ( class_exists( '\AFCGlide\Listings\AFCGlide_Block_Manager' ) ) {
        \AFCGlide\Listings\AFCGlide_Block_Manager::init();
    }
    
    
    if ( class_exists( '\AFCGlide\Admin\AFCGlide_Inventory' ) ) {
        \AFCGlide\Admin\AFCGlide_Inventory::init();
    }
    
    if ( class_exists( '\AFCGlide\Admin\AFCGlide_Welcome' ) ) {
        \AFCGlide\Admin\AFCGlide_Welcome::init();
    }

    if ( class_exists( '\AFCGlide\Core\AFCGlide_SEO' ) ) {
        \AFCGlide\Core\AFCGlide_SEO::init();
    }

    if ( class_exists( '\AFCGlide\Core\AFCGlide_Leads' ) ) {
        \AFCGlide\Core\AFCGlide_Leads::init();
    }
}

/**
 * 6. ASSET LOADING
 */
add_action( 'wp_enqueue_scripts', 'afcglide_frontend_assets' );
add_action( 'admin_enqueue_scripts', 'afcglide_admin_assets' );

function afcglide_frontend_assets() {
    if ( is_singular( \AFCGlide\Core\Constants::POST_TYPE ) ) {
        wp_enqueue_style( 
            'afc-single-listing', 
            AFCG_URL . 'assets/css/afcglide-single-listing.css', 
            [], 
            AFCG_VERSION 
        );

        // Dynamic WhatsApp Color
        $wa_color = get_option('afc_whatsapp_color', '#25D366');
        $custom_css = "
            .afc-whatsapp-float { background-color: {$wa_color} !important; }
            @keyframes afc-pulse {
                0% { box-shadow: 0 0 0 0 {$wa_color}b3; } 
                70% { box-shadow: 0 0 0 15px {$wa_color}00; } 
                100% { box-shadow: 0 0 0 0 {$wa_color}00; }
            }
        ";
        wp_add_inline_style( 'afc-single-listing', $custom_css );
    }

    // Leaflet Assets for Mapping
    wp_enqueue_style( 'afc-leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4' );
    wp_enqueue_script( 'afc-leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true );

    wp_enqueue_script( 'afc-public-js', AFCG_URL . 'assets/js/afcglide-public.js', ['jquery', 'afc-leaflet-js'], AFCG_VERSION, true );
    wp_localize_script( 'afc-public-js', 'afc_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce( \AFCGlide\Core\Constants::NONCE_AJAX ),
        'lang'     => afcglide_get_current_lang(),
    ]);

    // Submission Form Assets (Check for shortcode or specific page logic)
    global $post;
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'afcglide_submit_listing' ) ) {
        wp_enqueue_style( 
            'afc-submission-css', 
            AFCG_URL . 'assets/css/afcglide-frontend-submission.css', 
            [], 
            AFCG_VERSION 
        );

        wp_enqueue_script( 
            'afc-submission-js', 
            AFCG_URL . 'assets/js/afcglide-submission.js', 
            ['jquery'], 
            AFCG_VERSION, 
            true 
        );

        wp_localize_script( 'afc-submission-js', 'afc_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce( \AFCGlide\Core\Constants::NONCE_AJAX ),
            'lang'     => afcglide_get_current_lang(),
            'strings'  => [
                'loading'    => __('🚀 SYNCING ASSET...', 'afcglide'),
                'success'    => __('✨ ASSET DEPLOYED', 'afcglide'),
                'error'      => __('❌ ERROR:', 'afcglide'),
                'invalid'    => __('🚫 INVALID FILE: Please upload a JPG or PNG.', 'afcglide'),
                'too_small'  => __('⚠️ QUALITY REJECTED: Luxury listings require 1200px width minimum.', 'afcglide'),
                'retry'      => __('RETRY SUBMISSION', 'afcglide'),
                'verifying'  => __('Listing Verified. Redirecting...', 'afcglide'),
                'handshake'  => __('Initializing secure handshake with server...', 'afcglide'),
            ]
        ]);
    }
}

function afcglide_admin_assets( $hook ) {
    global $post_type;
    
    $is_afc_page = ( 
        \AFCGlide\Core\Constants::POST_TYPE === $post_type || 
        ( isset($_GET['page']) && strpos($_GET['page'], 'afcglide') !== false ) ||
        'profile.php' === $hook ||
        'user-edit.php' === $hook ||
        'users.php' === $hook ||
        'user-new.php' === $hook
    );
    
    if ( ! $is_afc_page ) return;
    
    wp_enqueue_media();
    wp_enqueue_script( 'jquery-ui-sortable' );
    
    wp_enqueue_style( 
        'afc-admin-styles', 
        AFCG_URL . 'assets/css/afcglide-admin.css', 
        [], 
        AFCG_VERSION 
    );
    
    wp_enqueue_script( 
        'afc-admin-js', 
        AFCG_URL . 'assets/js/afcglide-admin.js', 
        ['jquery', 'jquery-ui-sortable'], 
        AFCG_VERSION, 
        true 
    );
    
    wp_localize_script( 'afc-admin-js', 'afc_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce( \AFCGlide\Core\Constants::NONCE_AJAX ),
    ]);

    // Dashboard Specific CSS
    if ( isset($_GET['page']) && $_GET['page'] === 'afcglide-dashboard' ) {
        wp_enqueue_style( 
            'afc-dashboard-css', 
            AFCG_URL . 'assets/css/afcglide-dashboard.css', 
            [], 
            AFCG_VERSION 
        );
    }
}

/**
 * 7. SINGLE LISTING TEMPLATE OVERRIDE
 */
add_filter( 'single_template', 'afcglide_single_template' );

function afcglide_single_template( $template ) {
    if ( is_singular( \AFCGlide\Core\Constants::POST_TYPE ) ) {
        $plugin_template = AFCG_PATH . 'templates/single-afcglide_listing.php';
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    }
    return $template;
}

/**
 * 8. GLOBAL FLOATING WHATSAPP
 */
add_action( 'wp_footer', 'afcglide_global_whatsapp' );

function afcglide_global_whatsapp() {
    
    // Don't show on listing pages (they have their own button)
    if ( is_singular( \AFCGlide\Core\Constants::POST_TYPE ) ) return;
    
    // Check if global WhatsApp is enabled
    if ( \AFCGlide\Core\Constants::get_option( \AFCGlide\Core\Constants::OPT_WA_GLOBAL ) !== '1' ) return;
    
    $global_phone = \AFCGlide\Core\Constants::get_option( \AFCGlide\Core\Constants::OPT_AGENT_PHONE );
    $wa_color     = \AFCGlide\Core\Constants::get_option( \AFCGlide\Core\Constants::OPT_WA_COLOR, '#25D366' );
    
    if ( empty($global_phone) ) return;
    
    $clean_phone = preg_replace('/[^0-9]/', '', $global_phone);
    ?>
    <style>
        .afc-global-wa {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background-color: <?php echo esc_attr($wa_color); ?>;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            z-index: 9999;
            transition: all 0.3s ease;
            animation: afc-pulse-global 2s infinite;
        }
        .afc-global-wa:hover { transform: scale(1.1); color: white; }
        .afc-global-wa svg { width: 32px; height: 32px; fill: currentColor; }
        @keyframes afc-pulse-global {
            0% { box-shadow: 0 0 0 0 <?php echo esc_attr($wa_color); ?>b3; }
            70% { box-shadow: 0 0 0 15px <?php echo esc_attr($wa_color); ?>00; }
            100% { box-shadow: 0 0 0 0 <?php echo esc_attr($wa_color); ?>00; }
        }
    </style>
    <a href="https://wa.me/<?php echo $clean_phone; ?>" class="afc-global-wa" target="_blank" rel="nofollow">
        <svg viewBox="0 0 32 32"><path d="M16 0c-8.837 0-16 7.163-16 16 0 2.825.737 5.588 2.137 8.137l-2.137 7.863 8.1-.2.1.2c2.487 1.463 5.112 2.112 7.9 2.112 8.837 0 16-7.163 16-16s-7.163-16-16-16zm8.287 21.825c-.337.95-1.712 1.838-2.737 2.05-.688.138-1.588.25-4.6-1.013-3.862-1.612-6.362-5.538-6.55-5.8-.188-.262-1.525-2.025-1.525-3.862 0-1.838.963-2.738 1.3-3.113.337-.375.75-.463 1-.463s.5 0 .712.013c.225.013.525-.088.825.638.3.713 1.013 2.475 1.1 2.663.088.188.15.413.025.663-.125.263-.188.425-.375.65-.188.225-.412.513-.587.688-.2.2-.412.412-.175.812.238.4.1.863 2.087 2.625 1.637 1.45 3.012 1.9 3.437 2.113.425.213.675.175.925-.113.25-.288 1.075-1.25 1.362-1.688.3-.425.588-.363.988-.212.4.15 2.525 1.188 2.962 1.4.438.213.738.313.838.488.1.175.1.988-.237 1.938z"/></svg>
    </a>
    <?php
}

/**
 */
function afcglide_get_current_lang() {
    if ( isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'es']) ) {
        return $_GET['lang'];
    }
    return 'en';
}

function afcglide_get_localized_url( $lang ) {
    return add_query_arg( 'lang', $lang, get_permalink() );
}

/**
 * 11. INVINCIBLE FOOTER UI (GLOBAL)
 */
add_action( 'wp_head', 'afcglide_render_global_footer_ui' );
function afcglide_render_global_footer_ui() {
    echo '<!-- AFCGLIDE-HEAD-PULSE-ACTIVE -->';
    $current_lang = afcglide_get_current_lang();
    $agent_hub_slug = \AFCGlide\Core\Constants::get_option( \AFCGlide\Core\Constants::OPT_PAGE_AGENT_HUB, 'agent-hub' );
    $target_url = is_user_logged_in() ? home_url('/' . $agent_hub_slug . '/') : wp_login_url();
    $can_access = ! is_user_logged_in() || current_user_can('read');
    ?>
    <style>
        .afc-footer-toolbar {
            position: fixed !important;
            bottom: 30px !important;
            left: 30px !important;
            z-index: 99999999 !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            font-family: 'Inter', sans-serif !important;
        }

        .afc-lang-switcher-pill {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(10px) !important;
            padding: 8px 16px !important;
            border-radius: 50px !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
            display: flex !important;
            gap: 12px !important;
            font-weight: 800 !important;
            font-size: 11px !important;
            letter-spacing: 1px !important;
            border: 1px solid rgba(0,0,0,0.08) !important;
            height: 40px !important;
            align-items: center !important;
        }
        .afc-lang-switcher-pill a { text-decoration: none !important; color: #94a3b8 !important; transition: 0.2s !important; }
        .afc-lang-switcher-pill a.active { color: #1e293b !important; }
        .afc-lang-switcher-pill a:hover:not(.active) { color: #64748b !important; }

        .afc-agent-access-btn {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(10px) !important;
            color: #1e293b !important;
            padding: 0 20px !important;
            border-radius: 50px !important;
            font-size: 10px !important;
            font-weight: 800 !important;
            text-decoration: none !important;
            text-transform: uppercase !important;
            letter-spacing: 1.2px !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08) !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            border: 1px solid #ff5a2d !important; /* Subtle Orange Accent */
            height: 40px !important;
            transition: all 0.3s ease !important;
        }
        .afc-agent-access-btn:hover { 
            background: #ff5a2d !important; 
            color: #fff !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 20px rgba(255, 90, 45, 0.2) !important;
        }
        .afc-agent-access-btn span { font-size: 13px !important; filter: grayscale(1) brightness(0.5); transition: 0.3s; }
        .afc-agent-access-btn:hover span { filter: none; }
    </style>

    <div class="afc-footer-toolbar">
        <div class="afc-lang-switcher-pill">
            <a href="<?php echo esc_url( add_query_arg('lang', 'en') ); ?>" class="<?php echo $current_lang === 'en' ? 'active' : ''; ?>">EN</a>
            <span style="color: #e2e8f0;">|</span>
            <a href="<?php echo esc_url( add_query_arg('lang', 'es') ); ?>" class="<?php echo $current_lang === 'es' ? 'active' : ''; ?>">ES</a>
        </div>

        <?php if ( $can_access ) : ?>
            <a href="<?php echo esc_url($target_url); ?>" class="afc-agent-access-btn">
                <span>🚀</span> Agent Access
            </a>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * 9. ACTIVATION HOOK
 */
register_activation_hook( __FILE__, 'afcglide_activate' );

function afcglide_activate() {
    // Load CPT class if not loaded
    if ( ! class_exists( '\AFCGlide\Listings\AFCGlide_CPT_Tax' ) ) {
        require_once AFCG_PATH . 'includes/class-cpt-tax.php';
    }
    
    // Register CPT and Taxonomies
    \AFCGlide\Listings\AFCGlide_CPT_Tax::register_post_type();
    \AFCGlide\Listings\AFCGlide_CPT_Tax::register_taxonomies();
    
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Set default options if not exist
    $defaults = [
        \AFCGlide\Core\Constants::OPT_PRIMARY_COLOR  => '#10b981',
        \AFCGlide\Core\Constants::OPT_WA_COLOR       => '#25D366',
        \AFCGlide\Core\Constants::OPT_QUALITY_GATE   => '1',
        \AFCGlide\Core\Constants::OPT_ADMIN_LOCKDOWN => '0',
        \AFCGlide\Core\Constants::OPT_WA_GLOBAL      => '0',
    ];
    
    // Initialize Roles
    afcglide_init_roles();

    foreach ( $defaults as $key => $value ) {
        if ( false === get_option( $key ) ) {
            add_option( $key, $value );
        }
    }
}

/**
 * 10. ROLE INITIALIZATION
 */
function afcglide_init_roles() {
    // 1. Managing Broker (Full Access)
    add_role( 'managing_broker', 'Managing Broker', [
        'read'                        => true,
        'manage_options'              => true,
        'upload_files'                => true,
        'edit_afc_listing'            => true,
        'read_afc_listing'            => true,
        'delete_afc_listing'          => true,
        'edit_afc_listings'           => true,
        'edit_others_afc_listings'    => true,
        'publish_afc_listings'        => true,
        'read_private_afc_listings'   => true,
        'delete_afc_listings'         => true,
        'delete_private_afc_listings' => true,
        'delete_published_afc_listings'=> true,
        'delete_others_afc_listings'  => true,
        'edit_private_afc_listings'   => true,
        'create_afc_listings'         => true,
    ]);

    // 2. Listing Agent
    add_role( 'listing_agent', 'Listing Agent', [
        'read'                        => true,
        'upload_files'                => true,
        'edit_afc_listing'            => true,
        'read_afc_listing'            => true,
        'delete_afc_listing'          => true,
        'edit_afc_listings'           => true,
        'publish_afc_listings'        => true,
        'delete_afc_listings'         => true,
        'delete_published_afc_listings'=> true,
        'edit_published_afc_listings' => true,
        'create_afc_listings'         => true,
    ]);

    // Ensure administrator always has full control
    $admin = get_role('administrator');
    if ($admin) {
        $admin->add_cap('edit_afc_listing');
        $admin->add_cap('read_afc_listing');
        $admin->add_cap('delete_afc_listing');
        $admin->add_cap('edit_afc_listings');
        $admin->add_cap('edit_others_afc_listings');
        $admin->add_cap('publish_afc_listings');
        $admin->add_cap('read_private_afc_listings');
        $admin->add_cap('delete_afc_listings');
        $admin->add_cap('delete_private_afc_listings');
        $admin->add_cap('delete_published_afc_listings');
        $admin->add_cap('delete_others_afc_listings');
        $admin->add_cap('edit_private_afc_listings');
        $admin->add_cap('edit_published_afc_listings');
        $admin->add_cap('create_afc_listings');
    }
}

/**
 * 11. ASSET OPTIMIZATION & MACHINE LOGIC
 */

// Enable WebP support for older WP versions (if applicable)
add_filter( 'upload_mimes', function( $mimes ) {
    $mimes['webp'] = 'image/webp';
    return $mimes;
});

// High-Res Auto-Resizer: Prevents site bloat from 50MB photos
add_filter( 'wp_handle_upload_prefilter', function( $file ) {
    if ( ! get_option('afc_quality_gatekeeper', 1) ) return $file;

    $img = getimagesize( $file['tmp_name'] );
    $minimum_width = 1200;
    
    // Hard rejection if too small (The 1200px Gate)
    if ( $img && $img[0] < $minimum_width ) {
        $file['error'] = "⚠️ ASSET REJECTED: Luxury listings require 1200px minimum width. Detected: {$img[0]}px";
    }

    return $file;
});

// SUCCESS NOTIFICATION LOGIC: When a listing hits "SOLD"
add_action( 'save_post_afcglide_listing', function( $post_id, $post, $update ) {
    if ( ! $update || $post->post_status !== 'sold' ) return;
    
    // Check if it was already sold (prevent duplicate logs)
    if ( get_post_meta( $post_id, '_afc_sold_logged', true ) ) return;

    // Log the success for the Broker Activity Stream
    update_post_meta( $post_id, '_afc_sold_logged', time() );
    
    // Pro-tip: Here is where we would trigger an email or SMS notification
}, 10, 3 );

/**
 * Enterprise Cache Refresh on Deletion
 */
add_action( 'deleted_post', function( $post_id ) {
    if ( get_post_type($post_id) === \AFCGlide\Core\Constants::POST_TYPE ) {
        if ( class_exists('\AFCGlide\Listings\AFCGlide_Ajax_Handler') ) {
            \AFCGlide\Listings\AFCGlide_Ajax_Handler::clear_filter_cache();
        }
    }
});

/**
 * 12. DEACTIVATION HOOK
 */
register_deactivation_hook( __FILE__, 'afcglide_deactivate' );

function afcglide_deactivate() {
    flush_rewrite_rules();
}