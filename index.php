<?php
/*
Plugin Name: jec-medium
Plugin URI: https://github.com/juanecl/jec-wp-rss-medium
Description: A plugin to display the latest posts from Medium using RSS feeds.
Version: 1.0
Author: Juan Enrique Chomon Del Campo
Author URI: https://www.juane.cl
License: GPL2
Text Domain: jec-medium
Domain Path: /languages
*/

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class JEC_Medium {
    private static $instance = null;

    private function __construct() {
        $this->define_constants();
        $this->load_textdomain();
        $this->includes();
        $this->init_hooks();
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function define_constants() {
        define('WMI_PLUGIN_DIR', plugin_dir_path(__FILE__));
    }

    private function load_textdomain() {
        load_plugin_textdomain('jec-medium', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    private function includes() {
        require_once WMI_PLUGIN_DIR . 'includes/class-medium-rss-fetcher.php';
        require_once WMI_PLUGIN_DIR . 'includes/class-medium-widget.php';
    }

    private function init_hooks() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('widgets_init', [$this, 'register_widget']);
        add_action('admin_init', [$this, 'register_settings']);
        add_shortcode('jec-medium', [$this, 'medium_feed_shortcode']);
    }

    public function enqueue_scripts() {
        // Enqueue custom styles and scripts
        wp_enqueue_style('wmi-styles', plugins_url('assets/css/styles.css', __FILE__), array(), '1.0.0');
        wp_enqueue_script('wmi-scripts', plugins_url('assets/js/scripts.js', __FILE__), ['jquery'], '1.0.0', true);
    }

    public function register_widget() {
        register_widget('MediumWidget');
    }

    public function register_settings() {
        add_settings_field(
            'wmi_feed_url',
            __('Medium Feed URL', 'jec-medium'),
            [$this, 'feed_url_render'],
            'general'
        );

        register_setting('general', 'wmi_feed_url', 'esc_url');
    }

    public function feed_url_render() {
        $value = get_option('wmi_feed_url', '');
        echo '<input type="url" id="wmi_feed_url" name="wmi_feed_url" value="' . esc_attr($value) . '" class="regular-text ltr" />';
    }

    public function medium_feed_shortcode($atts) {
        $feed_url = get_option('wmi_feed_url', '');
        $fetcher = new Medium_RSS_Fetcher($feed_url);
        $posts = $fetcher->fetch_feed();

        ob_start();
        include WMI_PLUGIN_DIR . 'templates/medium-feed-template.php';
        return ob_get_clean();
    }
}

// Initialize the plugin
JEC_Medium::get_instance();
?>