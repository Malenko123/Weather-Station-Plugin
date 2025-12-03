<?php
/**
 * Handles ACF field setup for the weather station plugin
 *
 * @package    Weather_Station
 */

class Weather_Station_ACF {

    /**
     * Initialize ACF functionality
     *
     * @since    1.0.0
     */
    public function init() {
        // Check if ACF is active
        if (!class_exists('ACF')) {
            add_action('admin_notices', array($this, 'acf_notice'));
            return;
        }
        
        $this->register_fields();
    }

    /**
     * Display notice if ACF is not active
     *
     * @since    1.0.0
     */
    public function acf_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('Weather Station Plugin requires Advanced Custom Fields to be installed and activated.', 'weather-station'); ?></p>
        </div>
        <?php
    }

    /**
     * Register ACF fields for weather station CPT
     *
     * @since    1.0.0
     */
    private function register_fields() {
        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group(array(
                'key' => 'group_weather_station_fields',
                'title' => 'Weather Station Details',
                'fields' => array(
                    array(
                        'key' => 'field_lat',
                        'label' => 'Latitude',
                        'name' => 'lat',
                        'type' => 'number',
                        'instructions' => 'Enter the latitude coordinate of the weather station',
                        'required' => 1,
                        'step' => '0.000001',
                        'placeholder' => '52.520008',
                    ),
                    array(
                        'key' => 'field_lng',
                        'label' => 'Longitude',
                        'name' => 'lng',
                        'type' => 'number',
                        'instructions' => 'Enter the longitude coordinate of the weather station',
                        'required' => 1,
                        'step' => '0.000001',
                        'placeholder' => '13.404954',
                    ),
                    array(
                        'key' => 'field_weather_data',
                        'label' => 'Weather Data',
                        'name' => 'weather_data',
                        'type' => 'textarea',
                        'instructions' => 'Weather data will be automatically updated every 24 hours',
                        'readonly' => 1,
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'weather_station',
                        ),
                    ),
                ),
            ));
        }
    }
}