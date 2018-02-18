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

        do_action( 'garminbygisaia_initiated' );
    }

    /**
     * @since 0.1
     */
    protected function initiate() {
        // ...
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
