<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register REST API route for searching posts.
 */
function csl_register_api_routes() {
    register_rest_route( 'crosslinker/v1', '/posts', [
        'methods'  => 'GET',
        'callback' => 'csl_handle_search',
        'args'     => [
            'q' => [
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
        'permission_callback' => '__return_true',
    ] );
}
add_action( 'rest_api_init', 'csl_register_api_routes' );

/**
 * Handle the search request.
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response
 */
function csl_handle_search( WP_REST_Request $request ) {
    $api_key_option = get_option( 'csl_api_key', '' );
    if ( ! empty( $api_key_option ) ) {
        $supplied_key = $request->get_header( 'X-API-KEY' );
        if ( $supplied_key !== $api_key_option ) {
            return new WP_REST_Response( [ 'error' => 'Unauthorized' ], 401 );
        }
    }

    $query = sanitize_text_field( $request['q'] );

    $cache_key = 'csl_' . md5( $query );
    $cached    = get_transient( $cache_key );
    if ( false !== $cached ) {
        return new WP_REST_Response( $cached );
    }

    $args = [
        's'              => $query,
        'post_status'    => 'publish',
        'posts_per_page' => 5,
    ];

    $wp_query = new WP_Query( $args );
    $results  = [];

    if ( $wp_query->have_posts() ) {
        foreach ( $wp_query->posts as $post ) {
            $results[] = [
                'title'   => get_the_title( $post ),
                'url'     => get_permalink( $post ),
                'excerpt' => wp_trim_words( $post->post_excerpt ? $post->post_excerpt : $post->post_content, 20, '...' ),
            ];
        }
    }

    set_transient( $cache_key, $results, 30 * MINUTE_IN_SECONDS );

    return new WP_REST_Response( $results );
}

