<?php
/**
 * AFCGlide Listings - Shortcodes (WordPress Authentication)
 *
 * Shortcodes included:
 *  - [afcglide_agent_login]
 *  - [afcglide_submission_form]
 *  - [afcglide_listings_grid]
 *  - [afcglide_slider]
 *
 * @package AFCGlide\Listings
 */

namespace AFCGlide\Listings;

if ( ! defined( 'ABSPATH' ) ) exit;

class AFCGlide_Shortcodes {

    public function __construct() {
        add_shortcode( 'afcglide_agent_login', [ $this, 'render_agent_login' ] );
        add_shortcode( 'afcglide_submission_form', [ $this, 'render_submission_form' ] );
        add_shortcode( 'afcglide_listings_grid', [ $this, 'render_listings_grid' ] );
        add_shortcode( 'afcglide_slider', [ $this, 'render_slider' ] );
        
        // Enqueue styles for shortcodes
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_shortcode_assets' ] );
    }

    /**
     * ========================================
     * ENQUEUE ASSETS
     * ========================================
     */
    public function enqueue_shortcode_assets() {
        // Only enqueue on pages that use our shortcodes
        global $post;
        
        if ( ! is_a( $post, 'WP_Post' ) ) {
            return;
        }

        $has_shortcode = false;
        $shortcodes = [ 'afcglide_agent_login', 'afcglide_submission_form', 'afcglide_listings_grid', 'afcglide_slider' ];
        
        foreach ( $shortcodes as $shortcode ) {
            if ( has_shortcode( $post->post_content, $shortcode ) ) {
                $has_shortcode = true;
                break;
            }
        }

        if ( $has_shortcode ) {
            wp_enqueue_style( 
                'afcglide-shortcodes', 
                AFCG_PLUGIN_URL . 'assets/css/shortcodes.css', 
                [], 
                AFCG_VERSION 
            );
        }
    }

    /**
     * ========================================
     * 1. AGENT LOGIN SHORTCODE
     * ========================================
     */
    public function render_agent_login() {
        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            
            ob_start(); ?>
            
            <div class="afcglide-login-status">
                <p>Welcome, <strong><?php echo esc_html( $current_user->display_name ); ?></strong>!</p>
                <p>
                    <a href="<?php echo esc_url( home_url( '/submit-listing/' ) ); ?>" class="afcglide-button">
                        Submit a Listing
                    </a>
                    <a href="<?php echo esc_url( add_query_arg( 'afcglide_logout', '1' ) ); ?>" class="afcglide-button afcglide-button-secondary">
                        Logout
                    </a>
                </p>
            </div>
            
            <?php
            return ob_get_clean();
        }

        // Display any messages
        $message = AFCGlide_Submission_Handler::get_message();

        ob_start(); ?>

        <div class="afcglide-login-wrapper">
            
            <?php if ( $message ): ?>
                <div class="afcglide-message afcglide-message-<?php echo esc_attr( $message['type'] ); ?>">
                    <?php echo esc_html( $message['text'] ); ?>
                </div>
            <?php endif; ?>

            <form method="post" class="afcglide-agent-login-form">
                <?php wp_nonce_field( 'afcglide_agent_login_action', 'afcglide_agent_login_nonce' ); ?>

                <div class="afcglide-form-group">
                    <label for="afc_email">Email Address</label>
                    <input 
                        type="email" 
                        id="afc_email" 
                        name="afc_email" 
                        required 
                        class="afcglide-input"
                        placeholder="your@email.com"
                    />
                </div>

                <div class="afcglide-form-group">
                    <label for="afc_password">Password</label>
                    <input 
                        type="password" 
                        id="afc_password" 
                        name="afc_password" 
                        required 
                        class="afcglide-input"
                        placeholder="Enter your password"
                    />
                </div>

                <div class="afcglide-form-group">
                    <button type="submit" class="afcglide-button afcglide-button-primary">
                        Login
                    </button>
                </div>

                <div class="afcglide-form-footer">
                    <p>
                        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">Forgot your password?</a>
                    </p>
                    <p>
                        Don't have an account? <a href="<?php echo esc_url( wp_registration_url() ); ?>">Register here</a>
                    </p>
                </div>
            </form>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * ========================================
     * 2. SUBMISSION FORM (Protected by WP Auth)
     * ========================================
     */
/**
 * Update this function in your class-afcglide-shortcodes.php file
 * Find the render_submission_form() function and replace it with this
 */

public function render_submission_form() {
    if ( ! is_user_logged_in() ) {
        return '<div class="afcglide-notice afcglide-notice-warning">
            <p>You must be logged in to submit a listing. <a href="' . esc_url( home_url( '/agent-login/' ) ) . '">Login here</a>.</p>
        </div>';
    }

    // Display any messages
    $message = \AFCGlide\Listings\Helpers\Message_Helper::get();

    // Check if just submitted successfully
    $just_submitted = isset( $_GET['listing_submitted'] ) && $_GET['listing_submitted'] === '1';

    // Get company branding from settings
    $logo_url = \AFCGlide\Listings\AFCGlide_Settings::get_company_logo( 'medium' );
    $company_name = \AFCGlide\Listings\AFCGlide_Settings::get_company_name();

    ob_start(); ?>

    <div class="afcglide-form-wrapper">
        
        <?php 
        // Display Company Branding
        if ( $logo_url || $company_name ): 
        ?>
            <div class="afcglide-form-branding" style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #eee;">
                <?php if ( $logo_url ): ?>
                    <img src="<?php echo esc_url( $logo_url ); ?>" 
                         alt="<?php echo esc_attr( $company_name ); ?>" 
                         class="afcglide-company-logo"
                         style="max-width: 300px; height: auto; margin: 0 auto 15px; display: block;">
                <?php endif; ?>
                
                <?php if ( $company_name ): ?>
                    <h2 class="afcglide-company-name" style="margin: 0; font-size: 24px; color: #333;">
                        <?php echo esc_html( $company_name ); ?>
                    </h2>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if ( $message ): ?>
            <div class="afcglide-message afcglide-message-<?php echo esc_attr( $message['type'] ); ?>">
                <?php echo esc_html( $message['text'] ); ?>
            </div>
        <?php endif; ?>

        <?php if ( $just_submitted ): ?>
            <div class="afcglide-message afcglide-message-success">
                Listing submitted successfully! It is awaiting admin approval.
            </div>
        <?php endif; ?>

        <h2>Submit a New Listing</h2>

        <form id="afcglide-listing-form" method="post" enctype="multipart/form-data" class="afcglide-submission-form">
            <?php wp_nonce_field( 'afcglide_new_listing', 'afcglide_nonce' ); ?>

            <div class="afcglide-form-group">
                <label for="listing_title">Listing Title <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="listing_title" 
                    name="listing_title" 
                    required 
                    class="afcglide-input"
                    placeholder="e.g., Luxury Beachfront Villa"
                />
            </div>

            <div class="afcglide-form-group">
                <label for="listing_description">Description <span class="required">*</span></label>
                <textarea 
                    id="listing_description" 
                    name="listing_description" 
                    required 
                    class="afcglide-textarea"
                    rows="8"
                    placeholder="Describe the property in detail..."
                ></textarea>
            </div>

            <div class="afcglide-form-group">
                <label for="listing_price">Price</label>
                <input 
                    type="text" 
                    id="listing_price" 
                    name="listing_price" 
                    class="afcglide-input"
                    placeholder="e.g., $500,000"
                />
                <small class="afcglide-help-text">Enter the listing price or leave blank if not applicable</small>
            </div>

            <div class="afcglide-form-group">
                <label for="hero_image">Featured Image</label>
                <input 
                    type="file" 
                    id="hero_image" 
                    name="hero_image" 
                    accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                    class="afcglide-file-input"
                />
                <small class="afcglide-help-text">Maximum file size: 5MB. Formats: JPEG, PNG, GIF, WebP</small>
            </div>

            <div class="afcglide-form-group">
                <label for="gallery_images">Gallery Images</label>
                <input 
                    type="file" 
                    id="gallery_images" 
                    name="gallery_images[]" 
                    accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                    multiple 
                    class="afcglide-file-input"
                />
                <small class="afcglide-help-text">Select multiple images for the gallery (optional)</small>
            </div>

            <!-- Optional: Agent details section -->
            <div class="afcglide-form-section">
                <h3>Agent Information (Optional)</h3>
                
                <div id="agent-fields-container">
                    <div class="afcglide-agent-group" data-agent-index="1">
                        <div class="afcglide-form-group">
                            <label for="agent_name_1">Agent Name</label>
                            <input 
                                type="text" 
                                id="agent_name_1" 
                                name="agent_name_1" 
                                class="afcglide-input"
                            />
                        </div>
                        <div class="afcglide-form-group">
                            <label for="agent_email_1">Agent Email</label>
                            <input 
                                type="email" 
                                id="agent_email_1" 
                                name="agent_email_1" 
                                class="afcglide-input"
                            />
                        </div>
                    </div>
                </div>
                
                <button type="button" id="add-agent-btn" class="afcglide-button afcglide-button-secondary">
                    + Add Another Agent
                </button>
            </div>

            <input type="hidden" name="agent_count" id="agent_count" value="1" />

            <div class="afcglide-form-actions">
                <button type="submit" class="afcglide-button afcglide-button-primary afcglide-button-large">
                    Submit Listing
                </button>
            </div>
        </form>
    </div>

    <script>
    (function() {
        let agentCount = 1;
        const addAgentBtn = document.getElementById('add-agent-btn');
        const container = document.getElementById('agent-fields-container');
        const countInput = document.getElementById('agent_count');

        if (addAgentBtn) {
            addAgentBtn.addEventListener('click', function() {
                agentCount++;
                
                const newGroup = document.createElement('div');
                newGroup.className = 'afcglide-agent-group';
                newGroup.setAttribute('data-agent-index', agentCount);
                newGroup.innerHTML = `
                    <div class="afcglide-form-group">
                        <label for="agent_name_${agentCount}">Agent Name</label>
                        <input type="text" id="agent_name_${agentCount}" name="agent_name_${agentCount}" class="afcglide-input" />
                    </div>
                    <div class="afcglide-form-group">
                        <label for="agent_email_${agentCount}">Agent Email</label>
                        <input type="email" id="agent_email_${agentCount}" name="agent_email_${agentCount}" class="afcglide-input" />
                    </div>
                    <button type="button" class="remove-agent afcglide-button afcglide-button-danger">Remove</button>
                `;
                
                container.appendChild(newGroup);
                countInput.value = agentCount;
                
                // Add remove handler
                const removeBtn = newGroup.querySelector('.remove-agent');
                removeBtn.addEventListener('click', function() {
                    newGroup.remove();
                });
            });
        }
    })();
    </script>

    <?php
    return ob_get_clean();
}
    /**
     * ========================================
     * 3. LISTINGS GRID
     * ========================================
     */
    public function render_listings_grid( $atts ) {
        $atts = shortcode_atts([
            'posts_per_page' => -1,
            'status' => 'publish', // Can be 'publish', 'pending', 'any'
        ], $atts );

        ob_start();

        $query = new \WP_Query([
            'post_type'      => 'afcglide_listing',
            'posts_per_page' => intval( $atts['posts_per_page'] ),
            'post_status'    => $atts['status'],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        echo '<div class="afcglide-grid">';

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();
                $price = get_post_meta( $post_id, '_price', true );

                echo '<div class="afcglide-card">';
                
                if ( has_post_thumbnail() ) {
                    echo '<div class="afcglide-card-image">';
                    the_post_thumbnail( 'medium' );
                    echo '</div>';
                }
                
                echo '<div class="afcglide-card-content">';
                echo '<h3 class="afcglide-card-title">' . get_the_title() . '</h3>';
                
                if ( ! empty( $price ) ) {
                    echo '<p class="afcglide-card-price">' . esc_html( $price ) . '</p>';
                }
                
                echo '<div class="afcglide-card-excerpt">' . wp_trim_words( get_the_content(), 20 ) . '</div>';
                echo '<a href="' . get_permalink() . '" class="afcglide-card-link">View Details →</a>';
                echo '</div>';
                
                echo '</div>';
            }
        } else {
            echo '<p class="afcglide-no-results">No listings found.</p>';
        }

        echo '</div>';
        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * ========================================
     * 4. SLIDER SHORTCODE
     * ========================================
     */
    public function render_slider( $atts ) {
        $atts = shortcode_atts([
            'count' => 5,
        ], $atts );

        $query = new \WP_Query([
            'post_type'      => 'afcglide_listing',
            'posts_per_page' => intval( $atts['count'] ),
            'post_status'    => 'publish',
        ]);

        ob_start(); ?>

        <div class="afcglide-slider">
            <?php if ( $query->have_posts() ): ?>
                <div class="afcglide-slider-wrapper">
                    <?php while ( $query->have_posts() ): $query->the_post(); ?>
                        <div class="afcglide-slide">
                            <?php if ( has_post_thumbnail() ): ?>
                                <?php the_post_thumbnail( 'large' ); ?>
                            <?php endif; ?>
                            <div class="afcglide-slide-content">
                                <h3><?php the_title(); ?></h3>
                                <a href="<?php the_permalink(); ?>" class="afcglide-button">View Listing</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No listings available for slider.</p>
            <?php endif; ?>
        </div>

        <?php 
        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * ========================================
     * INIT METHOD (for main plugin to call)
     * ========================================
     */
    public static function init() {
        new self();
    }
}

// Initialize if not using the main plugin's init system
if ( ! class_exists( 'AFCGlide\Listings\AFCGlide_Listings_Plugin' ) ) {
    new AFCGlide_Shortcodes();
}