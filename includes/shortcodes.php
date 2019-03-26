<?php
/**
 * Shortcodes
 *
 * @package     WPTally\Shortcodes
 * @since       1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Tally Shortcode
 *
 * @since       1.0.0
 * @param       array $atts Shortcode attributes
 * @param       string $content
 * @return      string $return The Tally form and ouput
 */
function wptally_shortcode( $atts, $content = null ) {
	$username          = ( isset( $_GET['wpusername'] ) && ! empty( $_GET['wpusername'] ) ? $_GET['wpusername'] : false );
	$active            = ( isset( $_GET['active'] ) && ! empty( $_GET['active'] ) && $_GET['active'] == 'themes' ? 'themes' : 'plugins' );
	$theme_visibility  = ( $active == 'themes' ? '' : ' style="display: none;"' );
	$plugin_visibility = ( $active == 'plugins' ? '' : ' style="display: none;"' );
	$order_by          = ( isset( $_GET['by'] ) && $_GET['by'] == 'downloads' ? 'downloaded' : 'name' );
	$sort              = ( isset( $_GET['dir'] ) && strtolower( $_GET['dir'] ) == 'desc' ? 'desc' : 'asc' );

	$search_field  = '<div class="tally-search-box">';
	$search_field .= '<form class="tally-search-form" method="get" action="">';
	$search_field .= '<input type="text" name="wpusername" class="tally-search-field" placeholder="Enter your WordPress.org username" value="' . ( $username ? $username : '' ) . '" />';
	$search_field .= '<input type="submit" class="tally-search-submit" value="Search" />';
	$search_field .= '</form>';
	$search_field .= '</div>';

	$results = '<div class="tally-search-results" id="search-results">';

	if ( $username ) {
		$lookup_count = get_option( 'wptally_lookups' );
		$lookup_count = $lookup_count ? $lookup_count + 1 : 1;
		update_option( 'wptally_lookups', $lookup_count );

		if ( isset( $_GET['force'] ) && $_GET['force'] == 'true' ) {
			delete_transient( 'wp-tally-user-' . $username );
		}

		$plugins = wptally_maybe_get_plugins( $username, ( isset( $_GET['force'] ) ? $_GET['force'] : false ) );

		$results .= '<div class="tally-search-results-wrapper">';
		$results .= '<a class="tally-search-results-plugins-header' . ( $active == 'plugins' ? ' active' : '' ) . '">Plugins</a>';
		$results .= '<a class="tally-search-results-themes-header' . ( $active == 'themes' ? ' active' : '' ) . '">Themes</a>';

		$results .= '<div class="tally-search-results-sort">';
			$results .= '<div class="tally-search-results-sort-by">';
				$results .= '<span class="tally-search-results-sort-title">Order By: </span>';
				$results .= '<a href="' . add_query_arg( 'by', 'name' ) . '#search-results"' . ( $order_by == 'name' ? ' class="active"' : '' ) . '>Name</a>';
				$results .= ' / ';
				$results .= '<a href="' . add_query_arg( 'by', 'downloads' ) . '#search-results"' . ( $order_by == 'downloaded' ? ' class="active"' : '' ) . '>Downloads</a>';
			$results .= '</div>';
			$results .= '<div class="tally-search-results-order">';
				$results .= '<span class="tally-search-results-sort-title">Sort: </span>';
				$results .= '<a href="' . add_query_arg( 'dir', 'asc' ) . '#search-results"' . ( $sort == 'asc' ? ' class="active"' : '' ) . '>ASC</a>';
				$results .= ' / ';
				$results .= '<a href="' . add_query_arg( 'dir', 'desc' ) . '#search-results"' . ( $sort == 'desc' ? ' class="active"' : '' ) . '>DESC</a>';
			$results .= '</div>';
		$results .= '</div>';

		$results .= '<div class="tally-search-results-plugins"' . $plugin_visibility . '>';
		if ( is_wp_error( $plugins ) ) {
			$results .= '<div class="tally-search-error">An error occurred with the plugins API. Please try again later.</div>';
		} else {
			$plugins = $plugins->plugins;

			// Maybe sort plugins
			$plugins = wptally_sort( $plugins, $order_by, $sort );

			// How many plugins does the user have?
			$count = count( $plugins );
			$total_downloads = 0;
			$ratings_count = 0;
			$ratings_total = 0;

			if ( $count == 0 ) {
				$results .= '<div class="tally-search-error">No plugins found for ' . $username . '!</div>';
			} else {
				foreach ( $plugins as $plugin ) {
					$rating = wptally_get_rating( $plugin['num_ratings'], $plugin['ratings'] );

					// Plugin row
					$results .= '<div class="tally-plugin">';

					// Content left
					$results .= '<div class="tally-plugin-left">';

					// Plugin title
					$results .= '<a class="tally-plugin-title" href="http://wordpress.org/plugins/' . $plugin['slug'] . '" target="_blank">' . $plugin['name'] . '&nbsp;&ndash;&nbsp;' . $plugin['version'] . '</a>';

					// Plugin meta
					$results .= '<div class="tally-plugin-meta">';
					$results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">Added:</span> ' . date( 'd M, Y', strtotime( $plugin['added'] ) ) . '</span>';
					$results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">Last Updated:</span> ' . date( 'd M, Y', strtotime( $plugin['last_updated'] ) ) . '</span>';
					$results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">Rating:</span> ' . ( empty( $rating ) ? 'not yet rated' : $rating . ' out of 5 stars' ) . '</span>';
					$results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">Active Installs:</span> ' . number_format( $plugin['active_installs'] ) . '</span>';
					$results .= '</div>';

					// End content left
					$results .= '</div>';

					// Content right
					$results .= '<div class="tally-plugin-right">';
					$results .= '<div class="tally-plugin-downloads">' . number_format( $plugin['downloaded'] ) . '</div>';
					$results .= '<div class="tally-plugin-downloads-title">Downloads</div>';
					$results .= '</div>';

					// End plugin row
					$results .= '</div>';

					$total_downloads = $total_downloads + $plugin['downloaded'];

					if ( ! empty( $rating ) ) {
						$ratings_total += $rating;
						$ratings_count++;
					}
				}

				$plugins_total     = number_format( $count );
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
						$results .= '<iframe src="https://www.facebook.com/plugins/share_button.php?href=http%3A%2F%2Fwptally.com%2F%3Fwpusername%3D' . esc_attr( $username ) . '&layout=button&size=small&mobile_iframe=true&appId=592073487648800&width=59&height=20" width="59" height="20" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>';
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

		// Maybe sort themes
		$themes = wptally_sort( $themes, $order_by, $sort );

		$results .= '<div class="tally-search-results-themes"' . $theme_visibility . '>';
		if ( is_wp_error( $themes ) ) {
			$results .= '<div class="tally-search-error">An error occurred with the themes API. Please try again later.</div>';
		} else {
			// How many themes does the user have?
			$count = count( $themes );
			$total_downloads = 0;
			$ratings_count = 0;
			$ratings_total = 0;

			if ( $count == 0 ) {
				$results .= '<div class="tally-search-error">No themes found for ' . $username . '!</div>';
			} else {
				foreach( $themes as $theme ) {
					$rating = wptally_get_rating( $theme['num_ratings'], $theme['rating'] );

					// Theme row
					$results .= '<div class="tally-plugin">';

					// Content left
					$results .= '<div class="tally-plugin-left">';

					// Theme title
					$results .= '<a class="tally-plugin-title" href="http://wordpress.org/themes/' . $theme['slug'] . '" target="_blank">' . $theme['name'] . '&nbsp;&ndash;&nbsp;' . $theme['version'] . '</a>';

					// Theme meta
					$results .= '<div class="tally-plugin-meta">';
					$results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">Last Updated:</span> ' . date( 'd M, Y', strtotime( $theme['last_updated'] ) ) . '</span>';
					$results .= '<span class="tally-plugin-meta-item"><span class="tally-plugin-meta-title">Rating:</span> ' . ( empty( $rating ) ? 'not yet rated' : $rating . ' out of 5 stars' ) . '</span>';
					$results .= '</div>';

					// End content left
					$results .= '</div>';

					// Content right
					$results .= '<div class="tally-plugin-right">';
					$results .= '<div class="tally-plugin-downloads">' . number_format( $theme['downloaded'] ) . '</div>';
					$results .= '<div class="tally-plugin-downloads-title">Downloads</div>';
					$results .= '</div>';

					// End theme row
					$results .= '</div>';

					$total_downloads = $total_downloads + $theme['downloaded'];

					if ( ! empty( $rating ) ) {
						$ratings_total += $rating;
						$ratings_count++;
					}
				}

				$themes_total      = number_format( $count );
				$themes_reference  = $themes_total == 1 ? 'theme' : 'themes';
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
						$results .= '<iframe src="https://www.facebook.com/plugins/share_button.php?href=http%3A%2F%2Fwptally.com%2F%3Fwpusername%3D' . esc_attr( $username ) . '&layout=button&size=small&mobile_iframe=true&appId=592073487648800&width=59&height=20" width="59" height="20" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>';
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
