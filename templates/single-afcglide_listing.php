<?php
/**
 * AFCGlide - Single Listing Template (Stabilized v3.2)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

global $post;
$post_id = get_the_ID();

// 1. Fetch Data using standard WordPress calls (Safe)
$price  = get_post_meta( $post_id, '_price', true );
$beds   = get_post_meta( $post_id, '_beds', true );
$baths  = get_post_meta( $post_id, '_baths', true );
$sqft   = get_post_meta( $post_id, '_sqft', true );
$status = get_post_meta( $post_id, '_listing_status', true ) ?: 'for-sale';

// 2. Gallery Logic
$gallery_ids = get_post_meta( $post_id, '_listing_gallery_ids', true );
if ( ! is_array( $gallery_ids ) ) {
    $gallery_ids = []; 
}
$gallery_ids = is_array( $gallery ) ? $gallery : ( json_decode( $gallery, true ) ?: [] );
?>

<div class="afcglide-single-root afcglide-container">
    <main class="afcglide-single-main">

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            
            <header class="afcglide-single-header">
                <h1 class="afcglide-single-title"><?php the_title(); ?></h1>
                <div class="afcglide-header-meta">
                    <span class="afcglide-single-price">$<?php echo number_format((float)$price); ?></span>
                    <span class="afc-badge"><?php echo esc_html(str_replace('-', ' ', $status)); ?></span>
                </div>
            </header>

            <section class="afcglide-hero-block" style="display: flex; gap: 10px; margin-bottom: 20px;">
                <div class="hero-main" style="flex: 2;">
                    <?php if ( has_post_thumbnail() ) the_post_thumbnail('large', ['style' => 'width:100%; height:auto; border-radius:8px;']); ?>
                </div>
            </section>

            <section class="afcglide-details" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; background: #f9f9f9; padding: 20px; border-radius: 8px;">
                <div><strong><?php echo esc_html($beds ?: '--'); ?></strong> Beds</div>
                <div><strong><?php echo esc_html($baths ?: '--'); ?></strong> Baths</div>
                <div><strong><?php echo esc_html($sqft ?: '--'); ?></strong> Sq Ft</div>
            </section>

            <div class="afcglide-description" style="margin-top: 30px;">
                <h3><?php _e('About this Property', 'afcglide'); ?></h3>
                <?php the_content(); ?>
            </div>

        </article>

    <?php endwhile; endif; ?>

    </main>
</div>

<?php get_footer(); ?>