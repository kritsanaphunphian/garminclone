<?php
/**
 * Plugin Name: GarminByGIS : AIA Integration
 * Description: Integrate AIA API to GarminByGIS website.
 * Version: 0.1
 * Author: GarminByGIS
 * Text Domain: garminbygis-aia
 **/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class GarminByGISAIA {
    /**
     * GarminByGISAIA plugin version number.
     *
     * @var string
     */
    public $version = '0.1';

    /**
     * The GarminByGISAIA Instance.
     *
     * @since 0.1
     *
     * @var   \GarminByGISAIA
     */
    protected static $the_instance = null;

    /**
     * @since 0.1
     */
    public function __construct() {
        $this->initiate();

        do_action( 'garminbygis_aia_initiated' );
    }

    /**
     * @since 0.1
     */
    protected function initiate() {
        defined( 'GARMINBYGIS_AIA_PLUGIN_PATH' ) || define( 'GARMINBYGIS_AIA_PLUGIN_PATH', __DIR__ );

        require_once GARMINBYGIS_AIA_PLUGIN_PATH . '/includes/AIA/class-aia-api.php';
        require_once GARMINBYGIS_AIA_PLUGIN_PATH . '/includes/class-user.php';

        add_filter( 'the_posts', array( $this, 'register_page_aia_users_verification' ) );
    }

    /**
     * @param  array $posts
     *
     * @return array
     */
    public function register_page_aia_users_verification( $posts ) {
        global $wp, $wp_query;

        $page_slug = 'aia-users-verification';
        $token     = isset( $_GET['token'] ) ? $_GET['token'] : '';

        //check if user is requesting our fake page
        if ( $token && 0 == count( $posts ) && ( $page_slug == strtolower( $wp->request ) || $page_slug == $wp->query_vars['page_id'] ) ) {
            $user = new User;

            if ( $user->validate_token( $token ) ) {
                wp_redirect( home_url() );
                exit;
            }
        }

        return $posts;
    }

    /**
     * The GarminByGISAIA Instance.
     *
     * @see    GarminByGISAIA()
     *
     * @since  0.1
     *
     * @static
     *
     * @return \GarminByGISAIA  The instance.
     */
    public static function instance() {
        if ( is_null( self::$the_instance ) ) {
            self::$the_instance = new self();
        }

        return self::$the_instance;
    }
}

function GarminByGISAIA() {
    return GarminByGISAIA::instance();
}

GarminByGISAIA();
