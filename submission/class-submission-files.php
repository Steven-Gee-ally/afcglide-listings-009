<?php
/**
 * Submission Files
 * Handles file uploads for listings (hero image and gallery)
 *
 * @package AFCGlide\Listings\Submission
 */

namespace AFCGlide\Listings\Submission;

use AFCGlide\Listings\Helpers\Upload_Helper;
use AFCGlide\Listings\Helpers\Validator;

if ( ! defined( 'ABSPATH' ) ) exit;

class Submission_Files {

    /**
     * Initialize file upload handlers
     */
    public function __construct() {
        add_action( 'afcglide_after_listing_created', [ $this, 'handle_post_files' ], 10, 2 );
    }

    /**
     * Handle file uploads after listing is created
     */
    public function handle_post_files( $post_id, $data ) {
        // Handle hero image
        if ( Validator::file_upload( 'hero_image' ) ) {
            $hero_result = $this->upload_hero_image( $post_id );
            
            if ( is_wp_error( $hero_result ) ) {
                // Log error but don't fail the entire submission
                error_log( 'AFCGlide Hero Image Upload Error: ' . $hero_result->get_error_message() );
            }
        }

        // Handle gallery images
        if ( Validator::multiple_files( 'gallery_images' ) ) {
            $gallery_result = $this->upload_gallery_images( $post_id );
            
            if ( is_wp_error( $gallery_result ) ) {
                error_log( 'AFCGlide Gallery Upload Error: ' . $gallery_result->get_error_message() );
            }
        }
    }

    /**
     * Upload and set hero image as featured image
     */
    public function upload_hero_image( $post_id ) {
        $attachment_id = Upload_Helper::upload_single( 'hero_image', $post_id );

        if ( is_wp_error( $attachment_id ) ) {
            return $attachment_id;
        }

        // Set as featured image
        Upload_Helper::set_featured_image( $attachment_id, $post_id );

        // Save to meta as well (for compatibility)
        update_post_meta( $post_id, '_hero_image_id', $attachment_id );

        return $attachment_id;
    }

    /**
     * Upload gallery images
     */
    public function upload_gallery_images( $post_id ) {
        $attachment_ids = Upload_Helper::upload_multiple( 'gallery_images', $post_id );

        if ( empty( $attachment_ids ) ) {
            return new \WP_Error( 'no_gallery_images', __( 'No gallery images uploaded.', 'afcglide' ) );
        }

        // Save gallery to post meta
        Upload_Helper::save_gallery( $attachment_ids, $post_id, '_gallery_images' );

        return $attachment_ids;
    }

    /**
     * Get hero image for a listing
     */
    public static function get_hero_image( $post_id, $size = 'large' ) {
        // Try featured image first
        if ( has_post_thumbnail( $post_id ) ) {
            return get_the_post_thumbnail_url( $post_id, $size );
        }

        // Fallback to meta
        $hero_id = get_post_meta( $post_id, '_hero_image_id', true );
        if ( $hero_id ) {
            return Upload_Helper::get_attachment_url( $hero_id, $size );
        }

        return false;
    }

    /**
     * Get gallery images for a listing
     */
    public static function get_gallery_images( $post_id, $size = 'medium' ) {
        $gallery_ids = Upload_Helper::get_gallery( $post_id, '_gallery_images' );
        $images = [];

        foreach ( $gallery_ids as $attachment_id ) {
            $url = Upload_Helper::get_attachment_url( $attachment_id, $size );
            
            if ( $url ) {
                $images[] = [
                    'id'  => $attachment_id,
                    'url' => $url,
                    'alt' => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
                ];
            }
        }

        return $images;
    }

    /**
     * Delete all images associated with a listing
     */
    public static function delete_listing_images( $post_id ) {
        // Delete featured image
        $thumbnail_id = get_post_thumbnail_id( $post_id );
        if ( $thumbnail_id ) {
            Upload_Helper::delete_attachment( $thumbnail_id );
        }

        // Delete hero image (if different from featured)
        $hero_id = get_post_meta( $post_id, '_hero_image_id', true );
        if ( $hero_id && $hero_id != $thumbnail_id ) {
            Upload_Helper::delete_attachment( $hero_id );
        }

        // Delete gallery images
        $gallery_ids = Upload_Helper::get_gallery( $post_id, '_gallery_images' );
        foreach ( $gallery_ids as $attachment_id ) {
            Upload_Helper::delete_attachment( $attachment_id );
        }
    }

    /**
     * Render hero image HTML
     */
    public static function render_hero_image( $post_id, $size = 'large', $attr = [] ) {
        if ( has_post_thumbnail( $post_id ) ) {
            the_post_thumbnail( $size, $attr );
        } else {
            $hero_id = get_post_meta( $post_id, '_hero_image_id', true );
            if ( $hero_id ) {
                echo Upload_Helper::get_attachment_image( $hero_id, $size, $attr );
            }
        }
    }

    /**
     * Render gallery HTML
     */
    public static function render_gallery( $post_id, $size = 'medium' ) {
        $images = self::get_gallery_images( $post_id, $size );

        if ( empty( $images ) ) {
            return;
        }

        echo '<div class="afcglide-gallery">';
        
        foreach ( $images as $image ) {
            printf(
                '<a href="%s" class="afcglide-gallery-item afcglide-lightbox" data-gallery="listing-%d">
                    <img src="%s" alt="%s" />
                </a>',
                esc_url( $image['url'] ),
                $post_id,
                esc_url( $image['url'] ),
                esc_attr( $image['alt'] )
            );
        }
        
        echo '</div>';
    }

    /**
     * Initialize (called by main plugin)
     */
    public static function init() {
        new self();
    }
}

// DON'T auto-instantiate here - let the main plugin file handle it
// REMOVED: new Submission_Files();