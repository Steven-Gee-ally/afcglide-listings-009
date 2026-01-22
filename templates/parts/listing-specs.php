<?php
/**
 * AFCGlide Template Part: Specs Bar
 * Version 4.5 - Enterprise Hardened
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Expects $beds, $baths, $sqft to be passed or accessible
?>
<div class="afcglide-specs-bar">
    <div class="afcglide-spec-item">
        <label><?php esc_html_e('Bedrooms', 'afcglide'); ?></label>
        <strong><?php echo esc_html($beds ?: '—'); ?></strong>
    </div>
    <div class="afcglide-spec-item">
        <label><?php esc_html_e('Bathrooms', 'afcglide'); ?></label>
        <strong><?php echo esc_html($baths ?: '—'); ?></strong>
    </div>
    <div class="afcglide-spec-item">
        <label><?php esc_html_e('Square Feet', 'afcglide'); ?></label>
        <?php 
            $numeric_sqft = preg_replace('/[^0-9.]/', '', $sqft);
        ?>
        <strong><?php echo $numeric_sqft ? number_format((float)$numeric_sqft) : '—'; ?></strong>
    </div>
</div>
