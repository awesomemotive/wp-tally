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
    $search_field .= '<input type="text" name="wpusername" class="tally-search-field" placeholder="Enter your WordPress.org username" value="' . ( $username ? $username : '' ) . '" />';
    $search_field .= '<input type="submit" class="tally-search-submit" value="Search" />';
    $search_field .= '</form>';
    $search_field .= '</div>';

    $results = '<div class="tally-search-results">';

    if( $username ) {

        if( isset( $_GET['force'] ) && $_GET['force'] == 'true' ) {
            delete_transient( 'wp-tally-user-' . $username );
        }

        $plugins = wptally_maybe_get_plugins( $username, ( isset( $_GET['force'] ) ? $_GET['force'] : false ) );

        if( is_wp_error( $plugins ) ) {
            $results .= 'An error occurred with the plugins API. Please try again later.';
        } else {
            // How many plugins does the user have?
            $count = count( $plugins->plugins );
            $total_downloads = 0;
        
            if( $count == 0 ) {
                $results .= 'No plugins found for ' . $username . '!';
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
                    $results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">Ver:</span> ' . $plugin->version . '</span>';
                    $results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">Added:</span> ' . date( 'd M, Y', strtotime( $plugin->added ) ) . '</span>';
                    $results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">Last Updated:</span> ' . date( 'd M, Y', strtotime( $plugin->last_updated ) ) . '</span>';
                    $results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">Rating:</span> ' . $rating . ' out of 5 stars</span>';
                    $results .= '</div>';

                    // End content left
                    $results .= '</div>';

                    // Content right
                    $results .= '<div class="tally-plugin-right">';
                    $results .= '<div class="tally-plugin-downloads">' . number_format( $plugin->downloaded ) . '</div>';
                    $results .= '<div class="tally-plugin-downloads-title">Downloads</div>';
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
                $results .= '<div class="tally-plugin-downloads-title">Total Downloads</div>';
                $results .= '<div class=tally-share">';
                    $results .= '<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://wptally.com/?wpusername=' . esc_attr( $username ) . '" data-text="My plugins on WordPress.org have a total of ' . number_format( $total_downloads ) . ' downloads. Check it out on wptally.com">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+\'://platform.twitter.com/widgets.js\';fjs.parentNode.insertBefore(js,fjs);}}(document, \'script\', \'twitter-wjs\');</script>';
                $results .= '</div>';
                $results .= '</div>';
            }
        }
    }

    $results .= '</div>';

    return $search_field . $results;
}
add_shortcode( 'tally', 'wptally_shortcode' );
