<?php
/**
 * Shortcodes for AFCGlide Listings
 */

namespace AFCGlide\Listings;

if ( ! defined( 'ABSPATH' ) ) exit;

class AFCGlide_Shortcodes {

    public static function init() {
        add_shortcode( 'afcglide_listing_grid', [ __CLASS__, 'render_listing_grid' ] );
        add_shortcode( 'afcglide_my_listings', [ __CLASS__, 'render_my_listings' ] );
        add_shortcode( 'afcglide_submit_listing', [ __CLASS__, 'render_submit_form' ] );
    }

    public static function render_submit_form() {
        if ( ! is_user_logged_in() ) {
            $login_url = home_url( '/listing-login/' );
            return '<div style="background:#fff3cd; padding:20px; border-radius:5px;"><p>You must be logged in to submit a listing. <a href="' . esc_url( $login_url ) . '">Login here</a>.</p></div>';
        }

        ob_start();
        ?>
        <div class="afcglide-submit-form">
            <h2>Submit a New Listing</h2>
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field( 'afcglide_new_listing', 'afcglide_nonce' ); ?>
                <p><label>Title *</label><input type="text" name="listing_title" required style="width:100%; padding:10px;"></p>
                <p><label>Description *</label><textarea name="listing_description" required rows="6" style="width:100%; padding:10px;"></textarea></p>
                <p><label>Price</label><input type="text" name="listing_price" placeholder="$500,000" style="width:100%; padding:10px;"></p>
                <p><label>Featured Image</label><input type="file" name="hero_image" accept="image/*"></p>
                <p><button type="submit" style="background:#0073aa; color:white; padding:12px 24px; border:none; border-radius:3px; cursor:pointer; font-size:16px;">Submit Listing</button></p>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function render_my_listings() {
        if ( ! is_user_logged_in() ) {
            $login_url = home_url( '/listing-login/' );
            return '<p>You must be logged in to view your listings. <a href="' . esc_url( $login_url ) . '">Login</a></p>';
        }

        $query = new \WP_Query( array(
            'post_type'      => 'afcglide_listing',
            'author'         => get_current_user_id(),
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'pending', 'draft' )
        ) );

        ob_start();
        ?>
        <div class="afcglide-my-listings">
            <h2>My Listings</h2>
            <?php if ( $query->have_posts() ) : ?>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f9f9f9;">
                            <th style="padding:10px; text-align:left;">Title</th>
                            <th style="padding:10px; text-align:left;">Status</th>
                            <th style="padding:10px; text-align:left;">Date</th>
                            <th style="padding:10px; text-align:left;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:10px;"><?php the_title(); ?></td>
                                <td style="padding:10px;"><?php echo esc_html( get_post_status() ); ?></td>
                                <td style="padding:10px;"><?php echo esc_html( get_the_date() ); ?></td>
                                <td style="padding:10px;"><a href="<?php the_permalink(); ?>">View</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>You have not submitted any listings yet. <a href="<?php echo esc_url( home_url( '/submit-listing/' ) ); ?>">Submit one now</a>!</p>
            <?php endif; ?>
            <?php wp_reset_postdata(); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function render_listing_grid( $atts ) {
        $atts = shortcode_atts( array( 'count' => 6 ), $atts );

        $query = new \WP_Query( array(
            'post_type'      => 'afcglide_listing',
            'posts_per_page' => intval( $atts['count'] ),
            'post_status'    => 'publish'
        ) );

        if ( ! $query->have_posts() ) {
            return '<p>No listings found.</p>';
        }

        ob_start();
        ?>
        <div class="afcglide-grid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:20px;">
            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                <div class="afcglide-card" style="border:1px solid #eee; border-radius:8px; overflow:hidden;">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php 
                        $img_atts = array( 'style' => 'width:100%; height:200px; object-fit:cover;' );
                        the_post_thumbnail( 'medium', $img_atts ); 
                        ?>
                    <?php endif; ?>
                    <div style="padding:15px;">
                        <h4 style="margin:0 0 10px;"><?php the_title(); ?></h4>
                        <a href="<?php the_permalink(); ?>" style="color:#27ae60; font-weight:bold;">View Details →</a>
                    </div>
                </div>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

        // Register shortcodes on init (main bootstrap)
        add_action( 'init', function() {
            if ( class_exists( __NAMESPACE__ . '\AFCGlide_Shortcodes' ) ) {
                AFCGlide_Shortcodes::init();
            }
        }, 10 );