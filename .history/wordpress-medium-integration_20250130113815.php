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
    wp_enqueue_script( 'wmi-scripts', plugins_url( 'assets/js/scripts.js', __FILE__ ), array( 'jquery' ), null, true );
}
add_action( 'wp_enqueue_scripts', 'wmi_enqueue_scripts' );

// Register the Medium widget
function wmi_register_widget() {
    register_widget( 'MediumWidget' );
}
add_action( 'widgets_init', 'wmi_register_widget' );
?>