<?php
/**
 * AFCGlide Single Listing Template
 * Version 4.0 - With 4-Photo Gallery Slider
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

// DATA HARVESTING
$post_id  = get_the_ID();
$price    = get_post_meta($post_id, '_listing_price', true);
$address  = get_post_meta($post_id, '_listing_address', true);
$hero_id  = get_post_meta($post_id, '_listing_hero_id', true);
$wa_brand_color = get_option('afc_whatsapp_color', '#25D366');

$beds     = get_post_meta($post_id, '_listing_beds', true); 
$baths    = get_post_meta($post_id, '_listing_baths', true); 
$sqft     = get_post_meta($post_id, '_listing_sqft', true);

// GALLERY
$gallery  = get_post_meta($post_id, '_listing_gallery_ids', true) ?: [];

// AMENITIES
$amenities = get_post_meta($post_id, '_listing_amenities', true);

// AGENT DATA
$a_name   = get_post_meta($post_id, '_agent_name_display', true);
$a_phone  = get_post_meta($post_id, '_agent_phone_display', true);
$a_photo  = get_post_meta($post_id, '_agent_photo_id', true);
$a_img    = $a_photo ? wp_get_attachment_url($a_photo) : AFCG_URL . 'assets/images/placeholder-agent.png';

// CLEAN PHONE
$clean_phone = preg_replace('/[^0-9]/', '', $a_phone);

// TOTAL PHOTOS (Hero + Gallery)
$total_photos = 1 + count($gallery);
?>

<div class="afcglide-wrapper">
    
    <!-- VISUAL STAGE -->
    <section class="afc-luxury-visual-stage">
        
        <!-- MAIN HERO VIEW -->
        <div class="afc-hero-main-display">
            <div class="afc-main-image-container">
                <?php if($hero_id): 
                    echo wp_get_attachment_image($hero_id, 'full', false, [
                        'class' => 'afc-hero-image', 
                        'id' => 'main-view',
                        'alt' => get_the_title()
                    ]); 
                else: ?>
                    <div class="afc-hero-placeholder">
                        <span class="afc-placeholder-icon">🏙️</span>
                    </div>
                <?php endif; ?>
                
                <!-- GLASSMORPHISM PRICE BADGE -->
                <div class="afc-price-badge-overlay">
                    <span class="afc-price-amount">
                        <?php echo $price ? '$' . number_format($price) : 'Contact for Price'; ?>
                    </span>
                </div>

                <!-- PHOTO COUNTER -->
                <div class="afc-photo-counter">
                    <span class="afc-camera-icon">📷</span> 
                    <span id="photo-index">1</span> / <?php echo $total_photos; ?>
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
                         onclick="afcUpdateMainView(this, '<?php echo wp_get_attachment_image_url($hero_id, 'full'); ?>', 1)">
                        <?php echo wp_get_attachment_image($hero_id, 'medium', false, ['alt' => get_the_title()]); ?>
                    </div>

                    <!-- GALLERY THUMBNAILS -->
                    <?php foreach($gallery as $index => $id): 
                        $full_url = wp_get_attachment_image_url($id, 'full');
                    ?>
                        <div class="afc-strip-item" 
                             onclick="afcUpdateMainView(this, '<?php echo esc_url($full_url); ?>', <?php echo $index + 2; ?>)">
                            <?php echo wp_get_attachment_image($id, 'medium', false, ['alt' => get_the_title()]); ?>
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

    <!-- CONTENT GRID -->
    <div class="afc-listing-grid">
        
        <!-- MAIN CONTENT -->
        <main class="afc-main-content">
            
            <!-- TITLE & ADDRESS -->
            <div class="afc-title-section">
                <h1 class="afc-property-title"><?php the_title(); ?></h1>
                <p class="afc-property-address">📍 <?php echo esc_html($address); ?></p>
            </div>

            <!-- SPECS BAR -->
            <div class="afcglide-specs-bar">
                <div class="afcglide-spec-item">
                    <label>Bedrooms</label>
                    <strong><?php echo esc_html($beds ?: '—'); ?></strong>
                </div>
                <div class="afcglide-spec-item">
                    <label>Bathrooms</label>
                    <strong><?php echo esc_html($baths ?: '—'); ?></strong>
                </div>
                <div class="afcglide-spec-item">
                    <label>Square Feet</label>
                    <strong><?php echo $sqft ? number_format($sqft) : '—'; ?></strong>
                </div>
            </div>

            <!-- DESCRIPTION -->
            <div class="afc-description-section">
                <h2 class="afc-section-heading">Property Narrative</h2>
                <div class="afc-description-content">
                    <?php the_content(); ?>
                </div>
            </div>

            <!-- AMENITIES -->
            <?php if ( ! empty( $amenities ) && is_array( $amenities ) ) : ?>
            <div class="afc-amenities-section">
                <h2 class="afc-section-heading">Premium Amenities</h2>
                <div class="afc-amenities-grid">
                    <?php 
                    $amenity_icons = [
                        'Gourmet Kitchen' => '🍳', 'Infinity Pool' => '🌊', 'Ocean View' => '🌅', 
                        'Wine Cellar' => '🍷', 'Private Gym' => '🏋️', 'Smart Home Tech' => '📱', 
                        'Outdoor Cinema' => '🎬', 'Helipad Access' => '🚁', 'Gated Community' => '🏰', 
                        'Guest House' => '🏠', 'Solar Power' => '☀️', 'Beach Front' => '🏖️',
                        'Spa / Sauna' => '🧖', '3+ Car Garage' => '🚗', 'Luxury Fire Pit' => '🔥', 
                        'Concierge Service' => '🛎️', 'Walk-in Closet' => '👗', 'High Ceilings' => '⤴️', 
                        'Staff Quarters' => '👨‍💼', 'Backup Generator' => '⚡'
                    ];
                    
                    foreach ( $amenities as $amenity ) : 
                        $icon = isset($amenity_icons[$amenity]) ? $amenity_icons[$amenity] : '💎';
                    ?>
                        <div class="afc-amenity-pill">
                            <span class="afc-icon-check"><?php echo $icon; ?></span>
                            <?php echo esc_html( $amenity ); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </main>

        <!-- SIDEBAR -->
        <aside class="afc-sidebar-modern">
            
            <!-- AGENT CARD -->
            <div class="afcglide-agent-card">
                <div class="afc-agent-photo-wrap">
                    <img src="<?php echo esc_url($a_img); ?>" alt="<?php echo esc_attr($a_name); ?>">
                </div>
                
                <h3 class="afc-agent-name"><?php echo esc_html($a_name); ?></h3>
                <p class="afc-agent-title">Listing Specialist</p>
                
                <div class="afc-agent-actions">
                    <?php if ($clean_phone): ?>
                    <a href="tel:<?php echo $clean_phone; ?>" class="afc-btn-primary">
                        📞 Call Agent
                    </a>
                    <a href="https://wa.me/<?php echo $clean_phone; ?>" class="afc-btn-primary" style="background: #25D366;">
                        💬 WhatsApp
                    </a>
                    <?php endif; ?>
                </div>
            </div>

        </aside>

    </div>

</div>

<?php 
// FLOATING WHATSAPP BUTTON
$show_wa = get_post_meta(get_the_ID(), '_show_floating_whatsapp', true);

if ( $show_wa === '1' && !empty($clean_phone) ) : 
?>
<style>
    .afc-whatsapp-float {
        background-color: <?php echo esc_attr($wa_brand_color); ?> !important;
    }
    @keyframes afc-pulse {
        0% { box-shadow: 0 0 0 0 <?php echo esc_attr($wa_brand_color); ?>b3; }
        70% { box-shadow: 0 0 0 15px <?php echo esc_attr($wa_brand_color); ?>00; }
        100% { box-shadow: 0 0 0 0 <?php echo esc_attr($wa_brand_color); ?>00; }
    }
</style>

<a href="https://wa.me/<?php echo $clean_phone; ?>" class="afc-whatsapp-float" target="_blank" rel="nofollow">
    <svg viewBox="0 0 32 32" class="afc-wa-icon">
        <path d="M16 0c-8.837 0-16 7.163-16 16 0 2.825.737 5.588 2.137 8.137l-2.137 7.863 8.1-.2.1.2c2.487 1.463 5.112 2.112 7.9 2.112 8.837 0 16-7.163 16-16s-7.163-16-16-16zm8.287 21.825c-.337.95-1.712 1.838-2.737 2.05-.688.138-1.588.25-4.6-1.013-3.862-1.612-6.362-5.538-6.55-5.8-.188-.262-1.525-2.025-1.525-3.862 0-1.838.963-2.738 1.3-3.113.337-.375.75-.463 1-.463s.5 0 .712.013c.225.013.525-.088.825.638.3.713 1.013 2.475 1.1 2.663.088.188.15.413.025.663-.125.263-.188.425-.375.65-.188.225-.412.513-.587.688-.2.2-.412.412-.175.812.238.4.1.863 2.087 2.625 1.637 1.45 3.012 1.9 3.437 2.113.425.213.675.175.925-.113.25-.288 1.075-1.25 1.362-1.688.3-.425.588-.363.988-.212.4.15 2.525 1.188 2.962 1.4.438.213.738.313.838.488.1.175.1.988-.237 1.938z" fill="currentColor"/>
    </svg>
    <span class="afc-wa-tooltip">Chat with Agent</span>
</a>
<?php endif; ?>

<script>
// ===== AFCGlide Gallery Slider v4.0 =====
(function() {
    'use strict';
    
    const filmstrip = document.getElementById('afcFilmstrip');
    if (!filmstrip) return; // Exit if no gallery
    
    const items = filmstrip.querySelectorAll('.afc-strip-item');
    const itemsPerPage = 4;
    const totalPages = Math.ceil(items.length / itemsPerPage);
    let currentPage = 0;

    // INITIALIZE
    function init() {
        createPageIndicators();
        updateButtons();
        updatePageIndicators();
    }

    // UPDATE MAIN VIEW
    window.afcUpdateMainView = function(element, imageUrl, index) {
        const mainImage = document.getElementById('main-view');
        const photoIndex = document.getElementById('photo-index');
        
        if (!mainImage || !photoIndex) return;
        
        // Add loading state
        mainImage.style.opacity = '0';
        
        // Remove active from all
        items.forEach(item => item.classList.remove('active'));
        
        // Add active to clicked
        element.classList.add('active');
        
        // Update image with fade
        setTimeout(function() {
            mainImage.src = imageUrl;
            photoIndex.textContent = index;
            mainImage.style.opacity = '1';
        }, 200);
    };

    // SCROLL GALLERY
    window.afcScrollGallery = function(direction) {
        const newPage = currentPage + direction;
        
        if (newPage < 0 || newPage >= totalPages) return;
        
        currentPage = newPage;
        const translateX = -(currentPage * 100);
        
        filmstrip.style.transform = 'translateX(' + translateX + '%)';
        
        updateButtons();
        updatePageIndicators();
    };

    // UPDATE NAV BUTTONS
    function updateButtons() {
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        
        if (prevBtn) prevBtn.disabled = currentPage === 0;
        if (nextBtn) nextBtn.disabled = currentPage === totalPages - 1;
    }

    // CREATE PAGE INDICATORS
    function createPageIndicators() {
        const container = document.getElementById('pageIndicators');
        if (!container) return;
        
        container.innerHTML = '';
        
        for (let i = 0; i < totalPages; i++) {
            const dot = document.createElement('div');
            dot.className = 'afc-page-dot';
            dot.onclick = function() { goToPage(i); };
            container.appendChild(dot);
        }
    }

    // UPDATE PAGE INDICATORS
    function updatePageIndicators() {
        const dots = document.querySelectorAll('.afc-page-dot');
        dots.forEach(function(dot, index) {
            if (index === currentPage) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }

    // GO TO PAGE
    function goToPage(pageIndex) {
        currentPage = pageIndex;
        const translateX = -(currentPage * 100);
        filmstrip.style.transform = 'translateX(' + translateX + '%)';
        updateButtons();
        updatePageIndicators();
    }

    // KEYBOARD NAVIGATION
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') window.afcScrollGallery(-1);
        if (e.key === 'ArrowRight') window.afcScrollGallery(1);
    });

    // START
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();
</script>

<?php get_footer(); ?>