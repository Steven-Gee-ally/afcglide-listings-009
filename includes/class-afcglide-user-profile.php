<?php
namespace AFCGlide\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AFCGlide User Profile Extensions
 * Version 2.0 - Identity Shield Integration
 */
class AFCGlide_User_Profile {

    public static function init() {
        add_action( 'show_user_profile', [ __CLASS__, 'add_agent_fields' ] );
        add_action( 'edit_user_profile', [ __CLASS__, 'add_agent_fields' ] );
        add_action( 'personal_options_update', [ __CLASS__, 'save_agent_fields' ] );
        add_action( 'edit_user_profile_update', [ __CLASS__, 'save_agent_fields' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_profile_scripts' ] );
        
        // Add lockdown notice
        add_action( 'admin_notices', [ __CLASS__, 'show_identity_shield_notice' ] );
        
        // Add custom styling to profile page
        add_action( 'admin_head-profile.php', [ __CLASS__, 'profile_page_styles' ] );
        add_action( 'admin_head-user-edit.php', [ __CLASS__, 'profile_page_styles' ] );
    }

    /**
     * Show Identity Shield warning banner
     */
    public static function show_identity_shield_notice() {
        $screen = get_current_screen();
        if ( ! $screen || ! in_array( $screen->id, ['profile', 'user-edit'] ) ) {
            return;
        }

        if ( get_option('afc_identity_shield', '0') === '1' && ! current_user_can('manage_options') ) {
            ?>
            <div class="notice notice-warning" style="border-left-color: #f59e0b; background: #fffbeb;">
                <p style="display: flex; align-items: center; gap: 12px; margin: 12px 0;">
                    <span style="font-size: 24px;">🛡️</span>
                    <strong style="color: #92400e;">Identity Shield Active:</strong>
                    <span style="color: #78350f;">Your agent profile is protected and cannot be modified. Contact your Lead Broker to make changes.</span>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Enqueue media and scripts for the profile page
     */
    public static function enqueue_profile_scripts( $hook ) {
        if ( ! in_array( $hook, ['profile.php', 'user-edit.php'] ) ) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_script( 'afcglide-admin-js', AFCG_URL . 'assets/js/afcglide-admin.js', ['jquery'], '1.2', true );
    }

    /**
     * Add custom styling to profile page
     */
    public static function profile_page_styles() {
        ?>
        <style>
            /* 🎨 AFCGlide Premium Profile Styling */
            .afc-profile-section {
                background: white;
                padding: 40px;
                border-radius: 16px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
                margin: 30px 0;
                border-left: 6px solid #0ea5e9; /* Sky Blue Brand */
                max-width: 800px;
            }
            .afc-profile-section h2 {
                padding: 0 0 20px 0 !important;
                margin-bottom: 20px !important;
                border-bottom: 2px solid #f1f5f9;
                color: #0f172a;
                font-size: 20px !important;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .afc-profile-section .form-table th { width: 200px; padding: 20px 10px 20px 0; }
            .afc-profile-section .form-table td { padding: 15px 10px; }
            .afc-profile-section input[type="text"], 
            .afc-profile-section textarea {
                background: #f8fafc;
                border: 1px solid #cbd5e1;
                border-radius: 8px;
                padding: 10px;
                transition: all 0.2s;
            }
            .afc-profile-section input[type="text"]:focus,
            .afc-profile-section textarea:focus {
                background: white;
                border-color: #0ea5e9;
                box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
            }

            /* 👻 GHOST MODE: Hide Standard WP Clutter for Agents ONLY */
            body.afc-agent-portal #your-profile > h2, /* Standard Headers */
            body.afc-agent-portal #your-profile > h3,
            body.afc-agent-portal .user-rich-editing-wrap,
            body.afc-agent-portal .user-admin-color-wrap,
            body.afc-agent-portal .user-comment-shortcuts-wrap,
            body.afc-agent-portal .show-admin-bar,
            body.afc-agent-portal .user-syntax-highlighting-wrap,
            body.afc-agent-portal .user-url-wrap,
            body.afc-agent-portal .user-description-wrap,
            body.afc-agent-portal .user-profile-picture,
            body.afc-agent-portal .user-sessions-wrap
            { display: none !important; }

            /* Ensure our section header remains visible */
            body.afc-agent-portal .afc-profile-section h2 { display: flex !important; }
            
            /* Clean up the top for Agents */
            body.afc-agent-portal .wrap > h1 { 
                font-size: 24px; font-weight: 800; color: #0ea5e9; text-transform: uppercase; letter-spacing: 1px;
                margin-bottom: 20px;
            }
        </style>
        <?php
    }

    /**
     * Add AFCGlide agent fields to profile
     */
    public static function add_agent_fields( $user ) {
        $is_locked = get_option('afc_identity_shield', '0') === '1' && ! current_user_can('manage_options');
        $disabled = $is_locked ? 'disabled' : '';
        
        // Get existing values
        $phone = get_user_meta( $user->ID, 'agent_phone', true );
        $photo = get_user_meta( $user->ID, 'agent_photo', true ); // Added
        $photo_url = $photo ? wp_get_attachment_image_url( $photo, 'thumbnail' ) : '';
        $license = get_user_meta( $user->ID, 'agent_license', true );
        $bio = get_user_meta( $user->ID, 'agent_bio', true );
        $office = get_user_meta( $user->ID, 'agent_office', true );
        $specialties = get_user_meta( $user->ID, 'agent_specialties', true );
        ?>
        
        <div class="afc-profile-section">
            <h2>
                <span style="font-size: 28px;">🏢</span>
                AFCGlide Agent Profile
                <?php if ($is_locked) : ?>
                    <span style="font-size: 20px; margin-left: auto;">🔒</span>
                <?php endif; ?>
            </h2>
            
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><label for="agent_photo">Profile Photo</label></th>
                        <td>
                            <div class="afc-agent-photo-preview" style="margin-bottom: 15px;">
                                <?php if ($photo_url) : ?>
                                    <img src="<?php echo esc_url($photo_url); ?>" id="afc-user-photo-img" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                <?php else : ?>
                                    <div id="afc-user-photo-placeholder" style="width: 100px; height: 100px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 40px;">👤</div>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="agent_photo" id="agent_photo" value="<?php echo esc_attr($photo); ?>">
                            <button type="button" class="button afcglide-upload-profile-photo" <?php echo $disabled; ?>>Upload Headshot</button>
                            <p class="description">Upload a professional headshot for your listings.</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="agent_phone">Contact Phone</label></th>
                        <td>
                            <input type="text" 
                                   name="agent_phone" 
                                   id="agent_phone" 
                                   value="<?php echo esc_attr( $phone ); ?>" 
                                   class="regular-text" 
                                   placeholder="e.g. +1 (555) 123-4567"
                                   <?php echo $disabled; ?>>
                            <p class="description">
                                This phone number will be used for WhatsApp contact buttons and public inquiries.
                            </p>
                            <?php if ($is_locked) : ?>
                                <div class="afc-locked-field">
                                    🔒 Locked by Identity Shield
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="agent_license">License Number</label></th>
                        <td>
                            <input type="text" 
                                   name="agent_license" 
                                   id="agent_license" 
                                   value="<?php echo esc_attr( $license ); ?>" 
                                   class="regular-text"
                                   placeholder="e.g. BRE #12345678"
                                   <?php echo $disabled; ?>>
                            <p class="description">
                                Your real estate license number (displayed on listings if configured).
                            </p>
                            <?php if ($is_locked) : ?>
                                <div class="afc-locked-field">
                                    🔒 Locked by Identity Shield
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="agent_office">Office Location</label></th>
                        <td>
                            <input type="text" 
                                   name="agent_office" 
                                   id="agent_office" 
                                   value="<?php echo esc_attr( $office ); ?>" 
                                   class="regular-text"
                                   placeholder="e.g. Beverly Hills Office"
                                   <?php echo $disabled; ?>>
                            <?php if ($is_locked) : ?>
                                <div class="afc-locked-field">
                                    🔒 Locked by Identity Shield
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="agent_specialties">Specialties</label></th>
                        <td>
                            <input type="text" 
                                   name="agent_specialties" 
                                   id="agent_specialties" 
                                   value="<?php echo esc_attr( $specialties ); ?>" 
                                   class="regular-text"
                                   placeholder="e.g. Luxury Estates, Waterfront Properties"
                                   <?php echo $disabled; ?>>
                            <p class="description">
                                Your areas of expertise (comma separated).
                            </p>
                            <?php if ($is_locked) : ?>
                                <div class="afc-locked-field">
                                    🔒 Locked by Identity Shield
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="agent_bio">Professional Bio</label></th>
                        <td>
                            <textarea name="agent_bio" 
                                      id="agent_bio" 
                                      rows="6" 
                                      class="large-text"
                                      placeholder="Tell potential clients about your experience and expertise..."
                                      <?php echo $disabled; ?>><?php echo esc_textarea( $bio ); ?></textarea>
                            <p class="description">
                                This bio may be displayed on your listings and agent profile pages.
                            </p>
                            <?php if ($is_locked) : ?>
                                <div class="afc-locked-field">
                                    🔒 Locked by Identity Shield
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <?php if ($is_locked) : ?>
                <div style="margin-top: 20px; padding: 15px 20px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px;">
                    <p style="margin: 0; color: #92400e; font-weight: 600;">
                        <strong>🛡️ Security Notice:</strong> The Lead Broker has enabled Identity Shield. 
                        All changes to your agent profile are currently restricted. Contact your administrator to request modifications.
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Save agent fields (with Identity Shield check)
     */
    public static function save_agent_fields( $user_id ) {
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return;
        }

        // 🔒 IDENTITY SHIELD CHECK
        if ( get_option('afc_identity_shield', '0') === '1' && ! current_user_can('manage_options') ) {
            // Add an admin notice that will show after redirect
            set_transient( 'afc_profile_locked_' . $user_id, true, 30 );
            return; // Exit early - don't save anything
        }

        // Save all agent fields if not locked
        $fields = [
            'agent_phone',
            'agent_photo', // Added
            'agent_license',
            'agent_office',
            'agent_specialties',
            'agent_bio'
        ];

        foreach ($fields as $field) {
            if ( isset($_POST[$field]) ) {
                $value = sanitize_text_field( $_POST[$field] );
                if ( $field === 'agent_bio' ) {
                    $value = sanitize_textarea_field( $_POST[$field] );
                }
                update_user_meta( $user_id, $field, $value );
            }
        }

        // Success transient
        set_transient( 'afc_profile_saved_' . $user_id, true, 30 );
    }
}