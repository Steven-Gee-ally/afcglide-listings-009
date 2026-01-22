<?php
/**
 * AFCGlide Template Part: Sidebar (Agent Card)
 * Version 4.5 - Enterprise Hardened
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Expects $a_img, $a_name, $clean_phone to be passed or accessible
?>
<aside class="afc-sidebar-modern">
    
    <!-- AGENT CARD -->
    <div class="afcglide-agent-card">
        <div class="afc-agent-photo-wrap">
            <img src="<?php echo esc_url($a_img); ?>" alt="<?php echo esc_attr($a_name); ?>">
        </div>
        
        <h3 class="afc-agent-name"><?php echo esc_html($a_name); ?></h3>
        <p class="afc-agent-title"><?php esc_html_e('Listing Specialist', 'afcglide'); ?></p>
        
        <div class="afc-agent-actions">
            <?php if ($clean_phone): ?>
            <a href="tel:<?php echo esc_attr($clean_phone); ?>" class="afc-btn-primary">
                📞 <?php esc_html_e('Call Agent', 'afcglide'); ?>
            </a>
            <a href="https://wa.me/<?php echo esc_attr($clean_phone); ?>" class="afc-btn-primary" style="background: #25D366;">
                💬 <?php esc_html_e('WhatsApp', 'afcglide'); ?>
            </a>
            <?php endif; ?>

            <!-- ELITE LEAD CAPTURE -->
            <button type="button" class="afc-btn-outline afc-trigger-showing" style="margin-top: 10px;">
                💎 <?php echo afcglide_get_current_lang() === 'es' ? 'Programar Visita' : 'Schedule Showing'; ?>
            </button>
        </div>
    </div>

</aside>
