<?php
/**
 * Register Weather Station custom post type
 *
 * @package    Weather_Station
 */

class Weather_Station_CPT {

    /**
     * Register the custom post type and meta fields.
     *
     * @since    1.0.0
     */
    public function register_cpt() {
        $labels = array(
            'name'                  => _x('Weather Stations', 'Post Type General Name', 'weather-station'),
            'singular_name'         => _x('Weather Station', 'Post Type Singular Name', 'weather-station'),
            'menu_name'             => __('Weather Stations', 'weather-station'),
            'name_admin_bar'        => __('Weather Station', 'weather-station'),
            'archives'              => __('Weather Station Archives', 'weather-station'),
            'attributes'            => __('Weather Station Attributes', 'weather-station'),
            'parent_item_colon'     => __('Parent Weather Station:', 'weather-station'),
            'all_items'             => __('All Weather Stations', 'weather-station'),
            'add_new_item'          => __('Add New Weather Station', 'weather-station'),
            'add_new'               => __('Add New', 'weather-station'),
            'new_item'              => __('New Weather Station', 'weather-station'),
            'edit_item'             => __('Edit Weather Station', 'weather-station'),
            'update_item'           => __('Update Weather Station', 'weather-station'),
            'view_item'             => __('View Weather Station', 'weather-station'),
            'view_items'            => __('View Weather Stations', 'weather-station'),
            'search_items'          => __('Search Weather Station', 'weather-station'),
            'not_found'             => __('Not found', 'weather-station'),
            'not_found_in_trash'    => __('Not found in Trash', 'weather-station'),
            'featured_image'        => __('Station Image', 'weather-station'),
            'set_featured_image'    => __('Set station image', 'weather-station'),
            'remove_featured_image' => __('Remove station image', 'weather-station'),
            'use_featured_image'    => __('Use as station image', 'weather-station'),
            'insert_into_item'      => __('Insert into station', 'weather-station'),
            'uploaded_to_this_item' => __('Uploaded to this station', 'weather-station'),
            'items_list'            => __('Stations list', 'weather-station'),
            'items_list_navigation' => __('Stations list navigation', 'weather-station'),
            'filter_items_list'     => __('Filter stations list', 'weather-station'),
        );
        
        $args = array(
            'label'                 => __('Weather Station', 'weather-station'),
            'description'           => __('Weather station locations and data', 'weather-station'),
            'labels'                => $labels,
            'supports'              => array('title'),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 20,
            'menu_icon'             => 'dashicons-location-alt',
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => 'post',
            'show_in_rest'          => false,
        );
        
        register_post_type('weather_station', $args);
        
        $this->register_meta_fields();
    }
    
    /**
     * Register custom meta fields for the weather station CPT.
     *
     * @since    1.0.0
     */
    private function register_meta_fields() {
         register_post_meta('weather_station', 'lat', array(
            'type' => 'number',
            'description' => __('Location latitude', 'weather-station'),
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => array($this, 'sanitize_float'),
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        ));
        
        register_post_meta('weather_station', 'lng', array(
            'type' => 'number',
            'description' => __('Location longitude', 'weather-station'),
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => array($this, 'sanitize_float'),
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        ));
        
        register_post_meta('weather_station', 'weather_data', array(
            'type' => 'string',
            'description' => __('Weather data stored for 24 hours', 'weather-station'),
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        ));
    }

    /**
     * Sanitize and validate float values for meta fields
     *
     * @since    1.0.1
     * @param mixed $value The value to sanitize
     * @param string $meta_key The meta key
     * @param string $object_type The object type
     * @param string $object_subtype The object subtype
     * @return float Sanitized float value
     */
    public function sanitize_float($value, $meta_key = '', $object_type = '', $object_subtype = '') {
        $float_value = floatval($value);
        
        // Additional validation for latitude.
        if ($meta_key === 'lat' && ($float_value < -90 || $float_value > 90)) {
            $float_value = 0; // Default to 0 if invalid.
        }
        
        // Additional validation for longitude.
        if ($meta_key === 'lng' && ($float_value < -180 || $float_value > 180)) {
            $float_value = 0; // Default to 0 if invalid.
        }
        
        return $float_value;
    }
}