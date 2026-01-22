<?php
namespace AFCGlide\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AFCGlide Lead Capture Engine
 * Version 1.0.0 - Unbreakable & Native
 */
class AFCGlide_Leads {

    public static function init() {
        add_action( 'wp_ajax_afc_submit_lead', [ __CLASS__, 'handle_lead_submission' ] );
        add_action( 'wp_ajax_nopriv_afc_submit_lead', [ __CLASS__, 'handle_lead_submission' ] );
    }

    /**
     * Process Private Showing Requests
     */
    public static function handle_lead_submission() {
        check_ajax_referer( Constants::NONCE_AJAX, 'security' );

        $post_id = intval( $_POST['post_id'] ?? 0 );
        $name    = sanitize_text_field( $_POST['lead_name'] ?? '' );
        $email   = sanitize_email( $_POST['lead_email'] ?? '' );
        $phone   = sanitize_text_field( $_POST['lead_phone'] ?? '' );
        $message = sanitize_textarea_field( $_POST['lead_message'] ?? '' );

        if ( empty($name) || empty($email) || !is_email($email) ) {
            wp_send_json_error( 'Invalid credentials. Please provide name and email.' );
        }

        // Enterprise Audit Trail: Save Lead to Asset Meta
        $lead_data = [
            'name'      => $name,
            'email'     => $email,
            'phone'     => $phone,
            'message'   => $message,
            'timestamp' => time(),
            'ip'        => $_SERVER['REMOTE_ADDR']
        ];

        $existing_leads = get_post_meta( $post_id, Constants::META_LEADS, true ) ?: [];
        $existing_leads[] = $lead_data;
        
        // Keep last 100 leads per asset
        if ( count($existing_leads) > 100 ) array_shift($existing_leads);
        
        update_post_meta( $post_id, Constants::META_LEADS, $existing_leads );

        // Optional: Trigger Email to Agent
        $agent_id = get_post_field( 'post_author', $post_id );
        $agent_email = get_the_author_meta( 'user_email', $agent_id );
        
        $subject = '💎 NEW LUXURY LEAD: ' . get_the_title($post_id);
        $body = "New inquiry for asset #" . $post_id . "\n\nName: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";
        
        wp_mail( $agent_email, $subject, $body );

        wp_send_json_success( '✨ INQUIRY SECURED: Our specialist will contact you shortly.' );
    }
}
