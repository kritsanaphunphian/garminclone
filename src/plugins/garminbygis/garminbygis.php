<?php
/**
 * Plugin Name: GarminByGIS
 * Plugin URI:  https://www.garminbygis.com
 * Description: GarminByGIS API Integration plugin
 * Version:     0.1
 * Author:      gisc
 * Text Domain: garminbygis
 */
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

class GISC {
	/**
	 * GISC plugin version number.
	 *
	 * @var string
	 */
	public $version = '0.1';

	/**
	 * The GISC Instance.
	 *
	 * @since 3.0
	 *
	 * @var   \GISC
	 */
	protected static $the_instance = null;

	/**
	 * @since  3.0
	 */
	public function __construct() {
		$this->initiate();
	}

	/**
	 * @since  3.0
	 */
	protected function initiate() {
		defined( 'GISC_PLUGIN_VERSION' ) || define( 'GISC_PLUGIN_VERSION', $this->version );
		defined( 'GISC_PLUGIN_PATH' ) || define( 'GISC_PLUGIN_PATH', __DIR__ );
	}

	/**
	 * @since  3.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'gisc', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * The GISC Instance.
	 *
	 * @see    GISC()
	 *
	 * @since  3.0
	 *
	 * @static
	 *
	 * @return \GISC - The instance.
	 */
	public static function instance() {
		if ( is_null( self::$the_instance ) ) {
			self::$the_instance = new self();
		}

		return self::$the_instance;
	}
}

function GISC() {
	return GISC::instance();
}

GISC();
