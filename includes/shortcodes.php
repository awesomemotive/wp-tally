<?php
/**
 * Shortcodes
 *
 * @package     WPTally\Shortcodes
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Tally Shortcode
 *
 * @since       1.0.0
 * @param       array $atts Shortcode attributes
 * @param       string $content
 * @return      string $return The Tally form and ouput
 */
function wptally_shortcode( $atts, $content = null ) {
    $username = ( isset( $_GET['wpusername'] ) && ! empty( $_GET['wpusername'] ) ? $_GET['wpusername'] : false );

    $search_field  = '<div class="tally-search-box">';
    $search_field .= '<form class="tally-search-form" method="get" action="">';
    $search_field .= '<input type="text" name="wpusername" class="tally-search-field" placeholder="' . sprintf( __( 'Enter your %s username', 'wp-tally' ), 'WordPress.org' ) . '" value="' . ( $username ? $username : '' ) . '" />';
    $search_field .= '<input type="submit" class="tally-search-submit" value="' . __( 'Search', 'wp-tally' ) . '" />';
    $search_field .= '</form>';
    $search_field .= '</div>';

    $results = '<div class="tally-search-results">';

    if( $username ) {

        if( isset( $_GET['force'] ) && $_GET['force'] == 'true' ) {
            delete_transient( 'wp-tally-user-' . $username );
        }

        $plugins = wptally_maybe_get_plugins( $username, ( isset( $_GET['force'] ) ? $_GET['force'] : false ) );

        if( is_wp_error( $plugins ) ) {
            $results .= __( 'An error occurred with the plugins API. Please try again later.', 'wp-tally' );
        } else {
            // How many plugins does the user have?
            $count = count( $plugins->plugins );
            $total_downloads = 0;
        
            if( $count == 0 ) {
                $results .= sprintf( __( 'No plugins found for %s!', 'wp-tally' ), $username );
            } else {
                foreach( $plugins->plugins as $plugin ) {
                    $rating = wptally_get_rating( $plugin->num_ratings, $plugin->ratings );

                    // Plugin row
                    $results .= '<div class="tally-plugin">';

                    // Content left
                    $results .= '<div class="tally-plugin-left">';

                    // Plugin title
                    $results .= '<a class="tally-plugin-title" href="http://wordpress.org/plugins/' . $plugin->slug . '" target="_blank">' . $plugin->name . '</a>';

                    // Plugin meta
                    $results .= '<div class="tally-plugin-meta">';
                    $results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">' . __( 'Version', 'wp-tally' ) . ':</span> ' . $plugin->version . '</span>';
                    $results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">' . __( 'Added', 'wp-tally' ) . ':</span> ' . date( 'd M, Y', strtotime( $plugin->added ) ) . '</span>';
                    $results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">' . __( 'Last Updated', 'wp-tally' ) . ':</span> ' . date( 'd M, Y', strtotime( $plugin->last_updated ) ) . '</span>';
                    $results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">' . __( 'Rating', 'wp-tally' ) . ':</span> ' . sprintf( __( '%s out of 5 stars', 'wp-tally' ), $rating );
                    $results .= '</span>'; 
                    $results .= '</div>';

                    // End content left
                    $results .= '</div>';

                    // Content right
                    $results .= '<div class="tally-plugin-right">';
                    $results .= '<div class="tally-plugin-downloads">' . number_format( $plugin->downloaded ) . '</div>';
                    $results .= '<div class="tally-plugin-downloads-title">' . __( 'Downloads', 'wp-tally' ) . '</div>';
                    $results .= '</div>';

                    // End plugin row
                    $results .= '</div>';

                    $total_downloads = $total_downloads + $plugin->downloaded;
                }

                // Totals row
                $results .= '<div class="tally-plugin">';
                $results .= '<div class="tally-plugin-left">';
                $results .= '</div>';
                $results .= '<div class="tally-plugin-right">';
                $results .= '<div class="tally-plugin-downloads">' . number_format( $total_downloads ) . '</div>';
                $results .= '<div class="tally-plugin-downloads-title">' . __( 'Total Downloads', 'wp-tally' ) . '</div>';
                $results .= '</div>';
            }
        }
    }

    $results .= '</div>';

    return $search_field . $results;
}
add_shortcode( 'tally', 'wptally_shortcode' );
