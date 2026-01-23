<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MediumWidget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'medium_widget',
            __('Medium Latest Posts', 'jec-medium'),
            ['description' => __('Displays the latest posts from a Medium profile.', 'jec-medium')]
        );
    }

    public function widget($args, $instance) {
        $feed_url = Medium_Customizer::get_feed_url();
        $fetcher = new Medium_RSS_Fetcher($feed_url);
        $posts = $fetcher->fetch_feed();

        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        if (!empty($posts)) {
            include(plugin_dir_path(__FILE__) . '../templates/medium-feed-template.php');
        } else {
            echo '<p>' . __('No posts found.', 'jec-medium') . '</p>';
        }

        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Latest Medium Posts', 'jec-medium');
        echo '<p><label for="' . esc_attr($this->get_field_id('title')) . '">' . __('Title:', 'jec-medium') . '</label><input class="widefat" id="' . esc_attr($this->get_field_id('title')) . '" name="' . esc_attr($this->get_field_name('title')) . '" type="text" value="' . esc_attr($title) . '"></p>';
    }

    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';

        return $instance;
    }
}