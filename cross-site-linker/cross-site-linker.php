<?php
/**
 * Plugin Name: Cross-Site Linker
 * Description: A plugin to cross-link posts between WordPress sites.
 * Version: 1.0.0
 * Author: Jules
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CROSS_SITE_LINKER_PATH', plugin_dir_path(__FILE__));
define('CROSS_SITE_LINKER_URL', plugin_dir_url(__FILE__));

// Manually include the files
require_once CROSS_SITE_LINKER_PATH . 'includes/Plugin.php';
require_once CROSS_SITE_LINKER_PATH . 'includes/api/Provider.php';
require_once CROSS_SITE_LINKER_PATH . 'includes/admin/Settings.php';
require_once CROSS_SITE_LINKER_PATH . 'includes/editor/Sidebar.php';
require_once CROSS_SITE_LINKER_PATH . 'includes/editor/LinkInserter.php';
require_once CROSS_SITE_LINKER_PATH . 'includes/editor/ClassicEditor.php';


// Initialize the plugin
add_action('plugins_loaded', function () {
    \CrossSiteLinker\Plugin::instance();

    if (!function_exists('is_plugin_active') || !is_plugin_active('classic-editor/classic-editor.php')) {
        new \CrossSiteLinker\Editor\Sidebar();
    } else {
        new \CrossSiteLinker\Editor\ClassicEditor();
    }
});
