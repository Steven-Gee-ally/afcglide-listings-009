<?php
/**
 * Registers Custom Post Types and Taxonomies.
 *
 * @package AFCGlide_Listings
 */

namespace AFCGlide\Listings;

if ( ! defined( 'ABSPATH' ) ) exit;

class AFCGlide_CPT_Tax {

    public static function init() {
        // Register CPT first, then Taxonomies
        add_action( 'init', [ __CLASS__, 'register_post_type' ], 5 );
        add_action( 'init', [ __CLASS__, 'register_taxonomies' ], 10 );
        
        // Auto-populate amenities
        add_action( 'admin_init', [ __CLASS__, 'populate_default_amenities' ] );
    }

    public static function register_post_type() {
        $labels = [
            'name'               => __( 'Listings', 'afcglide' ),
            'singular_name'      => __( 'Listing', 'afcglide' ),
            'add_new'            => __( 'Add New', 'afcglide' ),
            'add_new_item'       => __( 'Add New Listing', 'afcglide' ),
            'edit_item'          => __( 'Edit Listing', 'afcglide' ),
            'menu_name'          => __( 'Listings', 'afcglide' ),
        ];

        $args = [
            'labels'              => $labels,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true, // This puts "Listings" in the sidebar
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-admin-home',
            'has_archive'         => 'listings',
            'rewrite'             => [ 'slug' => 'listings', 'with_front' => false ],
            'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'author' ],
            'taxonomies'          => [ 'property_type', 'property_status', 'property_location', 'property_amenity' ],
            'show_in_rest'        => true,
        ];

        register_post_type( 'afcglide_listing', $args );
    }

    public static function register_taxonomies() {
    $taxonomies = [
        'property_location' => [ 'name' => 'Locations', 'slug' => 'location' ],
        'property_type'     => [ 'name' => 'Property Types', 'slug' => 'property-type' ],
        'property_status'   => [ 'name' => 'Statuses', 'slug' => 'property-status' ],
        'property_amenity'  => [ 'name' => 'Amenities', 'slug' => 'amenity' ]
    ];

    foreach ( $taxonomies as $slug => $args ) {
        register_taxonomy( $slug, 'afcglide_listing', [
            'labels' => [
                'name'          => $args['name'],
                'singular_name' => rtrim($args['name'], 's'),
                'menu_name'     => $args['name'], // Explicitly set menu name
            ],
            'hierarchical'      => ($slug === 'property_amenity') ? false : true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => true,
            'show_in_rest'      => true, // Essential for Gutenberg
            'show_in_menu'      => true, // Explicitly force into menu
            'query_var'         => true,
            'rewrite'           => [ 'slug' => $args['slug'], 'with_front' => false ],
        ] );
        
        // Final "Handshake" to ensure the link is solid
        register_taxonomy_for_object_type( $slug, 'afcglide_listing' );
    }
}

    public static function populate_default_amenities() {
        if ( ! taxonomy_exists('property_amenity') ) return;

        $amenities = [
            'Infinity Pool', 'Home Gym', 'Outdoor Shower', 'Hot Tub', 
            'Wrap-around Deck', 'Fire Pit', 'Vaulted Ceilings', 
            'Floor-to-Ceiling Windows', 'Gourmet Kitchen', 'Smart Home Tech', 
            'Hardwood Floors', 'Private Balcony', 'Stone Fireplace', 
            'Gear Storage', 'Home Office', 'EV Charging'
        ];

        foreach ( $amenities as $amenity ) {
            if ( ! term_exists( $amenity, 'property_amenity' ) ) {
                wp_insert_term( $amenity, 'property_amenity' );
            }
        }
    }
}