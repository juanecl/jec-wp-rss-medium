<?php

class MediumRssFetcher {
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
        $xml = simplexml_load_string($body);

        if ($xml === false) {
            return $posts;
        }

        foreach ($xml->channel->item as $item) {
            $posts[] = [
                'title' => (string) $item->title,
                'link' => (string) $item->link,
                'pubDate' => (string) $item->pubDate,
                'description' => (string) $item->description,
            ];
        }

        return $posts;
    }
}