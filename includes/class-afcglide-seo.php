<?php
namespace AFCGlide\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AFCGlide SEO & Schema Engine
 * Version 1.0.0 - Enterprise Grade JSON-LD
 */
class AFCGlide_SEO {

    public static function init() {
        add_action( 'wp_head', [ __CLASS__, 'inject_json_ld' ] );
    }

    /**
     * Inject Schema.org JSON-LD into head for Real Estate Listings
     */
    public static function inject_json_ld() {
        if ( ! is_singular( Constants::POST_TYPE ) ) return;

        global $post;
        $post_id = $post->ID;

        // Data Harvesting
        $price    = get_post_meta( $post_id, Constants::META_PRICE, true );
        $address  = get_post_meta( $post_id, Constants::META_ADDRESS, true );
        $beds     = get_post_meta( $post_id, Constants::META_BEDS, true );
        $baths    = get_post_meta( $post_id, Constants::META_BATHS, true );
        $sqft     = get_post_meta( $post_id, Constants::META_SQFT, true );
        $intro    = get_post_meta( $post_id, Constants::META_INTRO, true );
        $image    = get_the_post_thumbnail_url( $post_id, 'full' );

        $schema = [
            "@context" => "https://schema.org/",
            "@type"    => "RealEstateListing",
            "name"     => get_the_title(),
            "description" => $intro ?: get_the_excerpt(),
            "url"      => get_permalink(),
            "image"    => $image,
            "address"  => [
                "@type"           => "PostalAddress",
                "streetAddress"   => $address,
                "addressLocality" => "Costa Rica" // Central America Context
            ]
        ];

        // Add Price if numeric
        if ( is_numeric($price) ) {
            $schema['offers'] = [
                "@type"         => "Offer",
                "price"         => $price,
                "priceCurrency" => "USD",
                "availability"  => "https://schema.org/InStock"
            ];
        }

        // Professional Specs
        if ($beds) $schema['numberOfBedrooms'] = $beds;
        if ($baths) $schema['numberOfBathroomsTotal'] = $baths;
        if ($sqft) {
            $schema['floorSize'] = [
                "@type" => "QuantitativeValue",
                "value" => preg_replace('/[^0-9.]/', '', $sqft),
                "unitCode" => "FTK" // sqft
            ];
        }

        echo "\n<!-- AFCGlide Elite SEO Engine -->\n";
        echo '<script type="application/ld+json">' . json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>';
        echo "\n";
    }
}
