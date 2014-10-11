<?php
/**
 * Plugin Name:     WP Tally
 * Plugin URI:      http://wptally.com
 * Description:     Track your total WordPress plugin downloads
 * Version:         1.0.0
 * Author:          Pippin Williamson, Daniel J Griffiths & Sean Davis
 * Author URI:      http://easydigitaldownloads.com
 * Text Domain:     wp-tally
 * Domain Path:     /languages/
 *
 * @package         WPTally
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


if( ! class_exists( 'WPTally' ) ) {


    /**
     * Main WPTally class
     *
     * @since       1.0.0
     */
    class WPTally {


        /**
         * @access      private
         * @since       1.0.0
         * @var         WPTally $instance The one true WPTally
         */
        private static $instance;


        /**
         * @access      public
         * @since       1.0.0
         * @var         object $api The WPTally API object
         */
        public $api;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      self::$instance The one true WPTally
         */
        public static function instance() {
            if( ! self::$instance ) {
                self::$instance = new WPTally();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();
                self::$instance->api = new WPTally_API();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin path
            define( 'WPTALLY_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'WPTALLY_URL', plugin_dir_url( __FILE__ ) );
        }
        
        
        /**
         * load plugin language files
         *
         * @access public
         * @since 1.0.0
         * @return void
         */
        public function load_textdomain() {
            load_plugin_textdomain( 'wp-tally', false, trailingslashit( WPTALLY_DIR ) . 'languages/' );
        }


        /**
         * Include required files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            require_once WPTALLY_DIR . 'includes/scripts.php';
            require_once WPTALLY_DIR . 'includes/functions.php';
            require_once WPTALLY_DIR . 'includes/shortcodes.php';
            require_once WPTALLY_DIR . 'includes/class.wptally-api.php';
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {

        }
    }
}


/**
 * The main function responsible for returning the one true WPTally
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      WPTally The one true WPTally
 */
function wptally_load() {
    return WPTally::instance();
}
add_action( 'plugins_loaded', 'wptally_load' );
