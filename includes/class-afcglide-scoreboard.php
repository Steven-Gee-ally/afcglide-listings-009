<?php
namespace AFCGlide\Reporting;

use AFCGlide\Core\Constants as C;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AFCGlide Agent Scoreboard & Data Engine
 * Version 4.0.0 - The Real Estate Machine
 */
class AFCGlide_Scoreboard {

    /**
     * Fetch real-time data for the current agent or global
     */
    public static function get_stats( $user_id = null ) {
        global $wpdb;
        
        $stats = [
            'active_count' => 0, 'active_value' => 0,
            'pending_count' => 0, 'pending_value' => 0,
            'sold_count' => 0, 'sold_value' => 0,
            'total_hits' => 0
        ];

        $post_type = C::POST_TYPE;
        $author_query = $user_id ? $wpdb->prepare(" AND post_author = %d", $user_id) : "";

        // OPTIMIZED SQL: One pass for counts, values, and views
        // We join posts with postmeta twice for price and views
        $results = $wpdb->get_results("
            SELECT 
                post_status, 
                COUNT(*) as count, 
                SUM(CAST(pm_price.meta_value AS DECIMAL(20,2))) as total_value,
                SUM(CAST(pm_views.meta_value AS UNSIGNED)) as total_views
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm_price ON p.ID = pm_price.post_id AND pm_price.meta_key = '" . C::META_PRICE . "'
            LEFT JOIN {$wpdb->postmeta} pm_views ON p.ID = pm_views.post_id AND pm_views.meta_key = '" . C::META_VIEWS . "'
            WHERE post_type = '{$post_type}' 
            AND post_status IN ('publish', 'pending', 'sold')
            {$author_query}
            GROUP BY post_status
        ", ARRAY_A);

        foreach ( $results as $row ) {
            $status = $row['post_status'];
            $count = intval($row['count']);
            $val = floatval($row['total_value']);
            $views = intval($row['total_views']);

            if ( $status === 'publish' ) {
                $stats['active_count'] = $count;
                $stats['active_value'] = $val;
            } elseif ( $status === 'pending' ) {
                $stats['pending_count'] = $count;
                $stats['pending_value'] = $val;
            } elseif ( $status === 'sold' ) {
                $stats['sold_count'] = $count;
                $stats['sold_value'] = $val;
            }
            $stats['total_hits'] += $views;
        }

        return $stats;
    }

    /**
     * Render the Top Stats Scoreboard (Modern S-Grade)
     */
    public static function render_scoreboard( $user_id = null ) {
        $stats = self::get_stats( $user_id );
        ob_start(); ?>
        <div class="afc-modern-scoreboard">
            <div class="afc-stat-node" style="border-left: 4px solid #10b981;">
                <span class="afc-stat-label">ACTIVE PORTFOLIO</span>
                <span class="afc-stat-value">$<?php echo number_format( $stats['active_value'] ); ?></span>
                <span class="afc-stat-sub"><?php echo $stats['active_count']; ?> Live Assets</span>
            </div>
            <div class="afc-stat-node" style="border-left: 4px solid #f59e0b;">
                <span class="afc-stat-label">PENDING (ESCROW)</span>
                <span class="afc-stat-value">$<?php echo number_format( $stats['pending_value'] ); ?></span>
                <span class="afc-stat-sub"><?php echo $stats['pending_count']; ?> Under Contract</span>
            </div>
            <div class="afc-stat-node" style="border-left: 4px solid #ef4444;">
                <span class="afc-stat-label">CAREER VOLUME (SOLD)</span>
                <span class="afc-stat-value">$<?php echo number_format( $stats['sold_value'] ); ?></span>
                <span class="afc-stat-sub"><?php echo $stats['sold_count']; ?> Closed Transactions</span>
            </div>
            <div class="afc-stat-node" style="border-left: 4px solid #6366f1;">
                <span class="afc-stat-label">NETWORK ENGAGEMENT</span>
                <span class="afc-stat-value"><?php echo number_format( $stats['total_hits'] ); ?></span>
                <span class="afc-stat-sub">Interest Pings</span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}