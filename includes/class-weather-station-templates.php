<?php
/**
 * Handles template functionality for the weather station plugin
 *
 * @package    Weather_Station
 */

class Weather_Station_Templates {

    /**
     * Initialize template functionality
     *
     * @since    1.0.0
     */
    public function init() {
        add_filter('theme_page_templates', array($this, 'add_fullscreen_template'));
        add_filter('template_include', array($this, 'load_fullscreen_template'));
        
        // Use a safer approach to remove theme output.
        add_action('template_redirect', array($this, 'maybe_remove_theme_output'));
    }

    /**
     * Remove theme output only for our template
     */
    public function maybe_remove_theme_output() {
        global $post;
        
        if (!$post || !is_page()) {
            return;
        }
        
        $page_template = get_post_meta($post->ID, '_wp_page_template', true);
        
        if ($page_template === 'map-template.php') {
            // Remove theme support - safer than remove_all_actions.
            add_filter('show_admin_bar', '__return_false'); // Can be an option within the plugin.
            add_filter('wp_using_themes', '__return_false');
            
            // Buffer output and clean it.
            ob_start();
            add_action('shutdown', array($this, 'clean_output'), 0);
        }
    }

    /**
     * Clean the output buffer to remove theme content
     */
    public function clean_output() {
        $output = ob_get_clean();
        
        // Only clean if this is our template page.
        global $post;
        if ($post && get_post_meta($post->ID, '_wp_page_template', true) === 'map-template.php') {
            // Start fresh output
            ob_start();
            $this->output_minimal_template();
            $clean_output = ob_get_clean();
            echo $clean_output;
        } else {
            echo $output;
        }
    }

    /**
     * Output minimal template structure
     */
    public function output_minimal_template() {
        ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_the_title() . ' - ' . get_bloginfo('name'); ?></title>
    <?php 
    do_action('wp_head');
    ?>
</head>
<body <?php body_class('weather-station-fullscreen'); ?>>

    <!-- Hero Section -->
    <section class="ws-hero-section">
        <?php
        $site_logo_id = get_theme_mod('custom_logo');
        ?>
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
        ?> </div>

        <?php
        $hero_heading = get_option('weather_station_hero_heading');
        if ( empty( $hero_heading ) ) {
            $hero_heading = __('WeatherWay', 'weather-station');
        }
        ?>
        <div class="ws-hero-content">
            <h1><?php echo esc_html( $hero_heading ); ?></h1>
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
                    <?php 
                    $sidebar_desc = get_option('weather_station_sidebar_description');
                    if ( empty($sidebar_desc) ) {
                        $sidebar_desc = __('Click on the map to get weather data.', 'weather-station');
                    }
                    echo wp_kses($sidebar_desc, array(
                        'p' => array(),
                        'br' => array(),
                        'strong' => array(),
                        'em' => array(),
                        'a' => array('href' => array(), 'title' => array(), 'target' => array()),
                    ));
                    ?>
                </div>
                <div class="weather-data-container"></div>
            </div>
        </div>

        <div class="gradient-bg"></div>
        <div id="weather-station-map" class="weather-station-map"></div>
    </div>

    <?php 
    do_action('wp_footer'); 
    ?>
    
</body>
</html>
        <?php
    }

    /**
     * Add fullscreen template to page templates
     */
    public function add_fullscreen_template($templates) {
        $templates['map-template.php'] = __('Full Screen Weather Map', 'weather-station');
        return $templates;
    }

    /**
     * Load the fullscreen template when selected
     */
    public function load_fullscreen_template($template) {
        global $post;
        
        if (!$post) {
            return $template;
        }
        
        $page_template = get_post_meta($post->ID, '_wp_page_template', true);
        
        if ($page_template === 'map-template.php') {
            // Return a simple template that does nothing.
            // We handle the output via output buffering.
            return plugin_dir_path(__FILE__) . 'templates/simple-template.php';
        }
        
        return $template;
    }
}