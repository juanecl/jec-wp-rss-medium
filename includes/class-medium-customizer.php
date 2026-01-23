<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Medium_Customizer
 * Manages theme customizer settings for Medium integration
 */
class Medium_Customizer {
    
    /**
     * Initialize customizer hooks
     */
    public static function init() {
        add_action('customize_register', [__CLASS__, 'register_customizer_settings']);
        add_action('init', [__CLASS__, 'maybe_migrate_posts_count_default']);
    }

    /**
     * Register customizer settings
     *
     * @param WP_Customize_Manager $wp_customize
     */
    public static function register_customizer_settings($wp_customize) {
        // Add section for Medium settings
        $wp_customize->add_section('jec_medium_settings', [
            'title'    => __('Medium Integration', 'jec-medium'),
            'priority' => 130,
            'description' => __('Configure Medium RSS feed settings', 'jec-medium'),
        ]);

        // Feed URL setting
        $wp_customize->add_setting('jec_medium_feed_url', [
            'default'           => '',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'refresh',
        ]);

        $wp_customize->add_control('jec_medium_feed_url_control', [
            'label'       => __('Medium Feed URL', 'jec-medium'),
            'description' => __('Enter your Medium RSS feed URL (e.g., https://medium.com/feed/@username)', 'jec-medium'),
            'section'     => 'jec_medium_settings',
            'settings'    => 'jec_medium_feed_url',
            'type'        => 'url',
            'priority'    => 10,
        ]);

        // Number of posts to display
        $wp_customize->add_setting('jec_medium_posts_count', [
            'default'           => 15,
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        ]);

        $wp_customize->add_control('jec_medium_posts_count_control', [
            'label'       => __('Number of Posts', 'jec-medium'),
            'description' => __('How many posts to display', 'jec-medium'),
            'section'     => 'jec_medium_settings',
            'settings'    => 'jec_medium_posts_count',
            'type'        => 'number',
            'priority'    => 20,
            'input_attrs' => [
                'min'  => 1,
                'max'  => 20,
                'step' => 1,
            ],
        ]);

        // Cache duration
        $wp_customize->add_setting('jec_medium_cache_duration', [
            'default'           => 3600,
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        ]);

        $wp_customize->add_control('jec_medium_cache_duration_control', [
            'label'       => __('Cache Duration (seconds)', 'jec-medium'),
            'description' => __('How long to cache Medium posts (3600 = 1 hour)', 'jec-medium'),
            'section'     => 'jec_medium_settings',
            'settings'    => 'jec_medium_cache_duration',
            'type'        => 'number',
            'priority'    => 30,
            'input_attrs' => [
                'min'  => 300,
                'max'  => 86400,
                'step' => 300,
            ],
        ]);

        // Show featured images
        $wp_customize->add_setting('jec_medium_show_images', [
            'default'           => true,
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => [__CLASS__, 'sanitize_checkbox'],
            'transport'         => 'refresh',
        ]);

        $wp_customize->add_control('jec_medium_show_images_control', [
            'label'    => __('Show Featured Images', 'jec-medium'),
            'section'  => 'jec_medium_settings',
            'settings' => 'jec_medium_show_images',
            'type'     => 'checkbox',
            'priority' => 40,
        ]);

        // Show categories
        $wp_customize->add_setting('jec_medium_show_categories', [
            'default'           => true,
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => [__CLASS__, 'sanitize_checkbox'],
            'transport'         => 'refresh',
        ]);

        $wp_customize->add_control('jec_medium_show_categories_control', [
            'label'    => __('Show Categories', 'jec-medium'),
            'section'  => 'jec_medium_settings',
            'settings' => 'jec_medium_show_categories',
            'type'     => 'checkbox',
            'priority' => 50,
        ]);

        // Show excerpt
        $wp_customize->add_setting('jec_medium_show_excerpt', [
            'default'           => true,
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => [__CLASS__, 'sanitize_checkbox'],
            'transport'         => 'refresh',
        ]);

        $wp_customize->add_control('jec_medium_show_excerpt_control', [
            'label'    => __('Show Excerpt', 'jec-medium'),
            'section'  => 'jec_medium_settings',
            'settings' => 'jec_medium_show_excerpt',
            'type'     => 'checkbox',
            'priority' => 60,
        ]);

        // Button color
        $wp_customize->add_setting('jec_medium_button_color', [
            'default'           => '#ffffff',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'refresh',
        ]);

        $wp_customize->add_control('jec_medium_button_color_control', [
            'label'    => __('Button Color', 'jec-medium'),
            'section'  => 'jec_medium_settings',
            'settings' => 'jec_medium_button_color',
            'type'     => 'color',
            'priority' => 70,
        ]);

        // Outside text color
        $wp_customize->add_setting('jec_medium_outside_text_color', [
            'default'           => '#ffffff',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'refresh',
        ]);

        $wp_customize->add_control('jec_medium_outside_text_color_control', [
            'label'    => __('Outside Text Color', 'jec-medium'),
            'section'  => 'jec_medium_settings',
            'settings' => 'jec_medium_outside_text_color',
            'type'     => 'color',
            'priority' => 80,
        ]);
    }

    /**
     * Sanitize checkbox values
     *
     * @param bool $checked
     * @return bool
     */
    public static function sanitize_checkbox($checked) {
        return (isset($checked) && $checked === true) ? true : false;
    }

    /**
     * Get Medium feed URL from theme mods or fallback to option
     *
     * @return string
     */
    public static function get_feed_url() {
        $url = get_theme_mod('jec_medium_feed_url', '');
        if (empty($url)) {
            // Fallback to old option for backwards compatibility
            $url = get_option('wmi_feed_url', '');
        }
        return $url;
    }

    /**
     * Get posts count setting
     *
     * @return int
     */
    public static function get_posts_count() {
        return absint(get_theme_mod('jec_medium_posts_count', 15));
    }

    /**
     * Migrate default posts count to 15 for existing installs that still use 10
     */
    public static function maybe_migrate_posts_count_default() {
        $migration_key = 'jec_medium_posts_count_migrated_1_0_9';
        if (get_option($migration_key)) {
            return;
        }

        $current_value = get_theme_mod('jec_medium_posts_count', null);
        if ($current_value === null || absint($current_value) === 10) {
            set_theme_mod('jec_medium_posts_count', 15);
        }

        update_option($migration_key, 1);
    }

    /**
     * Get cache duration setting
     *
     * @return int
     */
    public static function get_cache_duration() {
        return absint(get_theme_mod('jec_medium_cache_duration', 3600));
    }

    /**
     * Check if featured images should be shown
     *
     * @return bool
     */
    public static function show_images() {
        return (bool) get_theme_mod('jec_medium_show_images', true);
    }

    /**
     * Check if categories should be shown
     *
     * @return bool
     */
    public static function show_categories() {
        return (bool) get_theme_mod('jec_medium_show_categories', true);
    }

    /**
     * Check if excerpt should be shown
     *
     * @return bool
     */
    public static function show_excerpt() {
        return (bool) get_theme_mod('jec_medium_show_excerpt', true);
    }

    /**
     * Get carousel button color
     *
     * @return string
     */
    public static function get_button_color() {
        return (string) get_theme_mod('jec_medium_button_color', '#ffffff');
    }

    /**
     * Get outside text color
     *
     * @return string
     */
    public static function get_outside_text_color() {
        return (string) get_theme_mod('jec_medium_outside_text_color', '#ffffff');
    }
}
