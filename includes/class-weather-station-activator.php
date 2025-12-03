<?php
/**
 * Fired during plugin activation.
 *
 * @package    Weather_Station
 */

class Weather_Station_Activator {

    public static function activate() {
        self::create_map_page();
        flush_rewrite_rules();
    }

    private static function create_map_page() {
        // Check if page already exists.
        $existing_page = get_page_by_path('map');
        if ($existing_page) {
            update_post_meta($existing_page->ID, '_wp_page_template', 'map-template.php');
            
            return $existing_page->ID;
        }
        
        $page_data = array(
            'post_title'    => 'Map Page',
            'post_name'     => 'map',
            'post_content'  => '<!-- wp:weather-station/weather-map --><div class="wp-block-weather-station-weather-map"></div><!-- /wp:weather-station-weather-map -->',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => 1,
        );
        
        $page_id = wp_insert_post($page_data);
        
        if (!is_wp_error($page_id)) {
            // Force set the template meta immediately after creation.
            update_post_meta($page_id, '_wp_page_template', 'map-template.php');
            update_option('weather_station_map_page_id', $page_id);
        } else {
            error_log('Weather Station: Failed to create map page: ' . $page_id->get_error_message());
        }
        
        return $page_id;
    }
}