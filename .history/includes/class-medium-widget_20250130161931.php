class MediumWidget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'medium_widget',
            __('Medium Latest Posts', 'text_domain'),
            ['description' => __('Displays the latest posts from a Medium profile.', 'text_domain')]
        );
    }

    public function widget($args, $instance) {
        $fetcher = new Medium_RSS_Fetcher();
        $posts = $fetcher->fetch_feed();

        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        include(plugin_dir_path(__FILE__) . '../templates/medium-widget-template.php');

        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Latest Medium Posts', 'text_domain');
        echo '<p><label for="' . esc_attr($this->get_field_id('title')) . '">' . __('Title:', 'text_domain') . '</label><input class="widefat" id="' . esc_attr($this->get_field_id('title')) . '" name="' . esc_attr($this->get_field_name('title')) . '" type="text" value="' . esc_attr($title) . '"></p>';
    }

    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';

        return $instance;
    }
}