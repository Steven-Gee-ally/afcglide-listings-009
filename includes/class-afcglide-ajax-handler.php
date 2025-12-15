<?php
/**
 * AFCGlide AJAX Handler
 * Handles filtering and load more functionality
 *
 * Save as: includes/class-afcglide-ajax-handler.php
 *
 * @package AFCGlide\Listings
 */

namespace AFCGlide\Listings;

if ( ! defined( 'ABSPATH' ) ) exit;

class AFCGlide_Ajax_Handler {

    public function __construct() {
        // AJAX actions for both logged-in and non-logged-in users
        add_action( 'wp_ajax_afcglide_filter_listings', [ $this, 'filter_listings' ] );
        add_action( 'wp_ajax_nopriv_afcglide_filter_listings', [ $this, 'filter_listings' ] );
        
        // Enqueue scripts
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Enqueue public scripts with localized data
     */
    public function enqueue_scripts() {
        // Enqueue public JS
        wp_enqueue_script(
            'afcglide-public',
            AFCG_PLUGIN_URL . 'assets/js/public.js',
            [ 'jquery' ],
            AFCG_VERSION,
            true
        );

        // Localize script with AJAX URL and nonce
        wp_localize_script( 'afcglide-public', 'afcglide_ajax_object', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'afcglide_ajax_nonce' ),
            'strings'  => [
                'loading' => __( 'Loading...', 'afcglide' ),
                'load_more' => __( 'Load More Listings', 'afcglide' ),
                'no_results' => __( 'No listings found.', 'afcglide' ),
                'error' => __( 'Error loading listings. Please try again.', 'afcglide' ),
            ]
        ]);

        // Enqueue GLightbox if not already loaded
        if ( is_singular( 'afcglide_listing' ) ) {
            wp_enqueue_style(
                'glightbox',
                'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css',
                [],
                '3.2.0'
            );
            
            wp_enqueue_script(
                'glightbox',
                'https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js',
                [],
                '3.2.0',
                true
            );
        }
    }

    /**
     * Handle AJAX filter & load more request
     */
    public function filter_listings() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'afcglide_ajax_nonce' ) ) {
            wp_send_json_error( [ 'message' => 'Security check failed.' ] );
        }

        // Get request parameters
        $page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        $filters = isset( $_POST['filters'] ) ? $_POST['filters'] : [];
        $query_vars = isset( $_POST['query_vars'] ) ? $_POST['query_vars'] : [];

        // Build query args
        $args = $this->build_query_args( $page, $filters, $query_vars );

        // Execute query
        $query = new \WP_Query( $args );

        // Generate HTML
        $html = $this->generate_listings_html( $query );

        // Prepare response
        if ( $query->have_posts() ) {
            wp_send_json_success([
                'html' => $html,
                'page' => $page,
                'max_pages' => $query->max_num_pages,
                'found_posts' => $query->found_posts,
            ]);
        } else {
            wp_send_json_success([
                'html' => '<div class="afcglide-no-results"><p>' . __( 'No listings found.', 'afcglide' ) . '</p></div>',
                'page' => $page,
                'max_pages' => 0,
                'found_posts' => 0,
            ]);
        }
    }

    /**
     * Build WP_Query arguments
     */
    private function build_query_args( $page, $filters, $query_vars ) {
        // Base args
        $args = [
            'post_type'      => 'afcglide_listing',
            'post_status'    => 'publish',
            'posts_per_page' => 9, // Default
            'paged'          => $page,
        ];

        // Merge with shortcode query vars if provided
        if ( ! empty( $query_vars ) && is_array( $query_vars ) ) {
            $args = array_merge( $args, $query_vars );
        }

        // Apply filters
        if ( ! empty( $filters ) && is_array( $filters ) ) {
            
            // Location filter (taxonomy)
            if ( ! empty( $filters['location'] ) ) {
                $args['tax_query'][] = [
                    'taxonomy' => 'afcglide_location',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field( $filters['location'] ),
                ];
            }

            // Type filter (taxonomy)
            if ( ! empty( $filters['type'] ) ) {
                $args['tax_query'][] = [
                    'taxonomy' => 'afcglide_type',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field( $filters['type'] ),
                ];
            }

            // Status filter (taxonomy)
            if ( ! empty( $filters['status'] ) ) {
                $args['tax_query'][] = [
                    'taxonomy' => 'afcglide_status',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field( $filters['status'] ),
                ];
            }

            // Price range filter (meta query)
            $meta_query = [];
            
            if ( ! empty( $filters['min_price'] ) || ! empty( $filters['max_price'] ) ) {
                $price_query = [
                    'key'     => '_price',
                    'type'    => 'NUMERIC',
                    'compare' => 'BETWEEN',
                ];

                $min = ! empty( $filters['min_price'] ) ? floatval( $filters['min_price'] ) : 0;
                $max = ! empty( $filters['max_price'] ) ? floatval( $filters['max_price'] ) : PHP_INT_MAX;
                
                $price_query['value'] = [ $min, $max ];
                $meta_query[] = $price_query;
            }

            if ( ! empty( $meta_query ) ) {
                $args['meta_query'] = $meta_query;
            }
        }

        return apply_filters( 'afcglide_ajax_query_args', $args, $filters );
    }

    /**
     * Generate HTML for listings
     */
    private function generate_listings_html( $query ) {
        if ( ! $query->have_posts() ) {
            return '';
        }

        ob_start();

        while ( $query->have_posts() ) {
            $query->the_post();
            
            // Use template if exists, otherwise default card
            $template = locate_template( 'afcglide-templates/card.php' );
            
            if ( $template ) {
                include $template;
            } else {
                $this->render_default_card();
            }
        }

        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Render default listing card
     */
    private function render_default_card() {
        $post_id = get_the_ID();
        $price = get_post_meta( $post_id, '_price', true );
        ?>
        
        <div class="afcglide-card">
            <?php if ( has_post_thumbnail() ): ?>
                <div class="afcglide-card-image">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_post_thumbnail( 'medium' ); ?>
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="afcglide-card-content">
                <h3 class="afcglide-card-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h3>
                
                <?php if ( ! empty( $price ) ): ?>
                    <p class="afcglide-card-price"><?php echo esc_html( $price ); ?></p>
                <?php endif; ?>
                
                <div class="afcglide-card-excerpt">
                    <?php echo wp_trim_words( get_the_content(), 20 ); ?>
                </div>
                
                <a href="<?php the_permalink(); ?>" class="afcglide-card-link">
                    <?php _e( 'View Details', 'afcglide' ); ?> &rarr;
                </a>
            </div>
        </div>
        
        <?php
    }

    /**
     * Static init method for main plugin
     */
    public static function init() {
        new self();
    }
}

// Initialize
new AFCGlide_Ajax_Handler();