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
	 * @var string  API server url.
	 */
	protected $api_server = 'http://garminuat.cdg.co.th:94/api';

	/**
	 * @var string  API endpoint.
	 */
	protected $endpoint = '';

	/**
	 * @var array
	 */
	protected $required_params = array();

	/**
	 * @var array
	 */
	protected $request_params = array();

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

		include_once GISC_PLUGIN_PATH . '/includes/class-product.php';
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

	public function get( $api_name, $params ) {
		if ( ! method_exists( $this, 'api_' . $api_name ) ) {
			return false;
		}

		$result = wp_remote_get( $this->build_request( $api_name, $params ) );

		if ( is_array( $result ) && ! is_wp_error( $result ) ) {
			return json_decode( $result['body'], true );
		}

		return false;
	}

	public function post( $api_name, $params ) {
		if ( ! method_exists( $this, 'api_' . $api_name ) ) {
			return false;
		}

		$result = wp_remote_post( $this->build_request( $api_name, $params ) );

		if ( is_array( $result ) && ! is_wp_error( $result ) ) {
			return json_decode( $result['body'], true );
		}

		return false;
	}

	/**
	 * @param string $email
	 * @param string $serial
	 */
	public function api_register_product() {
		$this->set_endpoint( 'ProductRegistration/GetProduct_byEmail' );
		$this->set_required_parameters( array( 'serialNo', 'Email' ) );
	}

	/**
	 * @param string $email
	 */
	public function api_list_registered_product() {
		$this->set_endpoint( 'ProductRegistration/GetRegisteredProducts_byEmail' );
		$this->set_required_parameters( array( 'Email' ) );
	}

	/**
	 * @param string $productOwnerId
	 */
	public function api_remove_registed_product() {
		$this->set_endpoint( 'ProductRegistration/Delete' );
		$this->set_required_parameters( array( 'productOwnerId' ) );
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @param string $name
	 * @param string $surname
	 */
	public function api_register_new_customer() {
		$this->set_endpoint( 'Customer/insertGarminCustomerCommerce' );
		$this->set_required_parameters( array( 'Email', 'Password', 'Passwordenc', 'Name', 'Surname' ) );
	}

	/**
	 * @param string $email
	 * @param string $password
	 */
	public function api_update_customer_password() {
		$this->set_endpoint( 'Customer/ChangePasswordGarminCustomerCommerce' );
		$this->set_required_parameters( array( 'Email', 'Password', 'Passwordenc' ) );
	}

	/**
	 * @param string $endpoint
	 */
	protected function set_endpoint( $endpoint ) {
		$this->endpoint = $this->api_server . '/' . $endpoint;
	}

	/**
	 * @param array $params
	 */
	protected function set_required_parameters( $params ) {
		$this->required_params = $params;
	}

	/**
	 * @param array $params
	 */
	protected function build_request( $api_name, $params ) {
		$api = 'api_' . $api_name;
		$this->$api();

		foreach ( $this->required_params as $key => $value ) {
			$this->request_params[ $value ] = isset( $params[ $value ] ) ? $params[ $value ] : null;
		}

		return add_query_arg( $this->request_params, $this->endpoint );
	}
}

function GISC() {
	return GISC::instance();
}

GISC();
