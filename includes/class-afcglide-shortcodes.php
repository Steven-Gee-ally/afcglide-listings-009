<?php
/**
 * Shortcodes for AFCGlide Listings
 *
 * @package AFCGlide_Listings
 */

namespace AFCGlide\Listings;

defined( 'ABSPATH' ) || exit;

class AFCGlide_Shortcodes {

    public static function init() {
        add_shortcode( 'afcglide_listing_grid', array( __CLASS__, 'render_listing_grid' ) );
        add_shortcode( 'afcglide_my_listings', array( __CLASS__, 'render_my_listings' ) );
        add_shortcode( 'afcglide_submit_listing', array( __CLASS__, 'render_submit_form' ) );
    }

    public static function render_submit_form() {
        if ( ! is_user_logged_in() ) {
            $login_url = home_url( '/listing-login/' );
            return '<div style="background:#fff3cd; padding:20px; border-radius:5px;"><p>You must be logged in. <a href="' . esc_url( $login_url ) . '">Login here</a>.</p></div>';
        }

        ob_start();
        echo '<div class="afcglide-submit-form">';
        echo '<h2>Submit a New Listing</h2>';
        echo '<form method="post" enctype="multipart/form-data">';
        wp_nonce_field( 'afcglide_new_listing', 'afcglide_nonce' );
        echo '<p><label>Title *</label><input type="text" name="listing_title" required style="width:100%; padding:10px;"></p>';
        echo '<p><label>Description *</label><textarea name="listing_description" required rows="6" style="width:100%; padding:10px;"></textarea></p>';
        echo '<p><label>Price</label><input type="text" name="listing_price" placeholder="$500,000" style="width:100%; padding:10px;"></p>';
        echo '<p><label>Featured Image</label><input type="file" name="hero_image" accept="image/*"></p>';
        echo '<p><button type="submit" style="background:#0073aa; color:white; padding:12px 24px; border:none; border-radius:3px; cursor:pointer;">Submit Listing</button></p>';
        echo '</form>';
        echo '</div>';
        return ob_get_clean();
    }

    public static function render_my_listings() {
        if ( ! is_user_logged_in() ) {
            return '<p>You must be logged in. <a href="' . esc_url( home_url( '/listing-login/' ) ) . '">Login</a></p>';
        }

        $query = new \WP_Query( array(
            'post_type'      => 'afcglide_listing',
            'author'         => get_current_user_id(),
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'pending', 'draft' )
        ) );

        ob_start();
        echo '<div class="afcglide-my-listings"><h2>My Listings</h2>';
        
        if ( $query->have_posts() ) {
            echo '<table style="width:100%; border-collapse:collapse;">';
            echo '<thead><tr style="background:#f9f9f9;">';
            echo '<th style="padding:10px; text-align:left;">Title</th>';
            echo '<th style="padding:10px; text-align:left;">Status</th>';
            echo '<th style="padding:10px; text-align:left;">Date</th>';
            echo '<th style="padding:10px; text-align:left;">Actions</th>';
            echo '</tr></thead><tbody>';
            
            while ( $query->have_posts() ) {
                $query->the_post();
                echo '<tr style="border-bottom:1px solid #eee;">';
                echo '<td style="padding:10px;">' . esc_html( get_the_title() ) . '</td>';
                echo '<td style="padding:10px;">' . esc_html( get_post_status() ) . '</td>';
                echo '<td style="padding:10px;">' . esc_html( get_the_date() ) . '</td>';
                echo '<td style="padding:10px;"><a href="' . esc_url( get_permalink() ) . '">View</a></td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
        } else {
            echo '<p>No listings yet. <a href="' . esc_url( home_url( '/submit-listing/' ) ) . '">Submit one now</a>!</p>';
        }
        
        wp_reset_postdata();
        echo '</div>';
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
        echo '<div class="afcglide-grid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:20px;">';
        
        while ( $query->have_posts() ) {
            $query->the_post();
            echo '<div class="afcglide-card" style="border:1px solid #eee; border-radius:8px; overflow:hidden;">';
            
            if ( has_post_thumbnail() ) {
                the_post_thumbnail( 'medium', array( 'style' => 'width:100%; height:200px; object-fit:cover;' ) );
            }
            
            echo '<div style="padding:15px;">';
            echo '<h4 style="margin:0 0 10px;">' . esc_html( get_the_title() ) . '</h4>';
            echo '<a href="' . esc_url( get_permalink() ) . '" style="color:#27ae60; font-weight:bold;">View Details →</a>';
            echo '</div>';
            echo '</div>';
        }
        
        wp_reset_postdata();
        echo '</div>';
        return ob_get_clean();
    }
}