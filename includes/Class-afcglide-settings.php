<?php
/**
 * Admin Settings Page
 *
 * @package AFCGlide_Listings
 */

namespace AFCGlide\Listings;

if ( ! defined( 'ABSPATH' ) ) exit;

class AFCGlide_Settings {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_settings_page' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_scripts' ] );
    }

    public static function add_settings_page() {
        add_options_page(
            __( 'AFCGlide Listings Settings', 'afcglide' ),
            __( 'AFCGlide Listings', 'afcglide' ),
            'manage_options',
            'afcglide-settings',
            [ __CLASS__, 'render_page' ]
        );
    }

    /**
     * Enqueue media uploader for logo
     */
    public static function enqueue_admin_scripts( $hook ) {
        if ( 'settings_page_afcglide-settings' !== $hook ) {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_script(
            'afcglide-settings-upload',
            AFCG_PLUGIN_URL . 'assets/js/settings-upload.js',
            [ 'jquery' ],
            AFCG_VERSION,
            true
        );
    }

    public static function register_settings() {
        register_setting( 'afcglide_settings_group', 'afcglide_options' );

        // Branding Section (NEW)
        add_settings_section( 
            'afcglide_branding_section', 
            __( 'Branding', 'afcglide' ), 
            [ __CLASS__, 'render_branding_section' ],
            'afcglide-settings' 
        );

        add_settings_field( 
            'company_logo', 
            __( 'Company Logo', 'afcglide' ), 
            [ __CLASS__, 'render_field_logo' ], 
            'afcglide-settings', 
            'afcglide_branding_section' 
        );

        add_settings_field( 
            'company_name', 
            __( 'Company Name', 'afcglide' ), 
            [ __CLASS__, 'render_field_company_name' ], 
            'afcglide-settings', 
            'afcglide_branding_section' 
        );

        // General Section
        add_settings_section( 
            'afcglide_general_section', 
            __( 'General Settings', 'afcglide' ), 
            null, 
            'afcglide-settings' 
        );

        add_settings_field( 
            'posts_per_page', 
            __( 'Listings Per Page', 'afcglide' ), 
            [ __CLASS__, 'render_field_posts_per_page' ], 
            'afcglide-settings', 
            'afcglide_general_section' 
        );

        add_settings_field( 
            'currency_symbol', 
            __( 'Currency Symbol', 'afcglide' ), 
            [ __CLASS__, 'render_field_currency' ], 
            'afcglide-settings', 
            'afcglide_general_section' 
        );

        add_settings_field( 
            'google_maps_api_key', 
            __( 'Google Maps API Key', 'afcglide' ), 
            [ __CLASS__, 'render_field_maps_key' ], 
            'afcglide-settings', 
            'afcglide_general_section' 
        );

        add_settings_field( 
            'delete_on_uninstall', 
            __( 'Delete Data on Uninstall?', 'afcglide' ), 
            [ __CLASS__, 'render_field_delete' ], 
            'afcglide-settings', 
            'afcglide_general_section' 
        );
    }

    public static function render_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'AFCGlide Listings Settings', 'afcglide' ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'afcglide_settings_group' );
                do_settings_sections( 'afcglide-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Branding section description
     */
    public static function render_branding_section() {
        echo '<p>' . __( 'Configure your company branding for the submission portal and listings.', 'afcglide' ) . '</p>';
    }

    /**
     * Company Logo field with media uploader
     */
    public static function render_field_logo() {
        $options = get_option( 'afcglide_options' );
        $logo_id = isset( $options['company_logo'] ) ? $options['company_logo'] : '';
        $logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
        ?>
        <div class="afcglide-logo-upload">
            <input type="hidden" id="afcglide_logo_id" name="afcglide_options[company_logo]" value="<?php echo esc_attr( $logo_id ); ?>">
            
            <div class="afcglide-logo-preview" style="margin-bottom: 10px;">
                <?php if ( $logo_url ): ?>
                    <img src="<?php echo esc_url( $logo_url ); ?>" style="max-width: 300px; height: auto; display: block; border: 1px solid #ddd; padding: 10px; background: #fff;">
                <?php else: ?>
                    <div style="width: 300px; height: 150px; border: 2px dashed #ddd; display: flex; align-items: center; justify-content: center; color: #999;">
                        No logo uploaded
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="button" class="button button-secondary afcglide-upload-logo-btn">
                <?php _e( 'Upload Logo', 'afcglide' ); ?>
            </button>
            
            <?php if ( $logo_url ): ?>
                <button type="button" class="button afcglide-remove-logo-btn" style="margin-left: 10px;">
                    <?php _e( 'Remove Logo', 'afcglide' ); ?>
                </button>
            <?php endif; ?>
            
            <p class="description">
                <?php _e( 'Upload your company logo. Recommended size: 400x200px (PNG or JPG)', 'afcglide' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Company Name field
     */
    public static function render_field_company_name() {
        $options = get_option( 'afcglide_options' );
        $val = isset( $options['company_name'] ) ? $options['company_name'] : '';
        echo '<input type="text" name="afcglide_options[company_name]" value="' . esc_attr( $val ) . '" class="regular-text">';
        echo '<p class="description">' . __( 'Your company name (displays with logo on submission portal)', 'afcglide' ) . '</p>';
    }

    public static function render_field_posts_per_page() {
        $options = get_option( 'afcglide_options' );
        $val = isset( $options['posts_per_page'] ) ? $options['posts_per_page'] : 6;
        echo '<input type="number" name="afcglide_options[posts_per_page]" value="' . esc_attr( $val ) . '" class="small-text">';
    }

    public static function render_field_currency() {
        $options = get_option( 'afcglide_options' );
        $val = isset( $options['currency_symbol'] ) ? $options['currency_symbol'] : '$';
        echo '<input type="text" name="afcglide_options[currency_symbol]" value="' . esc_attr( $val ) . '" class="small-text">';
    }

    public static function render_field_maps_key() {
        $options = get_option( 'afcglide_options' );
        $val = isset( $options['google_maps_api_key'] ) ? $options['google_maps_api_key'] : '';
        echo '<input type="text" name="afcglide_options[google_maps_api_key]" value="' . esc_attr( $val ) . '" class="regular-text">';
    }

    public static function render_field_delete() {
        $options = get_option( 'afcglide_options' );
        $checked = isset( $options['delete_on_uninstall'] ) ? $options['delete_on_uninstall'] : '';
        echo '<input type="checkbox" name="afcglide_options[delete_on_uninstall]" value="1" ' . checked( 1, $checked, false ) . '> <span class="description" style="color:red;">Warning: This will delete all listings when the plugin is removed.</span>';
    }

    /**
     * Helper: Get company logo URL
     */
    public static function get_company_logo( $size = 'medium' ) {
        $options = get_option( 'afcglide_options' );
        $logo_id = isset( $options['company_logo'] ) ? $options['company_logo'] : '';
        
        if ( ! $logo_id ) {
            return false;
        }
        
        return wp_get_attachment_image_url( $logo_id, $size );
    }

    /**
     * Helper: Get company name
     */
    public static function get_company_name() {
        $options = get_option( 'afcglide_options' );
        return isset( $options['company_name'] ) ? $options['company_name'] : '';
    }
}