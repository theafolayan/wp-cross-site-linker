<?php

namespace CrossSiteLinker\Api;

class Provider
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        register_rest_route('crosslinker/v1', '/posts', [
            'methods' => 'GET',
            'callback' => [$this, 'get_posts'],
            'permission_callback' => [$this, 'check_permission'],
        ]);
    }

    public function get_posts($request)
    {
        $keyword = $request->get_param('q');

        if (empty($keyword)) {
            return new \WP_Error('missing_keyword', 'Missing keyword', ['status' => 400]);
        }

        $transient_key = 'csl_posts_' . sanitize_title_with_dashes($keyword);
        $cached_posts = get_transient($transient_key);

        if (false !== $cached_posts) {
            return new \WP_REST_Response($cached_posts, 200);
        }

        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            's' => $keyword,
        ];

        $query = new \WP_Query($args);

        $posts = [];
        foreach ($query->posts as $post) {
            $posts[] = [
                'title' => $post->post_title,
                'url' => get_permalink($post->ID),
                'excerpt' => get_the_excerpt($post->ID),
            ];
        }

        set_transient($transient_key, $posts, 30 * MINUTE_IN_SECONDS);

        return new \WP_REST_Response($posts, 200);
    }

    public function check_permission($request)
    {
        $api_key = get_option('cross_site_linker_api_key');

        if (empty($api_key)) {
            return true;
        }

        $request_api_key = $request->get_header('X-API-KEY');

        if ($api_key === $request_api_key) {
            return true;
        }

        return new \WP_Error('invalid_api_key', 'Invalid API key', ['status' => 401]);
    }
}
