<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Fetch suggestions from all configured sites.
 *
 * @param string $keyword Keyword to search for.
 * @return array Array of suggestions.
 */
function csl_get_cross_site_suggestions( $keyword ) {
    $sites   = get_option( 'csl_sites', [] );
    $results = [];

    if ( empty( $sites ) ) {
        return $results;
    }

    $requests = [];
    foreach ( $sites as $site ) {
        if ( empty( $site['url'] ) ) {
            continue;
        }
        $url = trailingslashit( $site['url'] ) . 'wp-json/crosslinker/v1/posts?q=' . rawurlencode( $keyword );
        $args = [
            'timeout' => 5,
        ];
        if ( ! empty( $site['key'] ) ) {
            $args['headers'] = [ 'X-API-KEY' => $site['key'] ];
        }
        $requests[] = [ 'url' => $url, 'args' => $args ];
    }

    // Perform requests sequentially and merge results. To keep it simple we are not using async libraries.
    foreach ( $requests as $req ) {
        $cache_key = 'csl_remote_' . md5( $req['url'] );
        $data      = get_transient( $cache_key );

        if ( false === $data ) {
            $response = wp_remote_get( $req['url'], $req['args'] );
            if ( is_wp_error( $response ) ) {
                continue;
            }
            $code = wp_remote_retrieve_response_code( $response );
            if ( 200 !== $code ) {
                continue;
            }
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body, true );
            if ( is_array( $data ) ) {
                set_transient( $cache_key, $data, 30 * MINUTE_IN_SECONDS );
            } else {
                $data = [];
            }
        }

        if ( is_array( $data ) ) {
            foreach ( $data as $item ) {
                // Add site information.
                $item['site'] = parse_url( $req['url'], PHP_URL_HOST );
                $results[] = $item;
            }
        }
    }

    return $results;
}

