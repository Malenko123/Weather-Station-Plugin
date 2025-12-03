<?php
/**
 * Handles Gutenberg block registration for the weather station plugin
 *
 * @package    Weather_Station
 */

class Weather_Station_Blocks {

    /**
     * Initialize block functionality
     *
     * @since    1.0.0
     */
    public function init() {
        // Register blocks directly instead of through loader.
        $this->register_blocks();        

        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
    }

    /**
     * Register custom Gutenberg blocks
     *
     * @since    1.0.0
     */
    public function register_blocks() {
        // Use manual registration instead of block.json for dynamic blocks.
        $result = register_block_type('weather-station/weather-map', array(
            'api_version' => 2,
            'title' => __('Weather Station Map', 'weather-station'),
            'category' => 'widgets',
            'icon' => 'location-alt',
            'description' => __('Display an interactive map with weather stations', 'weather-station'),
            'keywords' => array('weather', 'map', 'station'),
            'textdomain' => 'weather-station',
            'supports' => array(
                'html' => false,
                'align' => array('wide', 'full')
            ),
            'render_callback' => array($this, 'render_weather_map_block'),
            'editor_script' => 'weather-station-map-editor',
            'editor_style' => 'weather-station-blocks-editor',
        ));
          
        return $result;
    }

    /**
     * Enqueue block editor assets
     *
     * @since    1.0.0
     */
    public function enqueue_block_editor_assets() {
        // Only enqueue on block editor screens.
        $screen = get_current_screen();
        if (!$screen || !method_exists($screen, 'is_block_editor') || !$screen->is_block_editor()) {
            error_log('Weather Station: Not on block editor screen');
            return;
        }
    
        // Manual enqueue with explicit dependencies.
        wp_enqueue_script(
            'weather-station-map-editor',
            plugin_dir_url(dirname(__FILE__)) . 'blocks/weather-map/weather-station-map-editor.js',
            array(
                'wp-blocks', 
                'wp-element', 
                'wp-block-editor', 
                'wp-i18n', 
                'wp-components',
                'wp-editor'
            ),
            WEATHER_STATION_VERSION,
            true
        );
        
        wp_enqueue_style(
            'weather-station-blocks-editor',
            plugin_dir_url(dirname(__FILE__)) . 'blocks/weather-map/style.css',
            array('wp-edit-blocks'),
            WEATHER_STATION_VERSION
        );
    }

    /**
     * Render callback for the weather map block
     *
     * @since    1.0.0
     * @param array $attributes Block attributes
     * @param string $content Block content
     * @return string Rendered block HTML
     */
    public function render_weather_map_block($attributes, $content) {
        // Enqueue necessary scripts and styles
        wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
        wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);
        wp_enqueue_script('weather-station-map', plugin_dir_url(__FILE__) . '../assets/dist/js/map.js', array('leaflet-js'), WEATHER_STATION_VERSION, true);
        
        // Get the site logo and settings
        $site_logo_id = get_theme_mod('custom_logo');
        $hero_heading = get_option('weather_station_hero_heading', __('WeatherWay', 'weather-station'));
        $sidebar_description = get_option('weather_station_sidebar_description', __('Click on the map to get weather data.', 'weather-station'));

        // Define allowed HTML tags for the sidebar description
        $allowed_html = array(
            'p' => array(),
            'br' => array(),
            'strong' => array(),
            'em' => array(),
            'a' => array('href' => array(), 'title' => array(), 'target' => array()),
        );

        ob_start();
        ?>
        <!-- Hero Section -->
        <section class="ws-hero-section">
            <div class="hero-logo-container"> 
                <?php
                if ($site_logo_id) {
                    echo wp_get_attachment_image($site_logo_id, 'medium', false, array(
                        'style' => 'max-height: 50px; width: auto;',
                        'alt' => get_bloginfo('name') . ' - Weather Station'
                    ));
                } else {
                    echo '<h2>' . esc_html__('Weather Station', 'weather-station') . '</h2>';
                }
                ?> 
            </div>

            <div class="ws-hero-content">
                <h1><?php echo esc_html($hero_heading); ?></h1>
            </div>

            <span><?php echo __('Scroll', 'weather-station'); ?></span>
        </section>
        
        <div id="weather-station-app" class="weather-station-app">
            <div class="weather-station-sidebar">
                <div class="sidebar-header">
                    <div class="sidebar-logo-container">
                        <div class="logo">
                            <?php
                            if ($site_logo_id) {
                                echo wp_get_attachment_image($site_logo_id, 'medium', false, array(
                                    'style' => 'max-height: 50px; width: auto;',
                                    'alt' => get_bloginfo('name') . ' - Weather Station'
                                ));
                            } else {
                                echo '<h2>' . esc_html__('Weather Station', 'weather-station') . '</h2>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="sidebar-content">
                    <div class="sidebar-description">
                        <?php echo wp_kses($sidebar_description, $allowed_html); ?>
                    </div>
                    <div class="weather-data-container">
                        <div class="weather-data-placeholder">
                            <p>Select a weather station to view data</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="gradient-bg"></div>
            <div id="weather-station-map" class="weather-station-map"></div>
        </div>
        <?php
        return ob_get_clean();
    }
}