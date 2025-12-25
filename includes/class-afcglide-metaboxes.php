<?php
/**
 * AFCGlide Metaboxes - Property Data Management
 * Version 3.7.0 - Fixed and Optimized
 */

namespace AFCGlide\Listings;

if ( ! defined( 'ABSPATH' ) ) exit;

class AFCGlide_Metaboxes {

    // All meta keys we manage
    public static $meta_keys = [
        '_listing_price', 
        '_listing_beds', 
        '_listing_baths', 
        '_listing_sqft',
        '_listing_property_type',
        '_property_address',
        '_property_city',
        '_property_state',
        '_property_country',
        '_gps_lat', 
        '_gps_lng', 
        '_listing_amenities', 
        '_listing_status',
        '_agent_name', 
        '_agent_email',
        '_agent_phone', 
        '_agent_license', 
        '_agent_bio',
        '_agent_whatsapp', 
        '_whatsapp_message', 
        '_show_floating_whatsapp',
        '_agent_photo_id', 
        '_agency_logo_id', 
        '_hero_image_id',
        '_property_stack_ids',
        '_property_slider_ids',
        '_is_featured'
    ];

    public static function init() {
        add_action( 'add_meta_boxes', [ __CLASS__, 'add_metaboxes' ] );
        add_action( 'save_post_afcglide_listing', [ __CLASS__, 'save_metabox' ], 10, 2 );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin_assets' ] );
    }

    public static function admin_assets( $hook ) {
        global $post;
        if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ] ) ) {
            return;
        }
        if ( ! isset( $post ) || $post->post_type !== 'afcglide_listing' ) {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_style( 'afcglide-admin-css', AFCG_URL . 'assets/css/admin.css', [], AFCG_VERSION );
        wp_enqueue_script( 'afcglide-admin-js', AFCG_URL . 'assets/js/afcglide-admin.js', [ 'jquery' ], AFCG_VERSION, true );
    }

    public static function add_metaboxes() {
        add_meta_box( 
            'afc_details', 
            __( 'Property Details', 'afcglide' ), 
            [ __CLASS__, 'render_details' ], 
            'afcglide_listing', 
            'normal', 
            'high' 
        );
        
        add_meta_box( 
            'afc_location', 
            __( 'Location & GPS', 'afcglide' ), 
            [ __CLASS__, 'render_location' ], 
            'afcglide_listing', 
            'normal', 
            'high' 
        );
        
        add_meta_box( 
            'afc_amenities', 
            __( 'Amenities', 'afcglide' ), 
            [ __CLASS__, 'render_amenities' ], 
            'afcglide_listing', 
            'normal', 
            'default' 
        );
        
        add_meta_box( 
            'afc_agent', 
            __( 'Agent Information', 'afcglide' ), 
            [ __CLASS__, 'render_agent' ], 
            'afcglide_listing', 
            'side', 
            'default' 
        );
    }

    /**
     * Property Details
     */
    public static function render_details( $post ) {
        wp_nonce_field( 'afcglide_meta_nonce', 'afcglide_meta_nonce' );
        
        $price = get_post_meta( $post->ID, '_listing_price', true );
        $beds = get_post_meta( $post->ID, '_listing_beds', true );
        $baths = get_post_meta( $post->ID, '_listing_baths', true );
        $sqft = get_post_meta( $post->ID, '_listing_sqft', true );
        $status = get_post_meta( $post->ID, '_listing_status', true );
        $property_type = get_post_meta( $post->ID, '_listing_property_type', true );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="listing_price">Price ($)</label></th>
                <td>
                    <input type="number" 
                           id="listing_price" 
                           name="_listing_price" 
                           value="<?php echo esc_attr( $price ); ?>" 
                           step="1000"
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th><label for="listing_property_type">Property Type</label></th>
                <td>
                    <select id="listing_property_type" name="_listing_property_type" class="regular-text">
                        <option value="">Select Type</option>
                        <option value="villa" <?php selected( $property_type, 'villa' ); ?>>Villa</option>
                        <option value="condo" <?php selected( $property_type, 'condo' ); ?>>Condo</option>
                        <option value="apartment" <?php selected( $property_type, 'apartment' ); ?>>Apartment</option>
                        <option value="house" <?php selected( $property_type, 'house' ); ?>>House</option>
                        <option value="penthouse" <?php selected( $property_type, 'penthouse' ); ?>>Penthouse</option>
                        <option value="estate" <?php selected( $property_type, 'estate' ); ?>>Estate</option>
                        <option value="land" <?php selected( $property_type, 'land' ); ?>>Land</option>
                        <option value="commercial" <?php selected( $property_type, 'commercial' ); ?>>Commercial</option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th><label for="listing_beds">Bedrooms</label></th>
                <td>
                    <input type="number" 
                           id="listing_beds" 
                           name="_listing_beds" 
                           value="<?php echo esc_attr( $beds ); ?>" 
                           min="0"
                           class="small-text">
                </td>
            </tr>
            
            <tr>
                <th><label for="listing_baths">Bathrooms</label></th>
                <td>
                    <input type="number" 
                           id="listing_baths" 
                           name="_listing_baths" 
                           value="<?php echo esc_attr( $baths ); ?>" 
                           step="0.5"
                           min="0"
                           class="small-text">
                </td>
            </tr>
            
            <tr>
                <th><label for="listing_sqft">Square Feet</label></th>
                <td>
                    <input type="number" 
                           id="listing_sqft" 
                           name="_listing_sqft" 
                           value="<?php echo esc_attr( $sqft ); ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th><label for="listing_status">Status</label></th>
                <td>
                    <select id="listing_status" name="_listing_status" class="regular-text">
                        <option value="for_sale" <?php selected( $status, 'for_sale' ); ?>>For Sale</option>
                        <option value="just_listed" <?php selected( $status, 'just_listed' ); ?>>Just Listed</option>
                        <option value="under_contract" <?php selected( $status, 'under_contract' ); ?>>Under Contract</option>
                        <option value="sold" <?php selected( $status, 'sold' ); ?>>Sold</option>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Location & GPS
     */
    public static function render_location( $post ) {
        $address = get_post_meta( $post->ID, '_property_address', true );
        $city = get_post_meta( $post->ID, '_property_city', true );
        $state = get_post_meta( $post->ID, '_property_state', true );
        $country = get_post_meta( $post->ID, '_property_country', true );
        $lat = get_post_meta( $post->ID, '_gps_lat', true );
        $lng = get_post_meta( $post->ID, '_gps_lng', true );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="property_address">Street Address</label></th>
                <td>
                    <input type="text" 
                           id="property_address" 
                           name="_property_address" 
                           value="<?php echo esc_attr( $address ); ?>" 
                           class="regular-text"
                           placeholder="e.g. 123 Beach Road">
                </td>
            </tr>
            
            <tr>
                <th><label for="property_city">City</label></th>
                <td>
                    <input type="text" 
                           id="property_city" 
                           name="_property_city" 
                           value="<?php echo esc_attr( $city ); ?>" 
                           class="regular-text"
                           placeholder="e.g. Tamarindo">
                </td>
            </tr>
            
            <tr>
                <th><label for="property_state">State/Province</label></th>
                <td>
                    <input type="text" 
                           id="property_state" 
                           name="_property_state" 
                           value="<?php echo esc_attr( $state ); ?>" 
                           class="regular-text"
                           placeholder="e.g. Guanacaste">
                </td>
            </tr>
            
            <tr>
                <th><label for="property_country">Country</label></th>
                <td>
                    <input type="text" 
                           id="property_country" 
                           name="_property_country" 
                           value="<?php echo esc_attr( $country ); ?>" 
                           class="regular-text"
                           placeholder="e.g. Costa Rica">
                </td>
            </tr>
            
            <tr>
                <th><label>GPS Coordinates</label></th>
                <td>
                    <p class="description">For map display. Get coordinates from Google Maps.</p>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <input type="text" 
                               name="_gps_lat" 
                               value="<?php echo esc_attr( $lat ); ?>" 
                               placeholder="Latitude (e.g. 9.748)"
                               style="flex: 1;">
                        <input type="text" 
                               name="_gps_lng" 
                               value="<?php echo esc_attr( $lng ); ?>" 
                               placeholder="Longitude (e.g. -83.75)"
                               style="flex: 1;">
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Amenities
     */
    public static function render_amenities( $post ) {
        $amenities = get_post_meta( $post->ID, '_listing_amenities', true );
        if ( ! is_array( $amenities ) ) {
            $amenities = [];
        }
        
        $available_amenities = [
            'pool' => '🏊 Swimming Pool',
            'gym' => '💪 Gym/Fitness Center',
            'ocean_view' => '🌊 Ocean View',
            'beach_access' => '🏖️ Beach Access',
            'air_conditioning' => '❄️ Air Conditioning',
            'parking' => '🚗 Parking',
            'security' => '🔒 24/7 Security',
            'furnished' => '🛋️ Fully Furnished',
            'garden' => '🌳 Garden',
            'terrace' => '🏡 Terrace/Balcony',
            'wifi' => '📶 WiFi',
            'hot_water' => '🚿 Hot Water',
        ];
        ?>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
            <?php foreach ( $available_amenities as $value => $label ) : ?>
                <label style="display: flex; align-items: center; padding: 8px; background: #f9f9f9; border-radius: 4px;">
                    <input type="checkbox" 
                           name="_listing_amenities[]" 
                           value="<?php echo esc_attr( $value ); ?>" 
                           <?php checked( in_array( $value, $amenities ) ); ?>
                           style="margin-right: 8px;">
                    <?php echo esc_html( $label ); ?>
                </label>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Agent Information
     */
    public static function render_agent( $post ) {
        $agent_name = get_post_meta( $post->ID, '_agent_name', true );
        $agent_email = get_post_meta( $post->ID, '_agent_email', true );
        $agent_phone = get_post_meta( $post->ID, '_agent_phone', true );
        $agent_license = get_post_meta( $post->ID, '_agent_license', true );
        $agent_whatsapp = get_post_meta( $post->ID, '_agent_whatsapp', true );
        $show_whatsapp = get_post_meta( $post->ID, '_show_floating_whatsapp', true );
        ?>
        <div style="padding: 10px;">
            <p>
                <label><strong>Agent Name</strong></label><br>
                <input type="text" 
                       name="_agent_name" 
                       value="<?php echo esc_attr( $agent_name ); ?>" 
                       style="width: 100%;"
                       placeholder="John Smith">
            </p>
            
            <p>
                <label><strong>Email</strong></label><br>
                <input type="email" 
                       name="_agent_email" 
                       value="<?php echo esc_attr( $agent_email ); ?>" 
                       style="width: 100%;"
                       placeholder="agent@agency.com">
            </p>
            
            <p>
                <label><strong>Phone</strong></label><br>
                <input type="tel" 
                       name="_agent_phone" 
                       value="<?php echo esc_attr( $agent_phone ); ?>" 
                       style="width: 100%;"
                       placeholder="+1 (555) 123-4567">
            </p>
            
            <p>
                <label><strong>License Number</strong></label><br>
                <input type="text" 
                       name="_agent_license" 
                       value="<?php echo esc_attr( $agent_license ); ?>" 
                       style="width: 100%;"
                       placeholder="RE-123456">
            </p>
            
            <hr>
            
            <p>
                <label>
                    <input type="checkbox" 
                           name="_show_floating_whatsapp" 
                           value="1" 
                           <?php checked( $show_whatsapp, '1' ); ?>>
                    <strong>Enable WhatsApp Button</strong>
                </label>
            </p>
            
            <p>
                <label><strong>WhatsApp Number</strong></label><br>
                <input type="text" 
                       name="_agent_whatsapp" 
                       value="<?php echo esc_attr( $agent_whatsapp ); ?>" 
                       style="width: 100%;"
                       placeholder="+506-1234-5678">
            </p>
        </div>
        <?php
    }

    /**
     * Save all metabox data
     */
    public static function save_metabox( $post_id, $post ) {
        // Security checks
        if ( ! isset( $_POST['afcglide_meta_nonce'] ) ) {
            return;
        }
        
        if ( ! wp_verify_nonce( $_POST['afcglide_meta_nonce'], 'afcglide_meta_nonce' ) ) {
            return;
        }
        
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save all meta fields
        foreach ( self::$meta_keys as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                $value = $_POST[ $field ];
                
                // Handle arrays (like amenities)
                if ( is_array( $value ) ) {
                    $value = array_map( 'sanitize_text_field', $value );
                } else {
                    $value = sanitize_text_field( $value );
                }
                
                update_post_meta( $post_id, $field, $value );
            } else {
                // Handle unchecked checkboxes
                if ( $field === '_show_floating_whatsapp' ) {
                    update_post_meta( $post_id, $field, '0' );
                } elseif ( $field === '_listing_amenities' ) {
                    update_post_meta( $post_id, $field, [] );
                }
            }
        }
    }
}