<?php
/**
 * AFCGlide Single Listing Template
 * Version 4.0 - With 4-Photo Gallery Slider
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

// DATA HARVESTING
$post_id  = get_the_ID();
$price    = \AFCGlide\Core\Constants::get_meta($post_id, \AFCGlide\Core\Constants::META_PRICE);
$address  = \AFCGlide\Core\Constants::get_meta($post_id, \AFCGlide\Core\Constants::META_ADDRESS);
$hero_id  = \AFCGlide\Core\Constants::get_meta($post_id, \AFCGlide\Core\Constants::META_HERO_ID);
$wa_brand_color = get_option('afc_whatsapp_color', '#25D366');

$beds     = \AFCGlide\Core\Constants::get_meta($post_id, \AFCGlide\Core\Constants::META_BEDS); 
$baths    = \AFCGlide\Core\Constants::get_meta($post_id, \AFCGlide\Core\Constants::META_BATHS); 
$sqft     = \AFCGlide\Core\Constants::get_meta($post_id, \AFCGlide\Core\Constants::META_SQFT);
$intro    = \AFCGlide\Core\Constants::get_meta($post_id, \AFCGlide\Core\Constants::META_INTRO);
$narrative = \AFCGlide\Core\Constants::get_meta($post_id, \AFCGlide\Core\Constants::META_NARRATIVE);

// ENTERPRISE BILINGUAL HARVEST (Costa Rica Sync)
$current_lang = afcglide_get_current_lang();
if ( $current_lang === 'es' ) {
    $intro_es = \AFCGlide\Core\Constants::get_meta( $post_id, \AFCGlide\Core\Constants::META_INTRO_ES );
    $narrative_es = \AFCGlide\Core\Constants::get_meta( $post_id, \AFCGlide\Core\Constants::META_NARRATIVE_ES );
    
    if ( ! empty($intro_es) ) $intro = $intro_es;
    if ( ! empty($narrative_es) ) $narrative = $narrative_es;
}

// GALLERY
$gallery  = \AFCGlide\Core\Constants::get_meta($post_id, \AFCGlide\Core\Constants::META_GALLERY_IDS) ?: [];

// AMENITIES
$amenities = \AFCGlide\Core\Constants::get_meta($post_id, \AFCGlide\Core\Constants::META_AMENITIES);

// AGENT DATA
$a_name   = \AFCGlide\Core\Constants::get_meta($post_id, \AFCGlide\Core\Constants::META_AGENT_NAME);
$a_phone  = \AFCGlide\Core\Constants::get_meta($post_id, \AFCGlide\Core\Constants::META_AGENT_PHONE);
$a_photo  = \AFCGlide\Core\Constants::get_meta($post_id, \AFCGlide\Core\Constants::META_AGENT_PHOTO);
$a_img    = $a_photo ? wp_get_attachment_url($a_photo) : AFCG_URL . 'assets/images/placeholder-agent.png';

// CLEAN PHONE
$clean_phone = preg_replace('/[^0-9]/', '', $a_phone);

// TOTAL PHOTOS (Hero + Gallery)
$total_photos = 1 + count($gallery);
?>

<div class="afcglide-wrapper">
    
    <!-- VISUAL STAGE -->
    <?php include AFCG_PATH . 'templates/parts/listing-visuals.php'; ?>

    <!-- CONTENT GRID -->
    <div class="afc-listing-grid">
        
        <!-- MAIN CONTENT -->
        <main class="afc-main-content">
            
            <!-- TITLE & ADDRESS -->
            <div class="afc-title-section">
                <h1 class="afc-property-title"><?php the_title(); ?></h1>
                <?php if ($intro) : ?>
                    <span class="afc-property-subtitle"><?php echo esc_html($intro); ?></span>
                <?php endif; ?>
                <p class="afc-property-address">📍 <?php echo esc_html($address); ?></p>
            </div>

            <!-- SPECS BAR -->
            <?php include AFCG_PATH . 'templates/parts/listing-specs.php'; ?>

            <!-- DESCRIPTION & AMENITIES -->
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
            include AFCG_PATH . 'templates/parts/listing-content.php'; 
            
            // GEOSPATIAL INTELLIGENCE
            include AFCG_PATH . 'templates/parts/listing-map.php';
            ?>

        </main>

        <!-- SIDEBAR -->
        <?php include AFCG_PATH . 'templates/parts/listing-sidebar.php'; ?>

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