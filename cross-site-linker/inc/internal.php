<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register internal REST route to fetch cross-site suggestions for the editor.
 */
function csl_register_internal_route() {
    register_rest_route( 'csl/v1', '/suggestions', [
        'methods'             => 'GET',
        'callback'            => 'csl_handle_suggestions_request',
        'permission_callback' => function() {
            return current_user_can( 'edit_posts' );
        },
        'args' => [
            'title' => [
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ] );
}
add_action( 'rest_api_init', 'csl_register_internal_route' );

/**
 * Handle suggestions request.
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response
 */
function csl_handle_suggestions_request( WP_REST_Request $request ) {
    $title     = sanitize_text_field( $request['title'] );
    $results   = csl_get_cross_site_suggestions( $title );

    // Filter out results pointing back to the current site.
    $home = parse_url( home_url(), PHP_URL_HOST );
    $results = array_filter( $results, function( $item ) use ( $home ) {
        return isset( $item['url'] ) && parse_url( $item['url'], PHP_URL_HOST ) !== $home;
    } );

    return new WP_REST_Response( array_values( $results ) );
}

