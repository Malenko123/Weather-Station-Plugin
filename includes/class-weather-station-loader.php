<?php
/**
 * Register all actions and filters for the plugin.
 *
 * @package    Weather_Station
 */

class Weather_Station_Loader {

    protected $actions;
    protected $filters;

    public function __construct() {
        $this->actions = array();
        $this->filters = array();
    }

    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    public function run() {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }

        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }
    }

    private function define_public_hooks() { 
        $plugin_public = new Weather_Station_Frontend($this->get_plugin_name(), $this->get_version());
        $plugin_ajax = new Weather_Station_AJAX();
        $plugin_map = new Weather_Station_Map();
        $plugin_blocks = new Weather_Station_Blocks();
        
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('init', $plugin_ajax, 'init');
        $this->loader->add_action('init', $plugin_map, 'init');
        $this->loader->add_action('init', $plugin_blocks, 'init');
        
        // Add register_blocks method.
        $this->loader->add_action('init', $plugin_blocks, 'register_blocks', 5);
    }
}