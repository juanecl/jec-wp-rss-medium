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

// Add settings field for Medium feed URL
function wmi_register_settings() {
    add_settings_field(
        'wmi_feed_url',
        __('Medium Feed URL', 'text_domain'),
        'wmi_feed_url_render',
        'general'
    );

    register_setting('general', 'wmi_feed_url', 'esc_url');
}

function wmi_feed_url_render() {
    $value = get_option('wmi_feed_url', '');
    echo '<input type="url" id="wmi_feed_url" name="wmi_feed_url" value="' . esc_attr($value) . '" class="regular-text ltr" />';
}

add_action('admin_init', 'wmi_register_settings');

// Shortcode to display Medium feed
function wmi_medium_feed_shortcode($atts) {
    $feed_url = get_option('wmi_feed_url', '');
    $fetcher = new Medium_RSS_Fetcher($feed_url);
    $posts = $fetcher->fetch_feed();

    if (empty($posts)) {
        return '<p>' . __('No posts found.', 'text_domain') . '</p>';
    }

    ob_start();
    ?>
    <div class="medium-feed">
        <?php foreach ($posts as $post): ?>
            <div class="medium-post">
                <h2><a href="<?php echo esc_url($post['link']); ?>"><?php echo esc_html($post['title']); ?></a></h2>
                <img src="<?php echo esc_url($post['image']); ?>" alt="<?php echo esc_attr($post['title']); ?>">
                <p><?php echo esc_html($post['description']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('medium_feed', 'wmi_medium_feed_shortcode');
?>