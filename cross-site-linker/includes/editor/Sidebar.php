<?php

namespace CrossSiteLinker\Editor;

class Sidebar
{
    public function __construct()
    {
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets()
    {
        wp_enqueue_script(
            'cross-site-linker-sidebar',
            CROSS_SITE_LINKER_URL . 'assets/js/sidebar.js',
            ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-api-fetch', 'wp-dom-ready'],
            '1.0.1',
            true
        );

        wp_localize_script('cross-site-linker-sidebar', 'crossSiteLinker', [
            'sites' => get_option('cross_site_linker_sites', []),
            'home_url' => get_home_url(),
        ]);
    }
}
