<?php
/**
 * Upload Helper
 * Handles file uploads with validation and error handling
 *
 * @package AFCGlide\Listings\Helpers
 */

namespace AFCGlide\Listings\Helpers;

if ( ! defined( 'ABSPATH' ) ) exit;

class Upload_Helper {

    const MAX_FILE_SIZE = 5242880; // 5MB in bytes
    const ALLOWED_TYPES = [ 'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp' ];

    /**
     * Load WordPress upload functions if not already loaded
     */
    private static function load_wp_functions() {
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }
        if ( ! function_exists( 'media_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }
    }

    /**
     * Upload single file and attach to post
     */
    public static function upload_single( $file_key, $post_id = 0 ) {
        self::load_wp_functions();

        // Validate file exists
        if ( ! Validator::file_upload( $file_key ) ) {
            return new \WP_Error( 'no_file', __( 'No file was uploaded.', 'afcglide' ) );
        }

        // Validate file type
        if ( ! Validator::file_type( $file_key, self::ALLOWED_TYPES ) ) {
            return new \WP_Error( 
                'invalid_type', 
                __( 'Only image files (JPEG, PNG, GIF, WebP) are allowed.', 'afcglide' ) 
            );
        }

        // Validate file size
        if ( ! Validator::file_size( $file_key, 5 ) ) {
            return new \WP_Error( 
                'file_too_large', 
                __( 'Image must be smaller than 5MB.', 'afcglide' ) 
            );
        }

        // Upload the file
        $attachment_id = media_handle_upload( $file_key, $post_id );

        if ( is_wp_error( $attachment_id ) ) {
            return new \WP_Error( 
                'upload_failed', 
                sprintf( 
                    __( 'Image upload failed: %s', 'afcglide' ), 
                    $attachment_id->get_error_message() 
                )
            );
        }

        return $attachment_id;
    }

    /**
     * Upload multiple files (gallery)
     */
    public static function upload_multiple( $file_key, $post_id = 0 ) {
        self::load_wp_functions();

        if ( ! Validator::multiple_files( $file_key ) ) {
            return [];
        }

        $files = $_FILES[ $file_key ];
        $file_count = count( $files['name'] );
        $attachment_ids = [];

        for ( $i = 0; $i < $file_count; $i++ ) {
            // Skip empty files
            if ( empty( $files['name'][$i] ) || $files['error'][$i] === UPLOAD_ERR_NO_FILE ) {
                continue;
            }

            // Create temporary single file entry
            $_FILES['temp_upload_file'] = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];

            $attachment_id = self::upload_single( 'temp_upload_file', $post_id );

            // Store result (even if error, for logging)
            if ( ! is_wp_error( $attachment_id ) ) {
                $attachment_ids[] = $attachment_id;
            }

            unset( $_FILES['temp_upload_file'] );
        }

        return $attachment_ids;
    }

    /**
     * Set uploaded file as featured image
     */
    public static function set_featured_image( $attachment_id, $post_id ) {
        if ( ! $attachment_id || ! $post_id ) {
            return false;
        }

        return set_post_thumbnail( $post_id, $attachment_id );
    }

    /**
     * Save gallery images to post meta
     */
    public static function save_gallery( $attachment_ids, $post_id, $meta_key = '_gallery_images' ) {
        if ( empty( $attachment_ids ) || ! $post_id ) {
            return false;
        }

        return update_post_meta( $post_id, $meta_key, $attachment_ids );
    }

    /**
     * Get gallery images from post meta
     */
    public static function get_gallery( $post_id, $meta_key = '_gallery_images' ) {
        $gallery = get_post_meta( $post_id, $meta_key, true );
        
        if ( ! is_array( $gallery ) ) {
            return [];
        }

        return array_filter( $gallery, 'is_numeric' );
    }

    /**
     * Delete attachment and its files
     */
    public static function delete_attachment( $attachment_id ) {
        return wp_delete_attachment( $attachment_id, true ) !== false;
    }

    /**
     * Get attachment URL
     */
    public static function get_attachment_url( $attachment_id, $size = 'full' ) {
        $url = wp_get_attachment_image_url( $attachment_id, $size );
        return $url ? $url : false;
    }

    /**
     * Get attachment HTML img tag
     */
    public static function get_attachment_image( $attachment_id, $size = 'medium', $attr = [] ) {
        return wp_get_attachment_image( $attachment_id, $size, false, $attr );
    }

    /**
     * Validate and prepare file for upload
     */
    public static function validate_file( $file_key ) {
        if ( ! Validator::file_upload( $file_key ) ) {
            return new \WP_Error( 'no_file', __( 'No file uploaded.', 'afcglide' ) );
        }

        $file = $_FILES[ $file_key ];

        // Check file type
        if ( ! in_array( $file['type'], self::ALLOWED_TYPES, true ) ) {
            return new \WP_Error( 
                'invalid_type', 
                __( 'Invalid file type. Only images are allowed.', 'afcglide' ) 
            );
        }

        // Check file size
        if ( $file['size'] > self::MAX_FILE_SIZE ) {
            $max_mb = self::MAX_FILE_SIZE / 1024 / 1024;
            return new \WP_Error( 
                'file_too_large', 
                sprintf( __( 'File is too large. Maximum size is %sMB.', 'afcglide' ), $max_mb ) 
            );
        }

        return [
            'name' => Sanitizer::file_name( $file['name'] ),
            'type' => $file['type'],
            'size' => $file['size'],
            'tmp_name' => $file['tmp_name'],
        ];
    }

    /**
     * Get human-readable file size
     */
    public static function format_file_size( $bytes ) {
        $units = [ 'B', 'KB', 'MB', 'GB', 'TB' ];
        
        for ( $i = 0; $bytes > 1024; $i++ ) {
            $bytes /= 1024;
        }

        return round( $bytes, 2 ) . ' ' . $units[ $i ];
    }

    /**
     * Get MIME type from file extension
     */
    public static function get_mime_type( $filename ) {
        $filetype = wp_check_filetype( $filename );
        return $filetype['type'] ? $filetype['type'] : false;
    }
}