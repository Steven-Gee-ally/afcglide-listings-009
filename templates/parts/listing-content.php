<?php
/**
 * AFCGlide Template Part: Narrative & Amenities
 * Version 4.5 - Enterprise Hardened
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Expects $narrative, $amenities, $amenity_icons to be passed or accessible
?>
<div class="afc-description-section">
    <h2 class="afc-section-heading">
        <?php echo afcglide_get_current_lang() === 'es' ? 'Propiedad Narrativa' : 'Property Narrative'; ?>
    </h2>
    <div class="afc-description-content">
        <?php 
        if ($narrative) {
            echo wp_kses_post(apply_filters('the_content', $narrative));
        } else {
            the_content();
        }
        ?>
    </div>
</div>

<!-- AMENITIES -->
<?php if ( ! empty( $amenities ) && is_array( $amenities ) ) : ?>
<div class="afc-amenities-section">
    <h2 class="afc-section-heading"><?php esc_html_e('Premium Amenities', 'afcglide'); ?></h2>
    <div class="afc-amenities-grid">
        <?php 
        foreach ( $amenities as $amenity ) : 
            $icon = isset($amenity_icons[$amenity]) ? $amenity_icons[$amenity] : '💎';
        ?>
            <div class="afc-amenity-pill">
                <span class="afc-icon-check"><?php echo esc_html($icon); ?></span>
                <?php echo esc_html( $amenity ); ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
