<?php
/**
 * Fired during plugin deactivation.
 *
 * @since      1.0.0
 * @package    Weather_Station
 */

class Weather_Station_Deactivator {

    /**
     * Clean up on plugin deactivation.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Flush rewrite rules.
        flush_rewrite_rules();
        
        // Clear any scheduled events.
        wp_clear_scheduled_hook('weather_station_update_data');
    }
}