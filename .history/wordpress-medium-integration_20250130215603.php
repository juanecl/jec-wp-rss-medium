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

class JEC_Medium {
    private static $instance = null;

    private function __construct() {
        $this->define_constants();
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
        wp_enqueue_style('wmi-styles', plugins_url('assets/css/styles.css', __FILE__));
        wp_enqueue_script('wmi-scripts', plugins_url('assets/js/scripts.js', __FILE__), ['jquery'], '1.0.0', true);
    }

    public function register_widget() {
        register_widget('MediumWidget');
    }

    public function register_settings() {
        add_settings_field(
            'wmi_feed_url',
            __('Medium Feed URL', 'text_domain'),
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

        if (empty($posts)) {
            return '<p>' . __('No posts found.', 'text_domain') . '</p>';
        }

        ob_start();
        ?>
        <div id="mediumCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach (array_chunk($posts, 3) as $index => $post_chunk): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="row">
                            <?php foreach ($post_chunk as $post): ?>
                                <div class="col-md-4 d-flex align-items-stretch">
                                    <div class="card mb-4 bg-secondary-muted text-white" style="border: 3px solid var(--bs-secondary);box-shadow: 5px 5px 0 rgba(0,0,0,0.3);">
                                        <div class="card-img-top-wrapper" style="height: 250px; overflow: hidden;">
                                            <img src="<?php echo esc_url($post['image']); ?>" class="d-block w-100" alt="<?php echo esc_attr($post['title']); ?>" style="height: 100%; object-fit: cover;">
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title flex-grow-1"><a href="<?php echo esc_url($post['link']); ?>" target="_blank"><?php echo esc_html($post['title']); ?></a></h5>
                                            <p class="card-text"><?php echo esc_html($post['description']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#mediumCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#mediumCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the plugin
JEC_Medium::get_instance();
?>