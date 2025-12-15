<?php
/**
 * Submission Listing
 * Handles listing creation and updates
 *
 * @package AFCGlide\Listings\Submission
 */

namespace AFCGlide\Listings\Submission;

use AFCGlide\Listings\Helpers\Validator;
use AFCGlide\Listings\Helpers\Sanitizer;
use AFCGlide\Listings\Helpers\Message_Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

class Submission_Listing {

    /**
     * Initialize listing handlers
     */
    public function __construct() {
        add_action( 'init', [ $this, 'handle_submission' ] );
        add_action( 'wp_ajax_afcglide_submit_listing', [ $this, 'handle_ajax_submission' ] );
    }

    /**
     * Handle standard POST form submission
     */
    public function handle_submission() {
        // Check if form was submitted
        if ( ! isset( $_POST['afcglide_nonce'] ) ) {
            return;
        }

        // Verify nonce
        if ( ! Validator::nonce( $_POST['afcglide_nonce'], 'afcglide_new_listing' ) ) {
            Message_Helper::error( __( 'Security check failed. Please try again.', 'afcglide' ) );
            return;
        }

        // Check if user is logged in
        if ( ! Validator::user_logged_in() ) {
            Message_Helper::error( __( 'You must be logged in to submit a listing.', 'afcglide' ) );
            return;
        }

        // Process the submission
        $result = $this->create_listing();

        if ( is_wp_error( $result ) ) {
            Message_Helper::from_wp_error( $result );
        } else {
            Message_Helper::success( __( 'Listing submitted successfully! It is awaiting admin approval.', 'afcglide' ) );
            
            // Redirect to prevent resubmission
            wp_safe_redirect( add_query_arg( 'listing_submitted', '1', wp_get_referer() ) );
            exit;
        }
    }

    /**
     * Handle AJAX submission
     */
    public function handle_ajax_submission() {
        // Verify nonce
        if ( ! Validator::nonce( $_POST['nonce'] ?? '', 'afcglide_nonce' ) ) {
            wp_send_json_error( [ 'message' => __( 'Security check failed.', 'afcglide' ) ] );
        }

        // Check if user is logged in
        if ( ! Validator::user_logged_in() ) {
            wp_send_json_error( [ 'message' => __( 'You must be logged in.', 'afcglide' ) ] );
        }

        // Process the submission
        $result = $this->create_listing();

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        } else {
            wp_send_json_success( [
                'message' => __( 'Listing submitted successfully!', 'afcglide' ),
                'post_id' => $result,
            ]);
        }
    }

    /**
     * Create listing post
     */
    private function create_listing() {
        // Get and sanitize form data
        $data = $this->get_form_data();

        // Validate data
        $validation = $this->validate_data( $data );
        if ( is_wp_error( $validation ) ) {
            return $validation;
        }

        // Create the post
        $post_id = $this->insert_post( $data );
        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        // Save metadata
        $this->save_metadata( $post_id, $data );

        // Allow other plugins to hook in
        do_action( 'afcglide_after_listing_created', $post_id, $data );

        return $post_id;
    }

    /**
     * Get and sanitize form data
     */
    private function get_form_data() {
        return [
            'title'       => Sanitizer::text( $_POST['listing_title'] ?? '' ),
            'description' => Sanitizer::html( $_POST['listing_description'] ?? '' ),
            'price'       => Sanitizer::text( $_POST['listing_price'] ?? '' ),
            'agent_count' => Sanitizer::int( $_POST['agent_count'] ?? 0 ),
            'agents'      => $this->get_agent_data(),
        ];
    }

    /**
     * Get agent data from form
     */
    private function get_agent_data() {
        $agent_count = Sanitizer::int( $_POST['agent_count'] ?? 0 );
        $agents = [];

        for ( $i = 1; $i <= $agent_count; $i++ ) {
            $name  = Sanitizer::text( $_POST["agent_name_$i"] ?? '' );
            $email = Sanitizer::email( $_POST["agent_email_$i"] ?? '' );

            if ( ! empty( $name ) || ! empty( $email ) ) {
                $agents[] = [
                    'name'  => $name,
                    'email' => $email,
                ];
            }
        }

        return $agents;
    }

    /**
     * Validate form data
     */
    private function validate_data( $data ) {
        // Title is required
        if ( ! Validator::required( $data['title'] ) ) {
            return new \WP_Error( 
                'missing_title', 
                __( 'Listing title is required.', 'afcglide' ) 
            );
        }

        // Description is required
        if ( ! Validator::required( $data['description'] ) ) {
            return new \WP_Error( 
                'missing_description', 
                __( 'Listing description is required.', 'afcglide' ) 
            );
        }

        // Title min length
        if ( ! Validator::min_length( $data['title'], 5 ) ) {
            return new \WP_Error( 
                'title_too_short', 
                __( 'Listing title must be at least 5 characters.', 'afcglide' ) 
            );
        }

        // Description min length
        if ( ! Validator::min_length( $data['description'], 20 ) ) {
            return new \WP_Error( 
                'description_too_short', 
                __( 'Listing description must be at least 20 characters.', 'afcglide' ) 
            );
        }

        // Validate agent emails if provided
        foreach ( $data['agents'] as $agent ) {
            if ( ! empty( $agent['email'] ) && ! Validator::email( $agent['email'] ) ) {
                return new \WP_Error( 
                    'invalid_agent_email', 
                    __( 'One or more agent emails are invalid.', 'afcglide' ) 
                );
            }
        }

        return true;
    }

    /**
     * Insert post into database
     */
    private function insert_post( $data ) {
        $post_data = [
            'post_title'   => $data['title'],
            'post_content' => $data['description'],
            'post_type'    => 'afcglide_listing',
            'post_status'  => apply_filters( 'afcglide_default_post_status', 'pending' ),
            'post_author'  => get_current_user_id(),
        ];

        $post_id = wp_insert_post( $post_data, true );

        if ( is_wp_error( $post_id ) ) {
            return new \WP_Error( 
                'creation_failed', 
                __( 'Failed to create listing. Please try again.', 'afcglide' ) 
            );
        }

        return $post_id;
    }

    /**
     * Save metadata to post
     */
    private function save_metadata( $post_id, $data ) {
        // Save price
        if ( ! empty( $data['price'] ) ) {
            update_post_meta( $post_id, '_price', $data['price'] );
        }

        // Save agent count
        if ( $data['agent_count'] > 0 ) {
            update_post_meta( $post_id, '_agent_count', $data['agent_count'] );
        }

        // Save individual agents
        foreach ( $data['agents'] as $index => $agent ) {
            $i = $index + 1;
            
            if ( ! empty( $agent['name'] ) ) {
                update_post_meta( $post_id, "_agent_name_$i", $agent['name'] );
            }
            
            if ( ! empty( $agent['email'] ) ) {
                update_post_meta( $post_id, "_agent_email_$i", $agent['email'] );
            }
        }

        // Save submission date
        update_post_meta( $post_id, '_submission_date', current_time( 'mysql' ) );

        // Save submitter ID
        update_post_meta( $post_id, '_submitted_by', get_current_user_id() );

        // Allow custom meta to be saved
        do_action( 'afcglide_save_listing_meta', $post_id, $data );
    }

    /**
     * Update existing listing
     */
    public function update_listing( $post_id, $data ) {
        // Check if post exists
        if ( ! Validator::post_exists( $post_id ) ) {
            return new \WP_Error( 'post_not_found', __( 'Listing not found.', 'afcglide' ) );
        }

        // Check if user can edit this post
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return new \WP_Error( 'no_permission', __( 'You cannot edit this listing.', 'afcglide' ) );
        }

        // Update post
        $updated = wp_update_post([
            'ID'           => $post_id,
            'post_title'   => $data['title'],
            'post_content' => $data['description'],
        ], true );

        if ( is_wp_error( $updated ) ) {
            return $updated;
        }

        // Update metadata
        $this->save_metadata( $post_id, $data );

        do_action( 'afcglide_after_listing_updated', $post_id, $data );

        return true;
    }

    /**
     * Delete listing
     */
    public function delete_listing( $post_id ) {
        if ( ! current_user_can( 'delete_post', $post_id ) ) {
            return new \WP_Error( 'no_permission', __( 'You cannot delete this listing.', 'afcglide' ) );
        }

        $deleted = wp_delete_post( $post_id, true );

        if ( ! $deleted ) {
            return new \WP_Error( 'delete_failed', __( 'Failed to delete listing.', 'afcglide' ) );
        }

        do_action( 'afcglide_after_listing_deleted', $post_id );

        return true;
    }

    /**
     * Initialize (called by main plugin)
     */
    public static function init() {
        new self();
    }
}