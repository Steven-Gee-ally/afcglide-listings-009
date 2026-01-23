<?php
namespace AFCGlide\Listings;

use AFCGlide\Core\Constants as C;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AFCGlide Metaboxes v4.1.0
 * Handles the administrative interface for luxury listings.
 *
 * @package AFCGlide\Listings
 */
class AFCGlide_Metaboxes {

    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_post_content_support' ] );
        add_action( 'add_meta_boxes', [ __CLASS__, 'add_metaboxes' ] );
        add_action( 'save_post', [ __CLASS__, 'save_metaboxes' ], 10, 2 );
        add_action( 'admin_notices', [ __CLASS__, 'render_admin_notices' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );
    }

    /** Enqueue admin scripts & styles */
    public static function enqueue_admin_assets( $hook ) {
        global $post;
        if ( ! $post || $post->post_type !== C::POST_TYPE ) return;

        if ( in_array( $hook, [ 'post.php', 'post-new.php' ] ) ) {
            wp_enqueue_media();
            wp_enqueue_script( 'afcglide-admin-js', AFCG_URL . 'assets/js/admin-listings.js', [ 'jquery' ], C::VERSION, true );
            wp_enqueue_style( 'afcglide-admin-css', AFCG_URL . 'assets/css/admin-listings.css', [], C::VERSION );
        }
    }

    public static function register_post_content_support() {
        add_post_type_support( C::POST_TYPE, 'editor' );
    }

    /** Add all metaboxes */
    public static function add_metaboxes() {
        remove_meta_box( 'submitdiv', C::POST_TYPE, 'side' );
        remove_meta_box( 'postimagediv', C::POST_TYPE, 'side' );
        remove_meta_box( 'authordiv', C::POST_TYPE, 'side' );

        $boxes = [
            'afc_1_intro'        => [ __( 'Property Description', 'afcglide' ), 'render_intro_metabox' ],
            'afc_2_description'  => [ __( 'Property Narrative', 'afcglide' ), 'render_description_metabox' ],
            'afc_3_details'      => [ __( 'Property Specifications', 'afcglide' ), 'render_details_metabox' ],
            'afc_4_media'        => [ __( 'Visual Command Center', 'afcglide' ), 'render_media_metabox' ],
            'afc_5_slider'       => [ __( 'Property Gallery Slider', 'afcglide' ), 'render_gallery_metabox' ],
            'afc_6_location'     => [ __( 'Location & GPS', 'afcglide' ), 'render_location_metabox' ],
            'afc_7_amenities'    => [ __( 'Property Features', 'afcglide' ), 'render_amenities_metabox' ],
            'afc_8_agent'        => [ __( 'Agent Branding', 'afcglide' ), 'render_agent_metabox' ],
            'afc_10_intelligence'=> [ __( 'Asset Intelligence & Files', 'afcglide' ), 'render_intelligence_metabox' ],
            'afc_11_publish'     => [ __( 'Publish Listing Control', 'afcglide' ), 'render_publish_metabox' ],
        ];

        foreach ( $boxes as $id => $data ) {
            add_meta_box( $id, $data[0], [ __CLASS__, $data[1] ], C::POST_TYPE, 'normal', 'high' );
        }
    }

    /** -----------------------
     * Metabox Render Functions
     * ----------------------- */

    public static function render_intro_metabox( $post ) {
        $intro    = C::get_meta( $post->ID, C::META_INTRO );
        $intro_es = C::get_meta( $post->ID, C::META_INTRO_ES );
        ?>
        <div class="afc-metabox-content">
            <label><?php _e('Property Headline (English)', 'afcglide'); ?></label>
            <input type="text" name="_listing_intro_text" value="<?php echo esc_attr($intro); ?>" class="afc-input">
            
            <label><?php _e('Título de la Propiedad (Español)', 'afcglide'); ?></label>
            <input type="text" name="_listing_intro_text_es" value="<?php echo esc_attr($intro_es); ?>" class="afc-input">
        </div>
        <?php
    }

    public static function render_description_metabox( $post ) {
        $narrative    = C::get_meta( $post->ID, C::META_NARRATIVE );
        $narrative_es = C::get_meta( $post->ID, C::META_NARRATIVE_ES );
        ?>
        <div class="afc-metabox-content">
            <p><?php _e('Property Narrative (English)', 'afcglide'); ?></p>
            <?php wp_editor( $narrative, '_listing_narrative', [ 'textarea_name' => '_listing_narrative', 'media_buttons' => false ] ); ?>

            <p><?php _e('Narrativa de la Propiedad (Español)', 'afcglide'); ?></p>
            <?php wp_editor( $narrative_es, '_listing_narrative_es', [ 'textarea_name' => '_listing_narrative_es', 'media_buttons' => false ] ); ?>
        </div>
        <?php
    }

    public static function render_details_metabox( $post ) {
        $fields = [
            '_listing_price' => C::META_PRICE,
            '_listing_beds'  => C::META_BEDS,
            '_listing_baths' => C::META_BATHS,
            '_listing_sqft'  => C::META_SQFT,
        ];
        ?>
        <div class="afc-metabox-content">
            <?php foreach ($fields as $key => $meta) : 
                $val = esc_attr( C::get_meta($post->ID, $meta) ); ?>
                <div class="afc-field">
                    <label><?php echo esc_html( ucwords(str_replace('_', ' ', $key)) ); ?></label>
                    <input type="text" name="<?php echo esc_attr($key); ?>" value="<?php echo $val; ?>" class="afc-input">
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    public static function render_media_metabox( $post ) {
        $hero_id = C::get_meta($post->ID, C::META_HERO_ID);
        $url     = $hero_id ? wp_get_attachment_image_url($hero_id, 'medium') : '';
        ?>
        <div class="afc-metabox-content">
            <input type="hidden" name="_listing_hero_id" value="<?php echo esc_attr($hero_id); ?>">
            <?php if ($url) : ?>
                <img src="<?php echo esc_url($url); ?>" style="max-width:150px;">
            <?php endif; ?>
            <button type="button" class="button afc-upload-btn"><?php _e('Select Hero Photo', 'afcglide'); ?></button>
        </div>
        <?php
    }

    public static function render_gallery_metabox( $post ) {
        $ids = C::get_meta($post->ID, C::META_GALLERY_IDS) ?: [];
        ?>
        <div class="afc-metabox-content">
            <input type="hidden" name="_listing_gallery_ids" value="<?php echo esc_attr( implode(',', $ids) ); ?>">
            <button type="button" class="button afc-upload-btn"><?php _e('Manage Gallery', 'afcglide'); ?></button>
        </div>
        <?php
    }

    public static function render_location_metabox( $post ) {
        $address = C::get_meta($post->ID, C::META_ADDRESS);
        $lat     = C::get_meta($post->ID, C::META_GPS_LAT);
        $lng     = C::get_meta($post->ID, C::META_GPS_LNG);
        ?>
        <div class="afc-metabox-content">
            <input type="text" name="_listing_address" value="<?php echo esc_attr($address); ?>" placeholder="<?php _e('Street, City, State', 'afcglide'); ?>">
            <input type="text" name="_gps_lat" value="<?php echo esc_attr($lat); ?>" placeholder="Latitude">
            <input type="text" name="_gps_lng" value="<?php echo esc_attr($lng); ?>" placeholder="Longitude">
        </div>
        <?php
    }

    public static function render_amenities_metabox( $post ) {
        $selected = C::get_meta($post->ID, C::META_AMENITIES) ?: [];
        $options  = [ 'Gourmet Kitchen'=>'🍳','Infinity Pool'=>'🌊','Ocean View'=>'🌅','Wine Cellar'=>'🍷',
                      'Private Gym'=>'🏋️','Smart Home Tech'=>'📱','Gated Community'=>'🏰','Beach Front'=>'🏖️' ];
        ?>
        <div class="afc-metabox-content">
            <?php foreach ($options as $label => $icon) : ?>
                <label>
                    <input type="checkbox" name="_listing_amenities[]" value="<?php echo esc_attr($label); ?>" <?php checked(in_array($label,$selected)); ?>>
                    <?php echo esc_html($icon.' '.$label); ?>
                </label>
            <?php endforeach; ?>
        </div>
        <?php
    }

    public static function render_agent_metabox( $post ) {
        wp_nonce_field( C::NONCE_META, 'afcglide_nonce' );
        $agent_name  = C::get_meta($post->ID, C::META_AGENT_NAME);
        $agent_phone = C::get_meta($post->ID, C::META_AGENT_PHONE);
        $agent_photo = C::get_meta($post->ID, C::META_AGENT_PHOTO);

        $users = get_users([ 'role__in' => ['administrator','editor','author'] ]);
        ?>
        <div class="afc-metabox-content">
            <select id="afc_agent_selector">
                <option value=""><?php _e('-- Choose Agent --', 'afcglide'); ?></option>
                <?php foreach ($users as $user) : ?>
                    <option value="<?php echo $user->ID; ?>" <?php selected($user->display_name, $agent_name); ?>><?php echo esc_html($user->display_name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    public static function render_intelligence_metabox( $post ) {
        $pdf_id   = C::get_meta($post->ID, C::META_PDF_ID);
        $stats    = intval(C::get_meta($post->ID, C::META_VIEWS));
        $pdf_file = $pdf_id && file_exists(get_attached_file($pdf_id)) ? basename(get_attached_file($pdf_id)) : __('No document attached', 'afcglide');
        ?>
        <div class="afc-metabox-content">
            <input type="hidden" name="_listing_pdf_id" value="<?php echo esc_attr($pdf_id); ?>">
            <span><?php echo esc_html($pdf_file); ?></span>
            <p><?php printf(__('Real-Time Hits: %d', 'afcglide'), $stats); ?></p>
        </div>
        <?php
    }

    public static function render_publish_metabox( $post ) {
        $status = $post->post_status;
        $statuses = [ 'publish'=>__('Active','afcglide'), 'pending'=>__('Pending','afcglide'), 'sold'=>__('Sold','afcglide'), 'draft'=>__('Draft','afcglide') ];
        ?>
        <div class="afc-metabox-content">
            <select name="_listing_market_status">
                <?php foreach ($statuses as $val=>$label) : ?>
                    <option value="<?php echo esc_attr($val); ?>" <?php selected($status,$val); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    /** -----------------------
     * Save Metaboxes
     * ----------------------- */
    public static function save_metaboxes( $post_id, $post ) {
        if ( ! isset($_POST['afcglide_nonce']) || ! wp_verify_nonce($_POST['afcglide_nonce'], C::NONCE_META) ) return;
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
        if ( ! current_user_can('edit_post', $post_id) ) return;

        $meta_map = [
            '_listing_intro_text'    => C::META_INTRO,
            '_listing_intro_text_es' => C::META_INTRO_ES,
            '_listing_narrative'     => C::META_NARRATIVE,
            '_listing_narrative_es'  => C::META_NARRATIVE_ES,
            '_listing_price'         => C::META_PRICE,
            '_listing_beds'          => C::META_BEDS,
            '_listing_baths'         => C::META_BATHS,
            '_listing_sqft'          => C::META_SQFT,
            '_listing_address'       => C::META_ADDRESS,
            '_gps_lat'               => C::META_GPS_LAT,
            '_gps_lng'               => C::META_GPS_LNG,
            '_agent_name_display'    => C::META_AGENT_NAME,
            '_agent_phone_display'   => C::META_AGENT_PHONE,
            '_agent_photo_id'        => C::META_AGENT_PHOTO,
            '_listing_hero_id'       => C::META_HERO_ID,
            '_listing_pdf_id'        => C::META_PDF_ID,
        ];

        foreach ($meta_map as $field=>$meta_key) {
            if ( isset($_POST[$field]) ) {
                $value = strpos($field,'narrative')!==false ? wp_kses_post($_POST[$field]) : sanitize_text_field($_POST[$field]);
                C::update_meta($post_id,$meta_key,$value);

                if ($field === '_listing_hero_id' && $value) set_post_thumbnail($post_id,intval($value));
            }
        }

        // Checkbox & Gallery Sync
        C::update_meta($post_id,C::META_SHOW_WA,isset($_POST['_show_floating_whatsapp'])?'1':'0');

        $gallery = isset($_POST['_listing_gallery_ids']) ? array_map('intval',array_filter(explode(',',$_POST['_listing_gallery_ids']))) : [];
        C::update_meta($post_id,C::META_GALLERY_IDS,$gallery);

        $amenities = isset($_POST['_listing_amenities']) ? array_map('sanitize_text_field',$_POST['_listing_amenities']) : [];
        C::update_meta($post_id,C::META_AMENITIES,$amenities);

        // Market status
        if ( isset($_POST['_listing_market_status']) && $_POST['_listing_market_status'] !== $post->post_status ) {
            wp_update_post(['ID'=>$post_id,'post_status'=>sanitize_text_field($_POST['_listing_market_status'])]);
        }

        // Clear Cache
        if ( class_exists('\AFCGlide\Listings\AFCGlide_Ajax_Handler') ) {
            \AFCGlide\Listings\AFCGlide_Ajax_Handler::clear_filter_cache();
        }
    }

    public static function render_admin_notices() {
        global $pagenow,$post;
        if ( $pagenow==='post.php' && isset($_GET['message']) && $_GET['message']=='6' && get_post_type($post)===C::POST_TYPE ) {
            echo '<div class="notice notice-success is-dismissible"><p>'.__('Listing is now live!','afcglide').'</p></div>';
        }
    }
}
