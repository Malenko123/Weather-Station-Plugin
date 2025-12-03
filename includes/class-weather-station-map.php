<?php
/**
 * Handles map functionality for the weather station plugin
 *
 * @package    Weather_Station
 */

class Weather_Station_Map {

    /**
     * Initialize map functionality
     *
     * @since    1.0.0
     */
    public function init() {
        add_shortcode('weather_station_map', array($this, 'render_map_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_map_scripts'));
    }

    /**
     * Render the map shortcode
     *
     * @since    1.0.0
     */
    public function render_map_shortcode() {
        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/map-template.php';
        return ob_get_clean();
    }

    /**
     * Enqueue map scripts and styles
     *
     * @since    1.0.0
     */
    public function enqueue_map_scripts() {
        global $post;
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'weather_station_map')) {
            wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
            wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);
            
            wp_enqueue_style('weather-station-map', plugin_dir_url(__FILE__) . '../assets/dist/css/map.css');
            wp_enqueue_script('weather-station-map', plugin_dir_url(__FILE__) . '../assets/dist/js/map.js', array('gsap', 'leaflet-js', 'jquery'), WEATHER_STATION_VERSION, true);
            
            // Localize script with stations data.
            $stations = $this->get_stations_data();
            wp_localize_script('weather-station-map', 'weatherStationData', array(
                'stations' => $stations,
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('weather_station_nonce')
            ));
        }
    }

    /**
     * Get all weather stations data
     *
     * @since    1.0.0
     * @return   array   Array of station data
     */
    private function get_stations_data() {
        $stations = array();
        $args = array(
            'post_type' => 'weather_station',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        );
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                $stations[] = array(
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'lat' => get_post_meta($post_id, 'lat', true),
                    'lng' => get_post_meta($post_id, 'lng', true),
                    'weather_data' => get_post_meta($post_id, 'weather_data', true)
                );
            }
        }
        
        wp_reset_postdata();
        return $stations;
    }
}