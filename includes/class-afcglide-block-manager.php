<?php
/**
 * Manages Gutenberg Blocks (Dynamic Server-Side Rendering).
 *
 * @package AFCGlide_Listings
 */

namespace AFCGlide\Listings;

if ( ! defined( 'ABSPATH' ) ) exit;

class AFCGlide_Block_Manager {

    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_blocks' ] );
    }

    /**
     * Registers the dynamic block.
     */
    public static function register_blocks() {
        
        // We register a simple script to define the block icon and title in the editor
        wp_register_script(
            'afcglide-block-editor',
            false // No file needed, we inject inline JS below
        );

        // Inject the minimal JS needed to show the block in the Inserter
        $js_code = "
            wp.blocks.registerBlockType( 'afcglide/listings-grid', {
                title: 'AFCGlide Listings',
                icon: 'grid-view',
                category: 'widgets',
                attributes: {
                    postsToShow: { type: 'number', default: 6 },
                    showFeatured: { type: 'boolean', default: false }
                },
                edit: function( props ) {
                    return wp.element.createElement(
                        'div', 
                        { className: 'afcglide-editor-preview', style: { padding: '20px', border: '1px dashed #ccc', textAlign: 'center', background: '#f9f9f9' } }, 
                        '🏠 AFCGlide Listings Grid (Preview will appear on frontend)'
                    );
                },
                save: function() {
                    return null; // Rendered in PHP
                }
            });
        ";
        wp_add_inline_script( 'afcglide-block-editor', $js_code );

        // Register the block Type with a PHP render callback
        register_block_type( 'afcglide/listings-grid', [
            'editor_script'   => 'afcglide-block-editor',
            'render_callback' => [ __CLASS__, 'render_block' ],
            'attributes'      => [
                'postsToShow' => [ 'type' => 'number', 'default' => 6 ],
                'showFeatured' => [ 'type' => 'boolean', 'default' => false ],
            ]
        ]);
    }

    /**
     * Renders the block content on the front end.
     * Uses the shortcode system for consistency.
     */
    public static function render_block( $attributes ) {
        
        // Convert block attributes to shortcode format
        $posts_per_page = isset($attributes['postsToShow']) ? $attributes['postsToShow'] : 6;
        
        // Use WordPress do_shortcode to render the listings grid
        return do_shortcode( "[afcglide_listings_grid posts_per_page='{$posts_per_page}']" );
    }
}