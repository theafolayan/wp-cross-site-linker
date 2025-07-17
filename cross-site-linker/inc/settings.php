<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register plugin settings.
 */
function csl_register_settings() {
    register_setting( 'csl_options', 'csl_sites' );
    register_setting( 'csl_options', 'csl_api_key' );

    add_settings_section( 'csl_main', __( 'Cross-Site Linker Settings', 'csl' ), '__return_false', 'csl' );

    add_settings_field( 'csl_api_key', __( 'API Key', 'csl' ), 'csl_api_key_field', 'csl', 'csl_main' );
    add_settings_field( 'csl_sites', __( 'Sites', 'csl' ), 'csl_sites_field', 'csl', 'csl_main' );
}
add_action( 'admin_init', 'csl_register_settings' );

/**
 * Render API key field.
 */
function csl_api_key_field() {
    $value = get_option( 'csl_api_key', '' );
    echo '<input type="text" name="csl_api_key" value="' . esc_attr( $value ) . '" class="regular-text" />';
    echo '<p class="description">' . esc_html__( 'Optional API key for protecting the public API.', 'csl' ) . '</p>';
}

/**
 * Render sites field.
 */
function csl_sites_field() {
    $sites = get_option( 'csl_sites', [] );
    if ( ! is_array( $sites ) ) {
        $sites = [];
    }
    echo '<div id="csl-sites-wrapper">';
    foreach ( $sites as $index => $site ) {
        echo '<div class="csl-site">';
        echo '<input type="text" name="csl_sites[' . esc_attr( $index ) . '][name]" value="' . esc_attr( $site['name'] ) . '" placeholder="' . esc_attr__( 'Site Name', 'csl' ) . '" /> ';
        echo '<input type="text" name="csl_sites[' . esc_attr( $index ) . '][url]" value="' . esc_attr( $site['url'] ) . '" placeholder="https://example.com" /> ';
        echo '<input type="text" name="csl_sites[' . esc_attr( $index ) . '][key]" value="' . esc_attr( $site['key'] ) . '" placeholder="' . esc_attr__( 'API Key', 'csl' ) . '" /> ';
        echo '<button class="button csl-remove-site" type="button">' . esc_html__( 'Remove', 'csl' ) . '</button>';
        echo '</div>';
    }
    echo '</div>';
    echo '<button class="button" id="csl-add-site" type="button">' . esc_html__( 'Add Site', 'csl' ) . '</button>';
    echo '<p class="description">' . esc_html__( 'Manage the list of other WordPress sites for cross-linking.', 'csl' ) . '</p>';
}

/**
 * Add settings page to admin menu.
 */
function csl_add_settings_page() {
    add_options_page( __( 'Cross-Site Linker', 'csl' ), __( 'Cross-Site Linker', 'csl' ), 'manage_options', 'csl', 'csl_render_settings_page' );
}
add_action( 'admin_menu', 'csl_add_settings_page' );

/**
 * Render settings page.
 */
function csl_render_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Cross-Site Linker Settings', 'csl' ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'csl_options' );
            do_settings_sections( 'csl' );
            submit_button();
            ?>
        </form>
    </div>
    <script>
    (function($){
        $('#csl-add-site').on('click', function(){
            var index = $('#csl-sites-wrapper .csl-site').length;
            $('#csl-sites-wrapper').append('<div class="csl-site">' +
                '<input type="text" name="csl_sites['+index+'][name]" placeholder="Site Name" /> ' +
                '<input type="text" name="csl_sites['+index+'][url]" placeholder="https://example.com" /> ' +
                '<input type="text" name="csl_sites['+index+'][key]" placeholder="API Key" /> ' +
                '<button class="button csl-remove-site" type="button">Remove</button>' +
            '</div>');
        });
        $(document).on('click', '.csl-remove-site', function(){
            $(this).parent('.csl-site').remove();
        });
    })(jQuery);
    </script>
    <?php
}

