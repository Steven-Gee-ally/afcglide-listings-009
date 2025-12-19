<?php
/**
 * Submission Listing
 * Handles listing creation, updates, and Image Uploads
 *
 * @package AFCGlide\Listings\Submission
 */

namespace AFCGlide\Listings\Submission;

// Fallback to standard WP functions if these helpers aren't found
use AFCGlide\Listings\Helpers\Validator;
use AFCGlide\Listings\Helpers\Sanitizer;
use AFCGlide\Listings\Helpers\Message_Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

class Submission_Listing {

    public function __construct() {
        add_action( 'init', [ $this, 'handle_submission' ] );
    }

    /**
     * Handle standard POST form submission
     */
    public function handle_submission() {
        if ( ! isset( $_POST['afcglide_nonce'] ) || ! wp_verify_nonce( $_POST['afcglide_nonce'], 'afcglide_new_listing' ) ) {
            return;
        }

        if ( ! is_user_logged_in() ) {
            Message_Helper::error( __( 'You must be logged in to submit.', 'afcglide' ) );
            return;
        }

        $result = $this->create_listing();

        if ( is_wp_error( $result ) ) {
            Message_Helper::from_wp_error( $result );
        } else {
            // Redirect with success flag
            wp_safe_redirect( add_query_arg( 'listing_submitted', '1', wp_get_referer() ) );
            exit;
        }
    }

    /**
     * Create listing post with Featured Image Support
     */
    private function create_listing() {
        $data = $this->get_form_data();

        // 1. Basic Validation
        if ( empty( $data['title'] ) || strlen( $data['title'] ) < 5 ) {
            return new \WP_Error( 'bad_title', __( 'Title must be at least 5 characters.', 'afcglide' ) );
        }

        // 2. Insert the Post
        $post_id = wp_insert_post([
            'post_title'   => $data['title'],
            'post_content' => $data['description'],
            'post_type'    => 'afcglide_listing',
            'post_status'  => 'pending', // Awaiting Admin Approval
            'post_author'  => get_current_user_id(),
        ]);

        if ( is_wp_error( $post_id ) ) return $post_id;

        // 3. --- IMAGE UPLOAD LOGIC (NEW) ---
        if ( ! empty( $_FILES['hero_image']['name'] ) ) {
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );

            $attachment_id = media_handle_upload( 'hero_image', $post_id );
            
            if ( ! is_wp_error( $attachment_id ) ) {
                set_post_thumbnail( $post_id, $attachment_id );
            }
        }

        // 4. Save Metadata
        $this->save_metadata( $post_id, $data );

        return $post_id;
    }

    /**
     * Get and sanitize form data
     */
    private function get_form_data() {
        // We use class_exists to check if your custom Sanitizer is loaded
        $title = class_exists( 'AFCGlide\Listings\Helpers\Sanitizer' ) 
                 ? Sanitizer::text( $_POST['listing_title'] ?? '' ) 
                 : sanitize_text_field( $_POST['listing_title'] ?? '' );

        return [
            'title'       => $title,
            'description' => wp_kses_post( $_POST['listing_description'] ?? '' ),
            'price'       => sanitize_text_field( $_POST['listing_price'] ?? '' ),
            'agent_count' => intval( $_POST['agent_count'] ?? 0 ),
        ];
    }

    /**
     * Save metadata to post (Matches your Grid & Card logic)
     */
    private function save_metadata( $post_id, $data ) {
        if ( ! empty( $data['price'] ) ) {
            update_post_meta( $post_id, '_price', $data['price'] );
        }
        
        // Add default status so the badge shows up in the grid
        update_post_meta( $post_id, '_listing_status', 'for-sale' );
        update_post_meta( $post_id, '_submitted_by', get_current_user_id() );
    }

    public static function init() {
        new self();
    }
}