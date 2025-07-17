<?php

namespace CrossSiteLinker\Editor;

class LinkInserter
{
    public function __construct()
    {
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets()
    {
        wp_enqueue_script(
            'cross-site-linker-link-inserter',
            CROSS_SITE_LINKER_URL . 'assets/js/link-inserter.js',
            ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-api-fetch', 'wp-rich-text'],
            '1.0.2',
            true
        );
    }
}
