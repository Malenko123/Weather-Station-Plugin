<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Weather_Station
 */

class Weather_Station_Admin {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        $css_path = plugin_dir_path(__FILE__) . '../assets/dist/css/admin.css';
        if (file_exists($css_path)) {
            wp_enqueue_style(
                $this->plugin_name . '-admin',
                plugin_dir_url(__FILE__) . '../assets/dist/css/admin.css',
                array(),
                $this->version,
                'all'
            );
        }
    }

    public function enqueue_scripts() {
        $js_path = plugin_dir_path(__FILE__) . '../assets/dist/js/admin.js';
        if (file_exists($js_path)) {
            wp_enqueue_script(
                $this->plugin_name . '-admin',
                plugin_dir_url(__FILE__) . '../assets/dist/js/admin.js',
                array('jquery'),
                $this->version,
                false
            );
        }
    }

    public function add_options_page() {
        add_options_page(
            'Weather Station Settings',
            'Weather Station',
            'manage_options',
            'weather-station',
            array( $this, 'display_options_page' )
        );
    }

    public function display_options_page() {
        ?>
        <div class="wrap">
            <h1>Weather Station Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'weather_station_options' );
                do_settings_sections( 'weather-station' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Registering settings page for plugin Weather Station.
     */
    public function register_settings() {
        register_setting(
            'weather_station_options',
            'weather_station_api_key',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        register_setting(
            'weather_station_options',
            'weather_station_hero_heading',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        register_setting(
            'weather_station_options',
            'weather_station_sidebar_description',
            array(
                'type' => 'string',
                'sanitize_callback' => 'wp_kses_post', // Allow HTML in description.
                'default' => 'Interactive weather map displaying real-time weather data from stations around the world.'
            )
        );

        add_settings_section(
            'weather_station_api_section',
            'API Settings',
            array( $this, 'display_api_section' ),
            'weather-station'
        );

        add_settings_section(
            'weather_station_ui_section',
            'UI Settings',
            array($this, 'display_ui_section'),
            'weather-station'
        );

        add_settings_field(
            'weather_station_api_key',
            'OpenWeatherMap API Key',
            array( $this, 'display_api_key_field' ),
            'weather-station',
            'weather_station_api_section'
        );

        add_settings_field(
            'weather_station_hero_heading',
            __('Hero Heading', 'weather-station'),
            array( $this, 'display_hero_heading_field' ),
            'weather-station',
            'weather_station_ui_section'
        );

        add_settings_field(
            'weather_station_sidebar_description',
            'Sidebar Description',
            array($this, 'display_sidebar_description_field'),
            'weather-station',
            'weather_station_ui_section'
        );
    }

    /**
     * Method for displaying the api section.
     */
    public function display_api_section() {
        echo '<p>Enter your OpenWeatherMap API key to enable weather data fetching.</p>';
        echo '<p>You can get an API key from <a href="https://home.openweathermap.org/api_keys" target="_blank">OpenWeatherMap</a>.</p>';
    }

    public function display_api_key_field() {
        $api_key = get_option('weather_station_api_key');
        echo '<input type="text" name="weather_station_api_key" value="' . esc_attr($api_key) . '" class="regular-text">';
        echo '<p class="description">Your OpenWeatherMap API key</p>';
    }

    public function display_ui_section() {
        echo '<p>Customize the appearance and text of your weather station.</p>';
    }

    public function display_hero_heading_field() {
        $hero_heading = get_option('weather_station_hero_heading', '');
        echo '<input type="text" name="weather_station_hero_heading" value="' . esc_attr($hero_heading) . '" class="regular-text">';
        echo '<p class="description">' . __('This text will appear as the hero heading on the homepage. If empty, it will default to "WeatherWay".', 'weather-station') . '</p>';
    }


    public function display_sidebar_description_field() {
        $description = get_option('weather_station_sidebar_description');
        wp_editor(
            $description,
            'weather_station_sidebar_description',
            array(
                'textarea_name' => 'weather_station_sidebar_description',
                'textarea_rows' => 5,
                'media_buttons' => false,
                'teeny' => true
            )
        );
        echo '<p class="description">This text will appear in the sidebar of the weather map. HTML is allowed.</p>';
    }
}