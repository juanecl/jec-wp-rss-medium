<?php
/**
 * Plugin Name: WordPress Medium Integration
 * Description: A plugin to display the latest posts from Medium using RSS feeds.
 * Version: 1.0
 * Author: Your Name
 * License: GPL2
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin directory
define( 'WMI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Include necessary files
require_once WMI_PLUGIN_DIR . 'includes/class-medium-rss-fetcher.php';
require_once WMI_PLUGIN_DIR . 'includes/class-medium-widget.php';

// Enqueue scripts and styles
function wmi_enqueue_scripts() {
    wp_enqueue_style( 'wmi-styles', plugins_url( 'assets/css/styles.css', __FILE__ ) );
    wp_enqueue_script( 'wmi-scripts', plugins_url( 'assets/js/scripts.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'wmi_enqueue_scripts' );

// Register the Medium widget
function wmi_register_widget() {
    register_widget( 'MediumWidget' );
}
add_action( 'widgets_init', 'wmi_register_widget' );

// Shortcode to display Medium feed
function wmi_medium_feed_shortcode() {
    $fetcher = new Medium_RSS_Fetcher();
    $posts = $fetcher->fetch_feed();

    if (empty($posts)) {
        return '<p>No posts found.</p>';
    }

    $output = '<div class="medium-feed">';
    foreach ($posts as $post) {
        $output .= '<div class="medium-post">';
        $output .= '<h2><a href="' . esc_url($post['link']) . '">' . esc_html($post['title']) . '</a></h2>';
        $output .= '<img src="' . esc_url($post['image']) . '" alt="' . esc_attr($post['title']) . '">';
        $output .= '<p>' . esc_html($post['description']) . '</p>';
        $output .= '</div>';
    }
    $output .= '</div>';

    return $output;
}
add_shortcode('medium_feed', 'wmi_medium_feed_shortcode');
?>