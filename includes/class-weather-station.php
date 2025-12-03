<?php
/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    Weather_Station
 */

class Weather_Station {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->version = WEATHER_STATION_VERSION;
        $this->plugin_name = 'weather-station';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path(__FILE__) . 'class-weather-station-loader.php';
        require_once plugin_dir_path(__FILE__) . 'class-weather-station-i18n.php';
        require_once plugin_dir_path(__FILE__) . 'class-weather-station-activator.php';
        require_once plugin_dir_path(__FILE__) . 'class-weather-station-deactivator.php';
        require_once plugin_dir_path(__FILE__) . 'class-weather-station-cpt.php';
        require_once plugin_dir_path(__FILE__) . 'class-weather-station-admin.php';
        require_once plugin_dir_path(__FILE__) . 'class-weather-station-frontend.php';
        require_once plugin_dir_path(__FILE__) . 'class-weather-station-ajax.php';
        require_once plugin_dir_path(__FILE__) . 'class-weather-station-map.php';
        require_once plugin_dir_path(__FILE__) . 'class-weather-station-acf.php';
        require_once plugin_dir_path(__FILE__) . 'class-weather-station-blocks.php';
        require_once plugin_dir_path(__FILE__) . 'class-weather-station-templates.php';

        $this->loader = new Weather_Station_Loader();
    }

    private function set_locale() {
        $plugin_i18n = new Weather_Station_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    private function define_admin_hooks() {
        $plugin_admin = new Weather_Station_Admin($this->get_plugin_name(), $this->get_version());
        $plugin_cpt = new Weather_Station_CPT();
        $plugin_acf = new Weather_Station_ACF();

        $this->loader->add_action('init', $plugin_cpt, 'register_cpt');
        $this->loader->add_action('admin_init', $plugin_acf, 'init');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_options_page');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
    }

    private function define_public_hooks() {
        $plugin_public = new Weather_Station_Frontend($this->get_plugin_name(), $this->get_version());
        $plugin_ajax = new Weather_Station_AJAX();
        $plugin_map = new Weather_Station_Map();
        $plugin_blocks = new Weather_Station_Blocks();
        $plugin_templates = new Weather_Station_Templates();
        
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('init', $plugin_ajax, 'init');
        $this->loader->add_action('init', $plugin_map, 'init');
        $this->loader->add_action('init', $plugin_blocks, 'init');
        $this->loader->add_action('init', $plugin_templates, 'init');
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }
}