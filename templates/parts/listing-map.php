<?php
/**
 * AFCGlide Listing Map Template Part
 * Leaflet.js Implementation - Zero Bloat
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$lat     = \AFCGlide\Core\Constants::get_meta( get_the_ID(), \AFCGlide\Core\Constants::META_GPS_LAT );
$lng     = \AFCGlide\Core\Constants::get_meta( get_the_ID(), \AFCGlide\Core\Constants::META_GPS_LNG );
$address = \AFCGlide\Core\Constants::get_meta( get_the_ID(), \AFCGlide\Core\Constants::META_ADDRESS );

if ( ! $lat || ! $lng ) return;
?>

<section class="afc-location-stage" style="margin-top: 50px;">
    <div class="afc-section-header">
        <h2 class="afc-section-title">📍 <?php echo afcglide_get_current_lang() === 'es' ? 'Ubicación de la Propiedad' : 'Property Location'; ?></h2>
        <p class="afc-section-subtitle"><?php echo esc_html( $address ); ?></p>
    </div>
    
    <div id="afc-listing-map" style="height: 400px; width: 100%; border-radius: 20px; border: 4px solid #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; margin-top: 20px;"></div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if ( typeof L === 'undefined' ) return;
    
    const lat = <?php echo floatval( $lat ); ?>;
    const lng = <?php echo floatval( $lng ); ?>;
    
    const map = L.map('afc-listing-map', {
        center: [lat, lng],
        zoom: 14,
        zoomControl: false,
        scrollWheelZoom: false
    });

    // Luxury Map Tiles (CartoDB Voyager)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    // Custom Luxury Marker
    const luxuryIcon = L.divIcon({
        className: 'afc-map-marker',
        html: '<div style="background: #1e293b; width: 40px; height: 40px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px;">💎</div>',
        iconSize: [40, 40],
        iconAnchor: [20, 20]
    });

    L.marker([lat, lng], { icon: luxuryIcon }).addTo(map);
    
    // Add custom zoom controls
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // Interaction feedback
    map.on('mousedown', function() {
        map.scrollWheelZoom.enable();
    });
});
</script>
