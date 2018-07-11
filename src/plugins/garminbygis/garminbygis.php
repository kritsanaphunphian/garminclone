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
	 * @var string  API endpoint.
	 */
	protected $endpoint = 'http://203.151.93.59:94/api';

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

	/**
	 * @param string $email
	 * @param string $serial
	 */
	public function request_register_product( $email, $serial ) {
		$endpoint = 'ProductRegistration/GetProduct_byEmail';
		$params   = array( 'serialNo', 'Email' );
	}

	/**
	 * @param string $email
	 */
	public function request_list_registered_product( $email ) {
		$endpoint = 'ProductRegistration/GetRegisteredProducts_byEmail';
		$params   = array( 'Email' );
	}

	/**
	 * @param string $productOwnerId
	 */
	public function request_remove_registed_product( $productOwnerId ) {
		$endpoint = 'ProductRegistration/Delete';
		$params   = array( 'productOwnerId' );
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @param string $name
	 * @param string $surname
	 */
	public function request_register_new_customer( $email, $password, $name, $surname ) {
		$endpoint = 'Customer/insertGarminCustomer';
		$params   = array( 'Email', 'Password', 'Name', 'Surname' );
	}

	/**
	 * @param string $email
	 * @param string $password
	 */
	public function request_update_customer_password( $email, $password ) {
		$endpoint = 'Customer/ChangePasswordGarminCustomer';
		$params   = array( 'Email', 'Password' );
	}
}

function GISC() {
	return GISC::instance();
}

GISC();
