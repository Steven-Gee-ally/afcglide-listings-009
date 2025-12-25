<?php
/**
 * AFCGlide AJAX Handler
 * Handles all AJAX requests for listings submission and filtering
 *
 * @package AFCGlide\Listings
 * @since 3.6.6
 */

namespace AFCGlide\Listings;

use AFCGlide\Listings\Helpers\Sanitizer;
use AFCGlide\Listings\Submission\Submission_Files;

if ( ! defined( 'ABSPATH' ) ) exit;

class AFCGlide_Ajax_Handler {

    public static function init() {
        new self();
    }

    public function __construct() {
        // Search/Filter Actions
        add_action( 'wp_ajax_afcglide_filter_listings', [ $this, 'filter_listings' ] );
        add_action( 'wp_ajax_nopriv_afcglide_filter_listings', [ $this, 'filter_listings' ] );

        // Agent Submission Actions
        add_action( 'wp_ajax_afcglide_submit_listing', [ $this, 'handle_listing_submission' ] );
        
        // Enqueue scripts
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Enqueue public scripts and localize AJAX data
     */
    public function enqueue_scripts() {
        // Check if public.js exists, otherwise use afcglide-public.js
        $js_file = file_exists( AFCG_PATH . 'assets/js/public.js' ) 
            ? 'public.js' 
            : 'afcglide-public.js';
        
        wp_enqueue_script( 
            'afcglide-public', 
            AFCG_URL . 'assets/js/' . $js_file, 
            [ 'jquery' ], 
            AFCG_VERSION, 
            true 
        );

        wp_localize_script( 'afcglide-public', 'afcglide_ajax_object', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'afcglide_ajax_nonce' ),
            'strings'  => [
                'loading'    => __( 'Submitting...', 'afcglide' ),
                'success'    => __( '✨ Listing submitted successfully!', 'afcglide' ),
                'error'      => __( 'Error: Please check the form.', 'afcglide' ),
            ]
        ]);
        
        // Lightbox for single listings
        if ( is_singular( 'afcglide_listing' ) ) {
            wp_enqueue_style( 'glightbox', 'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css' );
            wp_enqueue_script( 'glightbox', 'https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js', [], '3.2.0', true );
        }
    }

    /**
     * Handle listing submission via AJAX
     */
    public function handle_listing_submission() {
        // Verify nonce
        check_ajax_referer( 'afcglide_ajax_nonce', 'nonce' );

        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => __( 'You must be logged in to submit a listing.', 'afcglide' ) ] );
        }

        // Validate required fields
        if ( empty( $_POST['property_title'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Property title is required.', 'afcglide' ) ] );
        }

        // Create the listing post
        $post_data = [
            'post_title'   => sanitize_text_field( $_POST['property_title'] ),
            'post_content' => isset( $_POST['property_description'] ) ? wp_kses_post( $_POST['property_description'] ) : '',
            'post_status'  => 'pending',
            'post_type'    => 'afcglide_listing',
            'post_author'  => get_current_user_id(),
        ];

        $post_id = wp_insert_post( $post_data );

        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( [ 'message' => __( 'Failed to create listing. Please try again.', 'afcglide' ) ] );
        }

        // Save all meta fields
        $this->save_listing_meta( $post_id );

        // Process file uploads (hero, stack, slider, agent photo, logo)
        $uploaded_files = [];
        if ( ! empty( $_FILES ) ) {
            $uploaded_files = $this->process_file_uploads( $post_id );
        }

        wp_send_json_success( [ 
            'message' => __( '✨ Success! Your property has been submitted for review.', 'afcglide' ),
            'post_id' => $post_id,
            'uploaded_files' => $uploaded_files
        ] );
    }

    /**
     * Save all listing meta data
     * 
     * @param int $post_id Post ID
     */
    private function save_listing_meta( $post_id ) {
        // Property Details
        if ( isset( $_POST['price'] ) ) {
            update_post_meta( $post_id, '_listing_price', sanitize_text_field( $_POST['price'] ) );
        }
        
        if ( isset( $_POST['beds'] ) ) {
            update_post_meta( $post_id, '_listing_beds', absint( $_POST['beds'] ) );
        }
        
        if ( isset( $_POST['baths'] ) ) {
            update_post_meta( $post_id, '_listing_baths', sanitize_text_field( $_POST['baths'] ) );
        }

        // Property Type
        if ( isset( $_POST['property_type'] ) ) {
            update_post_meta( $post_id, '_listing_property_type', sanitize_text_field( $_POST['property_type'] ) );
        }

        // Location Fields
        if ( isset( $_POST['property_address'] ) ) {
            update_post_meta( $post_id, '_property_address', sanitize_text_field( $_POST['property_address'] ) );
        }
        
        if ( isset( $_POST['property_city'] ) ) {
            update_post_meta( $post_id, '_property_city', sanitize_text_field( $_POST['property_city'] ) );
        }
        
        if ( isset( $_POST['property_state'] ) ) {
            update_post_meta( $post_id, '_property_state', sanitize_text_field( $_POST['property_state'] ) );
        }
        
        if ( isset( $_POST['property_country'] ) ) {
            update_post_meta( $post_id, '_property_country', sanitize_text_field( $_POST['property_country'] ) );
        }

        // GPS Coordinates
        if ( isset( $_POST['gps_lat'] ) ) {
            update_post_meta( $post_id, '_gps_lat', sanitize_text_field( $_POST['gps_lat'] ) );
        }
        
        if ( isset( $_POST['gps_lng'] ) ) {
            update_post_meta( $post_id, '_gps_lng', sanitize_text_field( $_POST['gps_lng'] ) );
        }

        // Amenities (array)
        if ( isset( $_POST['amenities'] ) && is_array( $_POST['amenities'] ) ) {
            $amenities = array_map( 'sanitize_text_field', $_POST['amenities'] );
            update_post_meta( $post_id, '_listing_amenities', $amenities );
        }

        // Agent Information
        if ( isset( $_POST['agent_name'] ) ) {
            update_post_meta( $post_id, '_agent_name', sanitize_text_field( $_POST['agent_name'] ) );
        }
        
        if ( isset( $_POST['agent_email'] ) ) {
            update_post_meta( $post_id, '_agent_email', sanitize_email( $_POST['agent_email'] ) );
        }
        
        if ( isset( $_POST['agent_phone'] ) ) {
            update_post_meta( $post_id, '_agent_phone', sanitize_text_field( $_POST['agent_phone'] ) );
        }
        
        if ( isset( $_POST['agent_license'] ) ) {
            update_post_meta( $post_id, '_agent_license', sanitize_text_field( $_POST['agent_license'] ) );
        }

        // Set default status
        update_post_meta( $post_id, '_listing_status', 'for_sale' );
    }

    /**
     * Process all file uploads
     * 
     * @param int $post_id Post ID
     * @return array Uploaded file info
     */
    private function process_file_uploads( $post_id ) {
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $uploaded = [];

        // 1. Hero Image (Main featured image)
        if ( ! empty( $_FILES['hero_image']['name'] ) ) {
            $hero_id = media_handle_upload( 'hero_image', $post_id );
            if ( ! is_wp_error( $hero_id ) ) {
                set_post_thumbnail( $post_id, $hero_id );
                update_post_meta( $post_id, '_hero_image_id', $hero_id );
                $uploaded['hero'] = $hero_id;
            }
        }

        // 2. Stack Images (3-photo stack)
        if ( ! empty( $_FILES['stack_images']['name'][0] ) ) {
            $stack_ids = $this->handle_multiple_uploads( 'stack_images', $post_id );
            if ( ! empty( $stack_ids ) ) {
                update_post_meta( $post_id, '_property_stack_ids', $stack_ids );
                $uploaded['stack'] = $stack_ids;
            }
        }

        // 3. Slider Images (Gallery)
        if ( ! empty( $_FILES['slider_images']['name'][0] ) ) {
            $slider_ids = $this->handle_multiple_uploads( 'slider_images', $post_id );
            if ( ! empty( $slider_ids ) ) {
                update_post_meta( $post_id, '_property_slider_ids', $slider_ids );
                $uploaded['slider'] = $slider_ids;
            }
        }

        // 4. Agent Photo
        if ( ! empty( $_FILES['agent_photo']['name'] ) ) {
            $agent_id = media_handle_upload( 'agent_photo', $post_id );
            if ( ! is_wp_error( $agent_id ) ) {
                update_post_meta( $post_id, '_agent_photo_id', $agent_id );
                $uploaded['agent_photo'] = $agent_id;
            }
        }

        // 5. Agency Logo
        if ( ! empty( $_FILES['agency_logo']['name'] ) ) {
            $logo_id = media_handle_upload( 'agency_logo', $post_id );
            if ( ! is_wp_error( $logo_id ) ) {
                update_post_meta( $post_id, '_agency_logo_id', $logo_id );
                $uploaded['agency_logo'] = $logo_id;
            }
        }

        return $uploaded;
    }

    /**
     * Handle multiple file uploads (for arrays like stack_images[], slider_images[])
     * 
     * @param string $file_key The $_FILES key
     * @param int    $post_id  Post ID
     * @return array Array of attachment IDs
     */
    private function handle_multiple_uploads( $file_key, $post_id ) {
        $attachment_ids = [];
        
        if ( ! isset( $_FILES[ $file_key ] ) ) {
            return $attachment_ids;
        }

        $files = $_FILES[ $file_key ];
        
        // WordPress expects each file to be a separate $_FILES entry
        foreach ( $files['name'] as $key => $value ) {
            if ( $files['name'][ $key ] ) {
                $file = [
                    'name'     => $files['name'][ $key ],
                    'type'     => $files['type'][ $key ],
                    'tmp_name' => $files['tmp_name'][ $key ],
                    'error'    => $files['error'][ $key ],
                    'size'     => $files['size'][ $key ]
                ];

                // Temporarily set this file as the upload
                $_FILES['temp_upload'] = $file;
                
                $attachment_id = media_handle_upload( 'temp_upload', $post_id );
                
                if ( ! is_wp_error( $attachment_id ) ) {
                    $attachment_ids[] = $attachment_id;
                }
            }
        }

        return $attachment_ids;
    }

    /**
     * Filter listings via AJAX
     */
    public function filter_listings() {
        check_ajax_referer( 'afcglide_ajax_nonce', 'nonce' );

        $page    = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        $filters = isset( $_POST['filters'] ) ? $_POST['filters'] : [];
        $args    = $this->build_query_args( $page, $filters );
        
        $query = new \WP_Query( $args );
        $html  = '';

        if ( $query->have_posts() ) {
            ob_start();
            while ( $query->have_posts() ) {
                $query->the_post();
                $this->render_listing_card();
            }
            $html = ob_get_clean();
        }

        wp_reset_postdata();
        
        wp_send_json_success( [ 
            'html'      => $html, 
            'max_pages' => $query->max_num_pages 
        ] );
    }

    /**
     * Render a single listing card
     */
    private function render_listing_card() {
        $price = get_post_meta( get_the_ID(), '_listing_price', true );
        $beds  = get_post_meta( get_the_ID(), '_listing_beds', true );
        $baths = get_post_meta( get_the_ID(), '_listing_baths', true );
        ?>
        <article class="afc-listing-card"> 
            <div class="afc-card-media">
                <?php if ( has_post_thumbnail() ) : ?>
                    <a href="<?php the_permalink(); ?>">
                        <?php the_post_thumbnail( 'large' ); ?>
                    </a>
                <?php else : ?>
                    <div class="afc-card-placeholder">🏠</div>
                <?php endif; ?>
                
                <?php if ( $price ) : ?>
                    <div class="afc-card-price-tag">
                        $<?php echo number_format( (float) $price ); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="afc-card-content">
                <h3 class="afc-card-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h3>
                
                <?php if ( $beds || $baths ) : ?>
                    <div class="afc-card-meta">
                        <?php if ( $beds ) : ?>
                            <span><?php echo esc_html( $beds ); ?> beds</span>
                        <?php endif; ?>
                        <?php if ( $baths ) : ?>
                            <span><?php echo esc_html( $baths ); ?> baths</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="afc-card-excerpt">
                    <?php echo wp_trim_words( get_the_content(), 15, '...' ); ?>
                </div>
                
                <a href="<?php the_permalink(); ?>" class="afcglide-btn">
                    <?php _e( 'View Details', 'afcglide' ); ?>
                </a>
            </div>
        </article>
        <?php
    }

    /**
     * Build query arguments for filtering
     * 
     * @param int   $page    Page number
     * @param array $filters Filter parameters
     * @return array Query arguments
     */
    private function build_query_args( $page, $filters ) {
        return [
            'post_type'      => 'afcglide_listing',
            'post_status'    => 'publish',
            'posts_per_page' => 9,
            'paged'          => $page,
        ];
    }
}