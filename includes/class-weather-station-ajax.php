<?php
/**
 * Handle AJAX requests for weather data
 *
 * @package    Weather_Station
 */

class Weather_Station_AJAX {

    /**
     * Initialize AJAX handlers
     *
     * @since    1.0.0
     */
    public function init() {
        add_action('wp_ajax_get_weather_data', array($this, 'get_weather_data'));
        add_action('wp_ajax_nopriv_get_weather_data', array($this, 'get_weather_data'));
    }

    /**
     * Get weather data from OpenWeatherMap API
     *
     * @since    1.0.0
     */
    public function get_weather_data() {
        if (!wp_verify_nonce($_POST['nonce'], 'weather_station_nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        
        $station_id = intval($_POST['station_id']);
        $lat = floatval($_POST['lat']);
        $lng = floatval($_POST['lng']);
        $units = sanitize_text_field($_POST['units']);
        
        // Check if we have cached data for this specific unit.
        $cached_data = get_transient('weather_data_' . $station_id . '_' . $units);
        
        if ($cached_data !== false) {
            wp_send_json_success($cached_data);
        }
        
        // Get API key from settings.
        $api_key = get_option('weather_station_api_key');
        
        if (empty($api_key)) {
            wp_send_json_error('API key not configured');
        }
        
        // Build API URL with units parameter.
        $api_url = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lng}&units={$units}&appid={$api_key}";
        
        // Make API request
        $response = wp_remote_get($api_url, array('timeout' => 15));
        
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['cod']) && $data['cod'] !== 200) {
            wp_send_json_error($data['message'] ?? 'Unknown error from API');
        }
        
        // Cache the data for 24 hours with unit-specific key.
        set_transient('weather_data_' . $station_id . '_' . $units, $data, DAY_IN_SECONDS);
        
        wp_send_json_success($data);
    }
}