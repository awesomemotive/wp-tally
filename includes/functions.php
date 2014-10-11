<?php
/**
 * Helper functions
 *
 * @package     WPTally\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Get a users plugin data
 *
 * @since       1.0.0
 * @param       string $username The user to check
 * @param       bool $force Forcibly remove any existing transient and requery
 * @return      object $plugins The users plugins
 */
function wptally_maybe_get_plugins( $username = false, $force = false ) {
    if( $username ) {
        if( ! function_exists( 'plugins_api' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        }

        if( $force ) {
            delete_transient( 'wp-tally-user-' . $username );
        }

        if( ! $plugins = get_transient( 'wp-tally-user-' . $username ) ) {
            $plugins = plugins_api( 'query_plugins',
                array(
                    'author'    => $username,
                    'per_page'  => 999,
                    'fields'    => array(
                        'downloaded'        => true,
                        'description'       => false,
                        'short_description' => false,
                        'donate_link'       => false,
                        'tags'              => false,
                        'sections'          => false,
                        'added'             => true,
                        'last_updated'      => true
                    )
                )
            );

            set_transient( 'wp-tally-user-' . $username, $plugins, 60 * 60 );
        }
    } else {
        $plugins = false;
    }

    return $plugins;
}


/**
 * Get the actual rating for a given plugin
 *
 * @since       1.0.0
 * @param       int $num_ratings The total number of ratings
 * @param       array $ratings The actual rating counts
 * @return      int $rating The calculated rating
 */
function wptally_get_rating( $num_ratings, $ratings ) {
    if( $num_ratings > 0 ) {
        $rating = ( $ratings[5] > 0 ? $ratings[5] * 5 : 0 );
        $rating = $rating + ( $ratings[4] > 0 ? $ratings[4] * 4 : 0 );
        $rating = $rating + ( $ratings[3] > 0 ? $ratings[3] * 3 : 0 );
        $rating = $rating + ( $ratings[2] > 0 ? $ratings[2] * 2 : 0 );
        $rating = $rating + ( $ratings[1] > 0 ? $ratings[1] * 1 : 0 );
        $rating = round( $rating / $num_ratings, 1 );
    } else {
        $rating = 0;
    }

    return $rating;
}
