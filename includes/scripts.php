<?php
/**
 * Scripts
 *
 * @package     WPTally\Scripts
 * @since       1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Load scripts and styles
 *
 * @since       1.0.0
 * @return      void
 */
function wptally_load_scripts() {
	wp_enqueue_script( 'wptally', WPTALLY_URL . 'assets/js/wptally.js', array( 'jquery' ) );
	wp_enqueue_style( 'wptally', WPTALLY_URL . 'assets/css/style.css' );
}
add_action( 'wp_enqueue_scripts', 'wptally_load_scripts' );


/**
 * Load admin scripts and styles
 *
 * @since       1.1.0
 * @return      void
 */
function wptally_load_admin_scripts() {
	wp_enqueue_style( 'wptally', WPTALLY_URL . 'assets/css/admin.css' );
}
add_action( 'admin_enqueue_scripts', 'wptally_load_admin_scripts' );
