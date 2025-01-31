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
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        return $this->parse_feed($body);
    }

    private function parse_feed($body) {
        $posts = [];
        $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            return $posts;
        }

        $items = $xml->channel->item;
        $count = 0;

        foreach ($items as $item) {
            if ($count >= 9) {
                break;
            }

            $namespaces = $item->getNameSpaces(true);
            $content_encoded = $item->children($namespaces['content'])->encoded;
            $content = (string) $content_encoded;
            $image = $this->extract_image($content);
            $description = $this->create_description($content);

            $posts[] = [
                'title' => (string) $item->title,
                'link' => (string) $item->link,
                'pubDate' => (string) $item->pubDate,
                'description' => $description,
                'image' => $image,
            ];

            $count++;
        }

        return $posts;
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
        $content_without_figure = preg_replace('/<figure.*?>.*?<\/figure>/is', '', $content);
        $excerpt = strip_tags($content_without_figure);
        return mb_strimwidth($excerpt, 0, 200, __('...', 'jec-medium'));
    }
}