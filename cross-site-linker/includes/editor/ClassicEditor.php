<?php

namespace CrossSiteLinker\Editor;

class ClassicEditor
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('wp_link_query', [$this, 'extend_link_query'], 10, 2);
    }

    public function add_meta_box()
    {
        add_meta_box(
            'cross-site-linker-meta-box',
            'Cross-Site Suggestions',
            [$this, 'render_meta_box'],
            'post',
            'side'
        );
    }

    public function render_meta_box()
    {
        ?>
        <div id="cross-site-linker-meta-box-content">
            <button type="button" class="button" id="refresh-suggestions">Refresh Suggestions</button>
            <hr />
            <div id="suggestions-list"></div>
        </div>
        <?php
    }

    public function enqueue_assets($hook)
    {
        if ('post.php' !== $hook && 'post-new.php' !== $hook) {
            return;
        }

        wp_enqueue_script(
            'cross-site-linker-classic-editor',
            CROSS_SITE_LINKER_URL . 'assets/js/classic-editor.js',
            ['jquery', 'wp-api-fetch'],
            '1.0.2',
            true
        );

        wp_localize_script('cross-site-linker-classic-editor', 'crossSiteLinker', [
            'sites' => get_option('cross_site_linker_sites', []),
            'home_url' => get_home_url(),
            'post_title' => get_the_title(),
        ]);
    }

    /**
     * Append cross-site results to the classic editor link dialog search.
     *
     * @param array $results Existing results returned by WordPress.
     * @param array $query   The WP_Query arguments used for the search.
     * @return array Modified results including remote posts.
     */
    public function extend_link_query($results, $query)
    {
        if (empty($query['s'])) {
            return $results;
        }

        $sites    = get_option('cross_site_linker_sites', []);
        $home_url = get_home_url();
        foreach ($sites as $site) {
            if (empty($site['url']) || $site['url'] === $home_url) {
                continue;
            }

            $url = trailingslashit($site['url']) . 'wp-json/crosslinker/v1/posts?q=' . urlencode($query['s']);

            $args = [
                'headers' => [],
                'timeout' => 5,
            ];

            if (!empty($site['api_key'])) {
                $args['headers']['X-API-KEY'] = $site['api_key'];
            }

            $response = wp_remote_get($url, $args);
            if (is_wp_error($response)) {
                continue;
            }

            $body  = wp_remote_retrieve_body($response);
            $posts = json_decode($body, true);
            if (!is_array($posts)) {
                continue;
            }

            foreach ($posts as $post) {
                $results[] = [
                    'ID'        => 0,
                    'title'     => isset($post['title']) ? $post['title'] : '',
                    'permalink' => isset($post['url']) ? $post['url'] : '',
                    'info'      => $site['name'] ?? '',
                ];
            }
        }

        return $results;
    }
}
