<?php

namespace CrossSiteLinker\Editor;

class ClassicEditor
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
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
}
