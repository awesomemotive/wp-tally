<?php
/**
 * Dashboard widgets
 *
 * @package     WPTally\DashboardWidgets
 * @since       1.1.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add lookups to the At A Glance widget
 *
 * @since       1.1.0
 * @return      void
 */
function wptally_at_a_glance() {
    $count = get_option( 'wptally_lookups' );

    if( $count ) {
        $count = number_format_i18n( $count );
    } else {
        $count = 0;
    }

    $label = _n( 'Lookup', 'Lookups', intval( $count ) );

    echo '<li class="wptally-count"><span>' . $count . ' ' . $label . '</span></li>';
}
add_action( 'dashboard_glance_items', 'wptally_at_a_glance' );
