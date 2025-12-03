<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Weather_Station
 */

class Weather_Station_Frontend {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name . '-main',
            plugin_dir_url( __FILE__ ) . '../assets/dist/css/main.css',
            array(),
            $this->version,
            'all'
        );

        // Load Roboto font.
        wp_enqueue_style(
            'weather-station-roboto',
            'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap',
            [],
            null
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        
        wp_enqueue_script('gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js', array(), '3.12.2', true);
        wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
        wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);

        // Enqueue main script.
        wp_enqueue_script(
            'weather-station-main-script',
            plugins_url('../assets/dist/js/main.js', __FILE__),
            array('gsap'),
            WEATHER_STATION_VERSION,
            true
        );

        // Enqueue map script.
        wp_enqueue_script(
            'weather-station-map', 
            plugin_dir_url(__FILE__) . '../assets/dist/js/map.js',
            array('gsap', 'leaflet-js'),
            WEATHER_STATION_VERSION,
            true
        );
        
        $stations_data = $this->get_stations_data();
        
        // Get sidebar description.
        $sidebar_description = get_option('weather_station_sidebar_description', 'Interactive weather map displaying real-time weather data from stations around the world.');
        $plugin_url = plugin_dir_url(dirname(__FILE__));

        wp_localize_script('weather-station-map', 'weatherStationData', array(
            'stations' => $stations_data,
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('weather_station_nonce'),
            'sidebar_description' => wp_kses($sidebar_description, array(
                'p' => array(),
                'br' => array(),
                'strong' => array(),
                'em' => array(),
                'a' => array('href' => array(), 'title' => array(), 'target' => array()),
            )),
            'plugin_url' => $plugin_url,
            'bookmark_icon' => $plugin_url . 'assets/src/images/svg/Bookmark.svg',
            'bookmark_filled_icon' => $plugin_url . 'assets/src/images/svg/BookmarkFilled.svg'
        ));
    }

    /**
     * Get weather stations data for JavaScript
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
                    'name' => get_the_title(),
                    'lat' => (float) get_post_meta($post_id, 'lat', true),
                    'lng' => (float) get_post_meta($post_id, 'lng', true),
                    'weather_data' => get_post_meta($post_id, 'weather_data', true)
                );
            }
        }
        
        wp_reset_postdata();
        
        return $stations;
    }
}