<?php
/**
 * AFCGlide Individual Listing Card
 * Vision: High-End Minimalist UI
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$post_id = get_the_ID();

// 1. DATA HARVESTING
$price   = \AFCGlide\Core\Constants::get_meta( $post_id, \AFCGlide\Core\Constants::META_PRICE );
$beds    = \AFCGlide\Core\Constants::get_meta( $post_id, \AFCGlide\Core\Constants::META_BEDS );
$baths   = \AFCGlide\Core\Constants::get_meta( $post_id, \AFCGlide\Core\Constants::META_BATHS );
$sqft    = \AFCGlide\Core\Constants::get_meta( $post_id, \AFCGlide\Core\Constants::META_SQFT );

// 2. FORMATTING
$display_price = ( ! empty($price) ) ? '$' . number_format( (float)$price ) : 'Contact for Price';
?>

<div class="afcglide-card">
    <div class="afcglide-card-image">
        <a href="<?php echo esc_url(get_permalink()); ?>">
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'large', ['alt' => esc_attr(get_the_title())] ); ?>
            <?php else : ?>
                <img src="<?php echo esc_url( AFCG_URL . 'assets/images/placeholder-listings.svg' ); ?>" alt="Placeholder">
            <?php endif; ?>
        </a>
        <div class="afcglide-price-tag">
            <?php echo esc_html( $display_price ); ?>
        </div>
    </div>

    <div class="afcglide-card-content">
        <?php 
        $current_lang = afcglide_get_current_lang();
        $title_es = get_post_meta($post_id, '_listing_intro_text_es', true);
        $display_title = ($current_lang === 'es' && !empty($title_es)) ? $title_es : get_the_title();
        ?>
        <h3><a href="<?php echo esc_url(add_query_arg('lang', $current_lang, get_permalink())); ?>"><?php echo esc_html($display_title); ?></a></h3>
        
        <div class="afcglide-meta-specs">
            <div class="afcglide-spec-item">
                <span>🛏️</span> <strong><?php echo esc_html( $beds ?: '0' ); ?></strong>
                <small><?php echo $current_lang === 'es' ? 'Hab.' : 'Beds'; ?></small>
            </div>
            <div class="afcglide-spec-item">
                <span>🛁</span> <strong><?php echo esc_html( $baths ?: '0' ); ?></strong>
                <small><?php echo $current_lang === 'es' ? 'Baños' : 'Baths'; ?></small>
            </div>
            <div class="afcglide-spec-item">
                <span>📐</span> 
                <?php 
                    $numeric_sqft = preg_replace('/[^0-9.]/', '', $sqft);
                ?>
                <strong><?php echo $numeric_sqft ? number_format( (float)$numeric_sqft ) : '0'; ?></strong> 
                <small><?php echo $current_lang === 'es' ? 'm²' : 'sqft'; ?></small>
            </div>
        </div>
    </div>
</div>