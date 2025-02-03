<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Medium_RSS_Fetcher {
    private $feed_url;

    public function __construct($feed_url) {
        $this->feed_url = $feed_url;
    }

    public function fetch_feed() {
        $response = wp_remote_get($this->feed_url);

        if (is_wp_error($response)) {
            error_log('Error fetching feed: ' . $response->get_error_message());
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        $this->parse_feed($body);
    }

    private function parse_feed($body) {
        $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            error_log('Error parsing feed: Invalid XML');
            return;
        }

        $items = $xml->channel->item;
        $count = 0;

        foreach ($items as $item) {
            if ($count >= 10) {
                break;
            }

            $namespaces = $item->getNameSpaces(true);
            $content_encoded = $item->children($namespaces['content'])->encoded;
            $content = (string) $content_encoded;
            $image = $this->extract_image($content);
            $description = $this->create_description($content);

            $categories = [];
            foreach ($item->category as $category) {
                $categories[] = (string) $category;
            }

            $post_data = [
                'title' => (string) $item->title,
                'link' => (string) $item->link,
                'pubDate' => (string) $item->pubDate,
                'description' => $description,
                'content' => $content,
                'image' => $image,
                'categories' => $categories,
            ];

            if (!$this->post_exists($post_data['title'])) {
                $this->create_wp_post($post_data);
            }

            $count++;
        }
    }

    private function extract_image($content) {
        $default_image = 'https://png.pngtree.com/background/20230616/original/pngtree-faceted-abstract-background-in-3d-with-shimmering-iridescent-metallic-texture-of-picture-image_3653595.jpg';
        preg_match('/<figure.*?>.*?<img.*?src=["\'](.*?)["\'].*?>.*?<\/figure>/is', $content, $matches);

        if (!empty($matches)) {
            return $matches[1];
        }

        return $default_image;
    }

    private function create_description($content) {
        $content_without_media = preg_replace('/<figure.*?>.*?<\/figure>|<img.*?>|<video.*?>.*?<\/video>/is', '', $content);
        $excerpt = strip_tags($content_without_media);
        return $this->truncate_text($excerpt, 300);
    }
    
    private function truncate_text($text, $length) {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
    
        $truncated = mb_substr($text, 0, $length);
        $last_space = mb_strrpos($truncated, ' ');
    
        if ($last_space !== false) {
            $truncated = mb_substr($truncated, 0, $last_space);
        }
    
        return $truncated . __('...', 'jec-medium');
    }

    private function post_exists($title) {
        global $wpdb;
        $query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'post'", $title);
        return $wpdb->get_var($query);
    }

    private function create_wp_post($post_data) {
        $excerpt = $this->create_description($post_data['content']);
        
        $post_id = wp_insert_post([
            'post_title' => $post_data['title'],
            'post_content' => $post_data['content'], // Use the full content
            'post_excerpt' => $excerpt, // Use the first 200 characters without media
            'post_status' => 'publish',
            'post_type' => 'post',
            'meta_input' => [
                'medium_post_link' => $post_data['link'],
                'medium_post_image' => $post_data['image'],
                'medium_post_pubDate' => $post_data['pubDate'],
            ],
        ]);

        if (is_wp_error($post_id)) {
            error_log('Error creating post: ' . $post_id->get_error_message());
            return;
        }

        // Set the featured image if available
        $this->set_featured_image($post_id, $post_data['image']);
        // Set the categories
        $this->set_post_categories($post_id, $post_data['categories']);
    }

    private function set_featured_image($post_id, $image_url) {
        $upload_dir = wp_upload_dir();
        $image_data = @file_get_contents($image_url);
        if ($image_data === false) {
            error_log('Error fetching image: ' . $image_url);
            return;
        }

        $filename = basename($image_url);
        if (wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        if (file_put_contents($file, $image_data) === false) {
            error_log('Error saving image: ' . $file);
            return;
        }

        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = [
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        ];
        $attach_id = wp_insert_attachment($attachment, $file, $post_id);
        if (is_wp_error($attach_id)) {
            error_log('Error creating attachment: ' . $attach_id->get_error_message());
            return;
        }

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        wp_update_attachment_metadata($attach_id, $attach_data);
        set_post_thumbnail($post_id, $attach_id);
    }

    private function set_post_categories($post_id, $categories) {
        $default_categories = ['blog', 'medium'];
        $categories = array_merge($categories, $default_categories);

        $category_ids = [];
        foreach ($categories as $category_name) {
            $category = get_term_by('name', $category_name, 'category');
            if (!$category) {
                $category = wp_insert_term($category_name, 'category');
                if (is_wp_error($category)) {
                    error_log('Error creating category: ' . $category->get_error_message());
                    continue;
                }
                $category_ids[] = $category['term_id'];
            } else {
                $category_ids[] = $category->term_id;
            }
        }
        wp_set_post_categories($post_id, $category_ids);
    }
}