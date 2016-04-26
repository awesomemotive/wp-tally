<?php
/**
 * WP Tally API
 *
 * @package     WPTally\API
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * WP Tally API Class
 *
 * @since       1.0.0
 */
class WPTally_API {


    /**
     * @access      private
     * @since       1.0.0
     * @var         bool $pretty_print Pretty print?
     */
    private $pretty_print = false;


    /**
     * @access      private
     * @since       1.0.0
     * @var         array $data The data to return
     */
    private $data = array();


    /**
     * Setup the API
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function __construct() {
        add_action( 'init', array( $this, 'add_endpoint' ) );
        add_action( 'template_redirect', array( $this, 'process_query' ), -1 );
        add_filter( 'query_vars', array( $this, 'query_vars' ) );

        // Determine if JSON_PRETTY_PRINT is available
        $this->pretty_print = defined( 'JSON_PRETTY_PRINT' ) ? JSON_PRETTY_PRINT : null;
    }


    /**
     * Register our API endpoint
     *
     * @access      public
     * @since       1.0.0
     * @param       array $rewrite_rules Existing rewrite rules
     * @return      void
     */
    public function add_endpoint( $rewrite_rules ) {
        add_rewrite_endpoint( 'api', EP_ALL );
    }


    /**
     * Register new query vars
     *
     * @access      public
     * @since       1.0.0
     * @param       array $vars Existing query vars
     * @return      array $vars Updated query vars
     */
    public function query_vars( $vars ) {
        $vars[] = 'username';
        $vars[] = 'force';
        $vars[] = 'format';

        return $vars;
    }


    /**
     * Listen for the API and process requests
     *
     * @access      public
     * @since       1.0.0
     * @global      object $wp_query The WordPress query object
     * @return      void
     */
    public function process_query() {
        global $wp_query;

        // Bail if this isn't an api call
        if( ! isset( $wp_query->query_vars['api'] ) ) {
            return;
        }

        $data = array();

        if( empty( $wp_query->query_vars['api'] ) ) {
            $data = array(
                'error' => 'No username specified'
            );
        } else {
            $lookup_count = get_option( 'wptally_lookups' );
            $lookup_count = $lookup_count ? $lookup_count + 1 : 1;
            update_option( 'wptally_lookups', $lookup_count );

            if( isset( $wp_query->query_vars['force'] ) && $wp_query->query_vars['force'] == 'true' ) {
                delete_transient( 'wp-tally-user-' . $wp_query->query_vars['api'] );
                delete_transient( 'wp-tally-user-themes-' . $wp_query->query_vars['api'] );
                $force = true;
            }

            $data['info'] = array(
                'user'      => $wp_query->query_vars['api'],
                'profile'   => 'https://profiles.wordpress.org/' . $wp_query->query_vars['api']
            );

            $plugins = wptally_maybe_get_plugins( $wp_query->query_vars['api'], ( isset( $force ) ? true : false ) );

            if( is_wp_error( $plugins ) ) {
                $data['plugins'] = array(
                    'error' => 'An error occurred with the plugins API'
                );
            } else {
                // How many plugins does the user have?
                $count = count( $plugins->plugins );
                $total_downloads = 0;

                if( $count == 0 ) {
                    $data['plugins'] = array(
                        'error' => 'No plugins found for ' . $wp_query->query_vars['api']
                    );
                } else {
                    foreach( $plugins->plugins as $plugin ) {
                        $rating = wptally_get_rating( $plugin->num_ratings, $plugin->ratings );

                        $data['plugins'][$plugin->slug] = array(
                            'name'      => $plugin->name,
                            'url'       => 'http://wordpress.org/plugins/' . $plugin->slug,
                            'version'   => $plugin->version,
                            'added'     => date( 'd M, Y', strtotime( $plugin->added ) ),
                            'updated'   => date( 'd M, Y', strtotime( $plugin->last_updated ) ),
                            'rating'    => $rating,
                            'downloads' => $plugin->downloaded,
                            'installs'  => $plugin->active_installs
                        );

                        $total_downloads = $total_downloads + $plugin->downloaded;
                    }

                    $data['info']['plugin_count']           = $count;
                    $data['info']['total_plugin_downloads'] = $total_downloads;
                }
            }
            
            $themes = wptally_maybe_get_themes( $wp_query->query_vars['api'], ( isset( $force ) ? true : false ) );

            if( is_wp_error( $themes ) ) {
                $data['themes'] = array(
                    'error' => 'An error occurred with the themes API'
                );
            } else {
                // How many plugins does the user have?
                $count = count( $themes );
                $total_downloads = 0;

                if( $count == 0 ) {
                    $data['themes'] = array(
                        'error' => 'No themes found for ' . $wp_query->query_vars['api']
                    );
                } else {
                    foreach( $themes as $theme ) {
                        $rating = wptally_get_rating( $theme->num_ratings, $theme->rating );

                        $data['themes'][$theme->slug] = array(
                            'name'      => $theme->name,
                            'url'       => 'http://wordpress.org/themes/' . $theme->slug,
                            'version'   => $theme->version,
                            'updated'   => date( 'd M, Y', strtotime( $theme->last_updated ) ),
                            'rating'    => $rating,
                            'downloads' => $theme->downloaded
                        );

                        $total_downloads = $total_downloads + $theme->downloaded;
                    }

                    $data['info']['theme_count']            = $count;
                    $data['info']['total_theme_downloads']  = $total_downloads;
                }
            }
        }

        $this->data = $data;

        // Send data to the output function
        $this->output();

    }


    /**
     * Retrieve the output format
     *
     * @access      public
     * @since       1.0.0
     * @global      object $wp_query The WordPress query object
     * @return      string $format The format to output in
     */
    public function get_output_format() {
        global $wp_query;

        $format = isset( $wp_query->query_vars['format'] ) ? $wp_query->query_vars['format'] : 'json';

        return $format;
    }


    /**
     * Retrieve the output data
     *
     * @access      public
     * @since       1.0.0
     * @return      array The output data
     */
    public function get_output() {
        return $this->data;
    }


    /**
     * Output query
     *
     * @access      public
     * @since       1.0.0
     * @param       int $status_code The status code to return
     * @global      object $wp_query The WordPress query object
     * @return      void
     */
    public function output( $status_code = 200 ) {
        global $wp_query;

        $format = $this->get_output_format();

        status_header( $status_code );

        switch( $format ) {

            case 'xml' :
                require_once WPTALLY_DIR . 'includes/libraries/array2xml.php';
                $xml = Array2XML::createXML( 'wptally', $this->data );
                echo $xml->saveXML();

                break;
            case 'json' :
                header( 'Content-Type: application/json' );
                if( ! empty( $this->pretty_print ) ) {
                    echo json_encode( $this->data, $this->pretty_print );
                } else {
                    echo json_encode( $this->data );
                }

                break;
        }

        die();
    }
}

