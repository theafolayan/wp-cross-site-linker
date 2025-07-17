<?php
/**
 * Plugin Name: CrossSiteLinker
 * Description: Cross links posts across multiple WordPress sites. Provides API endpoint and Gutenberg UI for suggestions.
 * Version: 0.1.0
 * Author: Theafolayan
 * License: GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'CSL_VERSION', '0.1.0' );
define( 'CSL_PATH', plugin_dir_path( __FILE__ ) );
define( 'CSL_URL', plugin_dir_url( __FILE__ ) );

// Include required files.
require_once CSL_PATH . 'inc/provider.php';
require_once CSL_PATH . 'inc/settings.php';
require_once CSL_PATH . 'inc/client.php';
require_once CSL_PATH . 'inc/internal.php';

/**
 * Enqueue scripts for Gutenberg editor.
 */
function csl_enqueue_editor_assets() {
    wp_enqueue_script(
        'csl-editor',
        CSL_URL . 'js/editor.js',
        [ 'wp-edit-post', 'wp-data', 'wp-components', 'wp-element', 'wp-i18n', 'wp-api-fetch' ],
        CSL_VERSION,
        true
    );
    wp_enqueue_script(
        'csl-contextual',
        CSL_URL . 'js/contextual.js',
        [ 'wp-edit-post', 'wp-data', 'wp-components', 'wp-element', 'wp-i18n', 'wp-api-fetch' ],
        CSL_VERSION,
        true
    );
}
add_action( 'enqueue_block_editor_assets', 'csl_enqueue_editor_assets' );

