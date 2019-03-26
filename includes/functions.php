<?php
/**
 * Helper functions
 *
 * @package     WPTally\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Get a users plugin data
 *
 * @since       1.0.0
 * @param       string $username The user to check
 * @param       bool $force Forcibly remove any existing transient and requery
 * @return      object $plugins The users plugins
 */
function wptally_maybe_get_plugins( $username = false, $force = false ) {
	if ( $username ) {
		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}

		if ( $force ) {
			delete_transient( 'wp-tally-user-' . $username );
		}

		if ( ! $plugins = get_transient( 'wp-tally-user-' . $username ) ) {
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
						'last_updated'      => true,
						'active_installs'   => true
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
 * Get a users theme data
 *
 * @since       1.1.0
 * @param       string $username The user to check
 * @param       bool $force Forcibly remove any existing transient and requery
 * @return      object $plugins The users plugins
 */
function wptally_maybe_get_themes( $username = false, $force = false ) {
	if ( $username ) {
		if ( ! function_exists( 'themes_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/theme.php';
		}

		if ( $force ) {
			delete_transient( 'wp-tally-user-themes-' . $username );
		}

		if ( ! $themes = get_transient( 'wp-tally-user-themes-' . $username ) ) {
			$themes     = array();
			$theme_list = themes_api( 'query_themes',
				array(
					'author'   => $username,
					'per_page' => 999
				)
			);

			foreach( $theme_list->themes as $id => $data ) {
				$themes[] = (array) themes_api( 'theme_information',
					array(
						'slug'   => $data->slug,
						'fields' => array(
							'downloaded'        => true,
							'description'       => false,
							'short_description' => false,
							'tags'              => false,
							'sections'          => false,
							'last_updated'      => true,
							'ratings'           => true
						)
					)
				);
			}

			set_transient( 'wp-tally-user-themes-' . $username, $themes, 60 * 60 );
		}
	} else {
		$themes = false;
	}

	return $themes;
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
	if ( $num_ratings > 0 ) {
		if ( is_array( $ratings ) ) {
			$rating = ( $ratings[5] > 0 ? $ratings[5] * 5 : 0 );
			$rating = $rating + ( $ratings[4] > 0 ? $ratings[4] * 4 : 0 );
			$rating = $rating + ( $ratings[3] > 0 ? $ratings[3] * 3 : 0 );
			$rating = $rating + ( $ratings[2] > 0 ? $ratings[2] * 2 : 0 );
			$rating = $rating + ( $ratings[1] > 0 ? $ratings[1] * 1 : 0 );
			$rating = round( $rating / $num_ratings, 1 );
		} else {
			if ( $ratings > 0 && $ratings < 10 ) {
				$rating = 0.5;
			} elseif ( $ratings >= 10 && $ratings < 20 ) {
				$rating = 1;
			} elseif ( $ratings >= 20 && $ratings < 30 ) {
				$rating = 1.5;
			} elseif ( $ratings >= 30 && $ratings < 40 ) {
				$rating = 2;
			} elseif ( $ratings >= 40 && $ratings < 50 ) {
				$rating = 2.5;
			} elseif ( $ratings >= 50 && $ratings < 60 ) {
				$rating = 3;
			} elseif ( $ratings >= 60 && $ratings < 70 ) {
				$rating = 3.5;
			} elseif ( $ratings >= 70 && $ratings < 80 ) {
				$rating = 4;
			} elseif ( $ratings >= 80 && $ratings < 90 ) {
				$rating = 4.5;
			} elseif ( $ratings >= 90 ) {
				$rating = 5;
			}
		}
	} else {
		$rating = 0;
	}

	return $rating;
}


/**
 * Sort themes/plugins
 *
 * @since       1.2.0
 * @param       array $items The themes or plugins to sort
 * @param       string $order_by The field to sort by
 * @param       string $sort The direction to sort
 * @return      array $items The sorted items
 */
function wptally_sort( $items, $order_by, $sort ) {
	if ( $order_by == 'downloaded' ) {
		if ( $sort == 'desc' ) {
			usort( $items, function( $a, $b ) {
				return ( $b['downloaded'] - $a['downloaded'] );
			} );
		} else {
			usort( $items, function( $a, $b ) {
				return ( $a['downloaded'] - $b['downloaded'] );
			} );
		}
	} else {
		if ( $sort == 'desc' ) {
			usort( $items, function( $a, $b ) {
				return strcmp( $b['slug'], $a['slug'] );
			} );
		} else {
			usort( $items, function( $a, $b ) {
				return strcmp( $a['slug'], $b['slug'] );
			} );
		}
	}

	return $items;
}
