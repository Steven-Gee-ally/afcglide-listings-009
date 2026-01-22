<?php
/**
 * AFCGlide Template Part: Visual Stage (Hero & Filmstrip)
 * Version 4.5 - Enterprise Hardened
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Expects $hero_id, $gallery, $price, $total_photos to be passed or accessible
?>
<section class="afc-luxury-visual-stage">
    
    <!-- MAIN HERO VIEW -->
    <div class="afc-hero-main-display">
        <div class="afc-main-image-container">
            <?php if($hero_id): 
                echo wp_get_attachment_image($hero_id, 'full', false, [
                    'class' => 'afc-hero-image', 
                    'id' => 'main-view',
                    'alt' => esc_attr(get_the_title())
                ]); 
            else: ?>
                <div class="afc-hero-placeholder">
                    <span class="afc-placeholder-icon">🏙️</span>
                </div>
            <?php endif; ?>
            
            <!-- GLASSMORPHISM PRICE BADGE -->
            <div class="afc-price-badge-overlay">
                <span class="afc-price-amount">
                    <?php echo $price ? '$' . number_format((float)$price) : esc_html__('Contact for Price', 'afcglide'); ?>
                </span>
            </div>

            <!-- PHOTO COUNTER -->
            <div class="afc-photo-counter">
                <span class="afc-camera-icon">📷</span> 
                <span id="photo-index">1</span> / <?php echo esc_html($total_photos); ?>
            </div>
        </div>
    </div>

    <!-- FILMSTRIP GALLERY (4 at a time) -->
    <?php if ( ! empty( $gallery ) ) : ?>
    <div class="afc-filmstrip-wrapper">
        <button class="afc-strip-nav prev" id="prevBtn" onclick="afcScrollGallery(-1)">❮</button>
        
        <div class="afc-filmstrip-container">
            <div class="afc-filmstrip-inner" id="afcFilmstrip">
                
                <!-- HERO THUMBNAIL (First Photo) -->
                <div class="afc-strip-item active" 
                     onclick="afcUpdateMainView(this, '<?php echo esc_url(wp_get_attachment_image_url($hero_id, 'full')); ?>', 1)">
                    <?php echo wp_get_attachment_image($hero_id, 'medium', false, ['alt' => esc_attr(get_the_title())]); ?>
                </div>

                <!-- GALLERY THUMBNAILS -->
                <?php foreach($gallery as $index => $id): 
                    $full_url = wp_get_attachment_image_url($id, 'full');
                ?>
                    <div class="afc-strip-item" 
                         onclick="afcUpdateMainView(this, '<?php echo esc_url($full_url); ?>', <?php echo (int)($index + 2); ?>)">
                        <?php echo wp_get_attachment_image($id, 'medium', false, ['alt' => esc_attr(get_the_title())]); ?>
                    </div>
                <?php endforeach; ?>
                
            </div>
        </div>

        <button class="afc-strip-nav next" id="nextBtn" onclick="afcScrollGallery(1)">❯</button>
    </div>

    <!-- PAGE INDICATORS -->
    <div class="afc-page-indicators" id="pageIndicators"></div>
    <?php endif; ?>

</section>
