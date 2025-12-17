<?php
/**
 * AFCGlide Listings - Shortcodes
 *
 * Shortcodes included:
 *  - [afcglide_login] - Login form (handled by Submission_Auth)
 *  - [afcglide_register] - Registration form (handled by Submission_Auth)
 *  - [afcglide_submit_listing] - Listing submission form
 *  - [afcglide_my_listings] - User's listings dashboard
 *  - [afcglide_listings_grid] - Display listings grid
 *  - [afcglide_slider] - Display listings slider
 *
 * @package AFCGlide\Listings
 */

namespace AFCGlide\Listings;

use AFCGlide\Listings\Helpers\Message_Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

class AFCGlide_Shortcodes {

    public function __construct() {
        // Note: [afcglide_login] and [afcglide_register] are registered in Submission_Auth class
        add_shortcode( 'afcglide_submit_listing', [ $this, 'render_submission_form' ] );
        add_shortcode( 'afcglide_my_listings', [ $this, 'render_my_listings' ] );
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
        global $post;
        
        if ( ! is_a( $post, 'WP_Post' ) ) {
            return;
        }

        $has_shortcode = false;
        $shortcodes = [ 
            'afcglide_login', 
            'afcglide_register',
            'afcglide_submit_listing', 
            'afcglide_my_listings',
            'afcglide_listings_grid', 
            'afcglide_slider' 
        ];
        
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
     * SUBMISSION FORM (Protected by WP Auth)
     * ========================================
     */
    public function render_submission_form() {
        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            return '<div class="afcglide-notice afcglide-notice-warning">
                <p>You must be logged in to submit a listing. <a href="' . esc_url( home_url( '/listing-login/' ) ) . '">Login here</a> or <a href="' . esc_url( home_url( '/listing-register/' ) ) . '">Register</a>.</p>
            </div>';
        }

        // Display any messages
        $message = Message_Helper::get();

        // Check if just submitted successfully
        $just_submitted = isset( $_GET['listing_submitted'] ) && $_GET['listing_submitted'] === '1';

        ob_start(); ?>

        <div class="afcglide-form-wrapper">
            
            <?php if ( $message ): ?>
                <div class="afcglide-message afcglide-message-<?php echo esc_attr( $message['type'] ); ?>">
                    <?php echo esc_html( $message['text'] ); ?>
                </div>
            <?php endif; ?>

            <?php if ( $just_submitted ): ?>
                <div class="afcglide-message afcglide-message-success">
                    <?php _e( 'Listing submitted successfully! It is awaiting admin approval.', 'afcglide' ); ?>
                </div>
            <?php endif; ?>

            <h2><?php _e( 'Submit a New Listing', 'afcglide' ); ?></h2>

            <form id="afcglide-listing-form" method="post" enctype="multipart/form-data" class="afcglide-submission-form">
                <?php wp_nonce_field( 'afcglide_new_listing', 'afcglide_nonce' ); ?>

                <div class="afcglide-form-group">
                    <label for="listing_title">
                        <?php _e( 'Listing Title', 'afcglide' ); ?> <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="listing_title" 
                        name="listing_title" 
                        required 
                        class="afcglide-input"
                        placeholder="<?php esc_attr_e( 'e.g., Luxury Beachfront Villa', 'afcglide' ); ?>"
                    />
                </div>

                <div class="afcglide-form-group">
                    <label for="listing_description">
                        <?php _e( 'Description', 'afcglide' ); ?> <span class="required">*</span>
                    </label>
                    <textarea 
                        id="listing_description" 
                        name="listing_description" 
                        required 
                        class="afcglide-textarea"
                        rows="8"
                        placeholder="<?php esc_attr_e( 'Describe the property in detail...', 'afcglide' ); ?>"
                    ></textarea>
                </div>

                <div class="afcglide-form-group">
                    <label for="listing_price"><?php _e( 'Price', 'afcglide' ); ?></label>
                    <input 
                        type="text" 
                        id="listing_price" 
                        name="listing_price" 
                        class="afcglide-input"
                        placeholder="<?php esc_attr_e( 'e.g., $500,000', 'afcglide' ); ?>"
                    />
                    <small class="afcglide-help-text">
                        <?php _e( 'Enter the listing price or leave blank if not applicable', 'afcglide' ); ?>
                    </small>
                </div>

                <div class="afcglide-form-group">
                    <label for="hero_image"><?php _e( 'Featured Image', 'afcglide' ); ?></label>
                    <input 
                        type="file" 
                        id="hero_image" 
                        name="hero_image" 
                        accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                        class="afcglide-file-input"
                    />
                    <small class="afcglide-help-text">
                        <?php _e( 'Maximum file size: 5MB. Formats: JPEG, PNG, GIF, WebP', 'afcglide' ); ?>
                    </small>
                </div>

                <div class="afcglide-form-group">
                    <label for="gallery_images"><?php _e( 'Gallery Images', 'afcglide' ); ?></label>
                    <input 
                        type="file" 
                        id="gallery_images" 
                        name="gallery_images[]" 
                        accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                        multiple 
                        class="afcglide-file-input"
                    />
                    <small class="afcglide-help-text">
                        <?php _e( 'Select multiple images for the gallery (optional)', 'afcglide' ); ?>
                    </small>
                </div>

                <!-- Agent Information Section -->
                <div class="afcglide-form-section">
                    <h3><?php _e( 'Agent Information (Optional)', 'afcglide' ); ?></h3>
                    
                    <div id="agent-fields-container">
                        <div class="afcglide-agent-group" data-agent-index="1">
                            <div class="afcglide-form-group">
                                <label for="agent_name_1"><?php _e( 'Agent Name', 'afcglide' ); ?></label>
                                <input 
                                    type="text" 
                                    id="agent_name_1" 
                                    name="agent_name_1" 
                                    class="afcglide-input"
                                />
                            </div>
                            <div class="afcglide-form-group">
                                <label for="agent_email_1"><?php _e( 'Agent Email', 'afcglide' ); ?></label>
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
                        <?php _e( '+ Add Another Agent', 'afcglide' ); ?>
                    </button>
                </div>

                <input type="hidden" name="agent_count" id="agent_count" value="1" />

                <div class="afcglide-form-actions">
                    <button type="submit" class="afcglide-button afcglide-button-primary afcglide-button-large">
                        <?php _e( 'Submit Listing', 'afcglide' ); ?>
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
                            <label for="agent_name_${agentCount}"><?php _e( 'Agent Name', 'afcglide' ); ?></label>
                            <input type="text" id="agent_name_${agentCount}" name="agent_name_${agentCount}" class="afcglide-input" />
                        </div>
                        <div class="afcglide-form-group">
                            <label for="agent_email_${agentCount}"><?php _e( 'Agent Email', 'afcglide' ); ?></label>
                            <input type="email" id="agent_email_${agentCount}" name="agent_email_${agentCount}" class="afcglide-input" />
                        </div>
                        <button type="button" class="remove-agent afcglide-button afcglide-button-danger"><?php _e( 'Remove', 'afcglide' ); ?></button>
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
     * MY LISTINGS DASHBOARD
     * ========================================
     */
    public function render_my_listings() {
        if ( ! is_user_logged_in() ) {
            return '<div class="afcglide-notice afcglide-notice-warning">
                <p>' . __( 'You must be logged in to view your listings.', 'afcglide' ) . ' 
                <a href="' . esc_url( home_url( '/listing-login/' ) ) . '">' . __( 'Login here', 'afcglide' ) . '</a>.</p>
            </div>';
        }

        $current_user_id = get_current_user_id();

        // Query user's listings
        $query = new \WP_Query([
            'post_type'      => 'afcglide_listing',
            'author'         => $current_user_id,
            'posts_per_page' => -1,
            'post_status'    => [ 'publish', 'pending', 'draft' ],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        ob_start(); ?>

        <div class="afcglide-my-listings">
            <div class="afcglide-dashboard-header">
                <h2><?php _e( 'My Listings', 'afcglide' ); ?></h2>
                <a href="<?php echo esc_url( home_url( '/submit-listing/' ) ); ?>" class="afcglide-button afcglide-button-primary">
                    <?php _e( '+ Add New Listing', 'afcglide' ); ?>
                </a>
            </div>

            <?php if ( $query->have_posts() ): ?>
                <div class="afcglide-listings-table">
                    <table>
                        <thead>
                            <tr>
                                <th><?php _e( 'Title', 'afcglide' ); ?></th>
                                <th><?php _e( 'Status', 'afcglide' ); ?></th>
                                <th><?php _e( 'Date', 'afcglide' ); ?></th>
                                <th><?php _e( 'Actions', 'afcglide' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ( $query->have_posts() ): $query->the_post(); ?>
                                <tr>
                                    <td>
                                        <?php if ( has_post_thumbnail() ): ?>
                                            <div class="afcglide-listing-thumb">
                                                <?php the_post_thumbnail( 'thumbnail' ); ?>
                                            </div>
                                        <?php endif; ?>
                                        <strong><?php the_title(); ?></strong>
                                    </td>
                                    <td>
                                        <?php 
                                        $status = get_post_status();
                                        $status_labels = [
                                            'publish' => __( 'Published', 'afcglide' ),
                                            'pending' => __( 'Pending Review', 'afcglide' ),
                                            'draft'   => __( 'Draft', 'afcglide' ),
                                        ];
                                        echo '<span class="afcglide-status afcglide-status-' . esc_attr( $status ) . '">';
                                        echo esc_html( $status_labels[ $status ] ?? $status );
                                        echo '</span>';
                                        ?>
                                    </td>
                                    <td><?php echo get_the_date(); ?></td>
                                    <td>
                                        <a href="<?php the_permalink(); ?>" class="afcglide-button afcglide-button-small">
                                            <?php _e( 'View', 'afcglide' ); ?>
                                        </a>
                                        <?php if ( current_user_can( 'edit_post', get_the_ID() ) ): ?>
                                            <a href="<?php echo get_edit_post_link(); ?>" class="afcglide-button afcglide-button-small">
                                                <?php _e( 'Edit', 'afcglide' ); ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="afcglide-no-listings">
                    <p><?php _e( 'You haven\'t submitted any listings yet.', 'afcglide' ); ?></p>
                    <a href="<?php echo esc_url( home_url( '/submit-listing/' ) ); ?>" class="afcglide-button afcglide-button-primary">
                        <?php _e( 'Submit Your First Listing', 'afcglide' ); ?>
                    </a>
                </div>
            <?php endif; ?>

            <?php wp_reset_postdata(); ?>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * ========================================
     * LISTINGS GRID
     * ========================================
     */
    public function render_listings_grid( $atts ) {
        $atts = shortcode_atts([
            'posts_per_page' => 12,
            'status'         => 'publish',
            'columns'        => 3,
        ], $atts );

        $query = new \WP_Query([
            'post_type'      => 'afcglide_listing',
            'posts_per_page' => intval( $atts['posts_per_page'] ),
            'post_status'    => $atts['status'],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        ob_start();

        echo '<div class="afcglide-grid afcglide-grid-cols-' . esc_attr( $atts['columns'] ) . '">';

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();
                $price = get_post_meta( $post_id, '_price', true );

                echo '<div class="afcglide-card">';
                
                if ( has_post_thumbnail() ) {
                    echo '<div class="afcglide-card-image">';
                    echo '<a href="' . get_permalink() . '">';
                    the_post_thumbnail( 'medium' );
                    echo '</a>';
                    echo '</div>';
                }
                
                echo '<div class="afcglide-card-content">';
                echo '<h3 class="afcglide-card-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
                
                if ( ! empty( $price ) ) {
                    echo '<p class="afcglide-card-price">' . esc_html( $price ) . '</p>';
                }
                
                echo '<div class="afcglide-card-excerpt">' . wp_trim_words( get_the_excerpt(), 20 ) . '</div>';
                echo '<a href="' . get_permalink() . '" class="afcglide-card-link">' . __( 'View Details', 'afcglide' ) . ' →</a>';
                echo '</div>';
                
                echo '</div>';
            }
        } else {
            echo '<p class="afcglide-no-results">' . __( 'No listings found.', 'afcglide' ) . '</p>';
        }

        echo '</div>';
        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * ========================================
     * SLIDER SHORTCODE
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
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'large' ); ?>
                                </a>
                            <?php endif; ?>
                            <div class="afcglide-slide-content">
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <a href="<?php the_permalink(); ?>" class="afcglide-button">
                                    <?php _e( 'View Listing', 'afcglide' ); ?>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p><?php _e( 'No listings available for slider.', 'afcglide' ); ?></p>
            <?php endif; ?>
        </div>

        <?php 
        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * ========================================
     * INIT METHOD
     * ========================================
     */
    public static function init() {
        new self();
    }
}