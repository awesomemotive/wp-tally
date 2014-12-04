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

        $results .= '<div class="tally-search-results-plugins">';
        $results .= '<div class="tally-search-results-header">Plugins</div>';

        if( is_wp_error( $plugins ) ) {
            $results .= '<div class="tally-search-error">An error occurred with the plugins API. Please try again later.</div>';
        } else {
            // How many plugins does the user have?
            $count = count( $plugins->plugins );
            $total_downloads = 0;
            $ratings_count = 0;
            $ratings_total = 0;
        
            if( $count == 0 ) {
                $results .= '<div class="tally-search-error">No plugins found for ' . $username . '!</div>';
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
                    $results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">Rating:</span> ' . ( empty( $rating ) ? 'not yet rated' : $rating . ' out of 5 stars</span>' );
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
                    
                    if( ! empty( $rating ) ) {
                        $ratings_total += $rating;
                        $ratings_count++;
                    }
                }
                
                $plugins_total = number_format( $count );
                $plugins_reference = $plugins_total == 1 ? 'plugin' : 'plugins';
                $cumulative_rating = $ratings_total / $ratings_count;

                // Totals row
                $results .= '<div class="tally-plugin">';
                $results .= '<div class="tally-plugin-left">';
                    $results .= '<div class="tally-info">';
                        $results .= '<span class="tally-count-rating">You have <span class="tally-count">' . $plugins_total . '</span> ' . $plugins_reference . ( empty( $ratings_count ) ? ' with no ratings.' : ' with a cumulative rating of <span class="tally-rating">' . number_format( $cumulative_rating, 2, '.', '' ) . '</span> out of 5 stars.</span>' );
                    $results .= '</div>';
                    $results .= '<div class="tally-share">';
                        $results .= '<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://wptally.com/?wpusername=' . esc_attr( $username ) . '" data-text="My plugins on WordPress.org have a total of ' . number_format( $total_downloads ) . ' downloads. Check it out on wptally.com">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+\'://platform.twitter.com/widgets.js\';fjs.parentNode.insertBefore(js,fjs);}}(document, \'script\', \'twitter-wjs\');</script>';
                    $results .= '</div>';
                $results .= '</div>';
                $results .= '<div class="tally-plugin-right">';
                $results .= '<div class="tally-plugin-downloads">' . number_format( $total_downloads ) . '</div>';
                $results .= '<div class="tally-plugin-downloads-title">Total Downloads</div>';
                $results .= '</div>';
                $results .= '</div>';
            }
        }

        $results .= '</div>';
        
        $themes = wptally_maybe_get_themes( $username, ( isset( $_GET['force'] ) ? $_GET['force'] : false ) );

        $results .= '<div class="tally-search-results-themes">';
        $results .= '<div class="tally-search-results-header">Themes</div>';

        if( is_wp_error( $themes ) ) {
            $results .= '<div class="tally-search-error">An error occurred with the themes API. Please try again later.</div>';
        } else {
            // How many themes does the user have?
            $count = count( $themes );
            $total_downloads = 0;
            $ratings_count = 0;
            $ratings_total = 0;
        
            if( $count == 0 ) {
                $results .= '<div class="tally-search-error">No themes found for ' . $username . '!</div>';
            } else {
                foreach( $themes as $theme ) {
                    $rating = wptally_get_rating( $theme->num_ratings, $theme->rating );

                    // Theme row
                    $results .= '<div class="tally-plugin">';

                    // Content left
                    $results .= '<div class="tally-plugin-left">';

                    // Theme title
                    $results .= '<a class="tally-plugin-title" href="http://wordpress.org/themes/' . $theme->slug . '" target="_blank">' . $theme->name . '</a>';

                    // Theme meta
                    $results .= '<div class="tally-plugin-meta">';
                    $results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">Ver:</span> ' . $theme->version . '</span>';
                    $results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">Last Updated:</span> ' . date( 'd M, Y', strtotime( $theme->last_updated ) ) . '</span>';
                    $results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">Rating:</span> ' . ( empty( $rating ) ? 'not yet rated' : $rating . ' out of 5 stars</span>' );
                    $results .= '</div>';

                    // End content left
                    $results .= '</div>';

                    // Content right
                    $results .= '<div class="tally-plugin-right">';
                    $results .= '<div class="tally-plugin-downloads">' . number_format( $theme->downloaded ) . '</div>';
                    $results .= '<div class="tally-plugin-downloads-title">Downloads</div>';
                    $results .= '</div>';

                    // End theme row
                    $results .= '</div>';

                    $total_downloads = $total_downloads + $theme->downloaded;
                    
                    if( ! empty( $rating ) ) {
                        $ratings_total += $rating;
                        $ratings_count++;
                    }
                }
                
                $themes_total = number_format( $count );
                $themes_reference = $themes_total == 1 ? 'theme' : 'themes';
                $cumulative_rating = $ratings_total / $ratings_count;

                // Totals row
                $results .= '<div class="tally-plugin">';
                $results .= '<div class="tally-plugin-left">';
                    $results .= '<div class="tally-info">';
                        $results .= '<span class="tally-count-rating">You have <span class="tally-count">' . $themes_total . '</span> ' . $themes_reference . ( empty( $ratings_count ) ? ' with no ratings.' : ' with a cumulative rating of <span class="tally-rating">' . number_format( $cumulative_rating, 2, '.', '' ) . '</span> out of 5 stars.</span>' );
                    $results .= '</div>';
                    $results .= '<div class="tally-share">';
                        $results .= '<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://wptally.com/?wpusername=' . esc_attr( $username ) . '" data-text="My themes on WordPress.org have a total of ' . number_format( $total_downloads ) . ' downloads. Check it out on wptally.com">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+\'://platform.twitter.com/widgets.js\';fjs.parentNode.insertBefore(js,fjs);}}(document, \'script\', \'twitter-wjs\');</script>';
                    $results .= '</div>';
                $results .= '</div>';
                $results .= '<div class="tally-plugin-right">';
                $results .= '<div class="tally-plugin-downloads">' . number_format( $total_downloads ) . '</div>';
                $results .= '<div class="tally-plugin-downloads-title">Total Downloads</div>';
                $results .= '</div>';
                $results .= '</div>';
            }
        }

        $results .= '</div>';
    }

    $results .= '</div>';

    return $search_field . $results;
}
add_shortcode( 'tally', 'wptally_shortcode' );
