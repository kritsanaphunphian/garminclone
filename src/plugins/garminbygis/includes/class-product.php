<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

class GISC_Product {
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
	public function register( $serialNo, $email ) {
	    return GISC()->request( 'register_product', array( 'serialNo' => $serialNo, 'Email' => $email ) );
	}

	/**
	 * @since  3.0
	 */
	public function deregister( $productOwnerId, $email ) {
    	return GISC()->request( 'remove_registed_product', array( 'productOwnerId' => $productOwnerId, 'Email' => $email ) );
	}

	/**
	 * The GISC_Product Instance.
	 *
	 * @see    GISC_Product()
	 *
	 * @since  3.0
	 *
	 * @static
	 *
	 * @return \GISC_Product - The instance.
	 */
	public static function instance() {
		if ( is_null( self::$the_instance ) ) {
			self::$the_instance = new self();
		}

		return self::$the_instance;
	}
}

function GISC_Product() {
	return GISC_Product::instance();
}

GISC_Product();
