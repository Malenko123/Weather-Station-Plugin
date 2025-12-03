<?php
/**
 * Plugin Name: Weather Station
 * Plugin URI:  https://example.com/weather-station
 * Description: Display weather stations on an interactive map with data from OpenWeatherMap.
 * Version:     1.0.0
 * Author:      Kliment Malenko
 * Author URI:  https://example.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: weather-station
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Define plugin constants.
 */
define('WEATHER_STATION_VERSION', '1.0.0');
define('WEATHER_STATION_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WEATHER_STATION_PLUGIN_PATH', plugin_dir_path(__FILE__));

register_activation_hook( __FILE__, array( 'Weather_Station_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Weather_Station_Deactivator', 'deactivate' ) );

// Use Composer autoloader if available.
if (file_exists(WEATHER_STATION_PLUGIN_PATH . 'vendor/autoload.php')) {
    require_once WEATHER_STATION_PLUGIN_PATH . 'vendor/autoload.php';
}

/**
 * The core plugin class.
 */
require_once WEATHER_STATION_PLUGIN_PATH . 'includes/class-weather-station.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_weather_station() {
    $plugin = new Weather_Station();
    $plugin->run();
}

run_weather_station();