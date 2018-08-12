<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

class GISC_Product {
	/**
	 * The GISC Instance.
	 *
	 * @since 0.1
	 *
	 * @var   \GISC
	 */
	protected static $the_instance = null;

	protected $product = null;
	protected $related_post_id = null;

	/**
	 * @return bool
	 *
	 * @since  0.1
	 */
	public function has_error() {
		if ( is_null( $this->product ) ) {
			return true;
		}

		if ( (int) $this->product['Flag'] > 6 )  {
			return true;
		}

		return false;
	}

	/**
	 * @return int
	 *
	 * @since  0.1
	 */
	public function related_post_id() {
		return $this->related_post_id;
	}

	/**
	 * @return HTML
	 *
	 * @since  0.1
	 */
	public function display_error() {
		?>
		<p class="alert-color">
			<?php
			if ( is_null( $this->product ) ) {
				echo __( 'Cannot retrieve product\'s information. Please try again or contact our support team', 'garminbygis' );
			} else if ( $this->product['Flag'] == 102 ) {
				echo __( 'The serial number has been registered.', 'garminbygis' );
			} else {
				echo __( 'No serial found.', 'garminbygis' );
			}
			?>
		</p>
		<?php
	}

	/**
	 * @since  0.1
	 */
	public function register( $serialNo, $email ) {
		$this->product = GISC()->get( 'register_product', array( 'serialNo' => $serialNo, 'Email' => $email ) );

		if ( $this->product['Flag'] >= 0 && $this->product['Flag'] <= 6 ) {
			$post_id = wp_insert_post( array(
			    'post_title'  => 'GISC Product Receipt, owner id: " ' . $this->product['ProductOwnerId'] . ' ", serial: "' . $this->product['SerialNo'] . '"',
			    'post_status' => 'publish',
			    'post_type'   => 'gis_reg_product'
			) );

			garminbygis_update_post_meta( $post_id, 'gisc_reg_product_product_owner_id', $this->product['ProductOwnerId'] );
			garminbygis_update_post_meta( $post_id, 'gisc_reg_product_serial_number', $this->product['SerialNo'] );
			garminbygis_update_post_meta( $post_id, 'gisc_reg_product_product_owner_email', $email );

			$this->related_post_id = $post_id;
		}

		return $this;
	}

	/**
	 * @since  0.1
	 */
	public function deregister( $productOwnerId, $email ) {
		$result = GISC()->get( 'remove_registed_product', array( 'productOwnerId' => $productOwnerId, 'Email' => $email ) );

		$args = array(
			'post_type'   => 'gis_reg_product',
			'post_status' => array( 'publish' ),
			'meta_query'  => array(
				array(
					'key'     => 'gisc_reg_product_product_owner_id',
					'value'   => $productOwnerId,
					'compare' => 'LIKE'
				),
				array(
					'key'     => 'gisc_reg_product_product_owner_email',
					'value'   => $email,
					'compare' => 'LIKE'
				)
			)
		);

		$query = new WP_Query( $args );
		$posts = $query->have_posts() ? $query->posts : array();

		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );
		}

		return $result;
	}

	public function get_related_posts( $productOwnerId, $email ) {
		$args = array(
			'post_type'   => 'gis_reg_product',
			'post_status' => array( 'publish' ),
			'meta_query'  => array(
				array(
					'key'     => 'gisc_reg_product_product_owner_id',
					'value'   => $productOwnerId,
					'compare' => 'LIKE'
				),
				array(
					'key'     => 'gisc_reg_product_product_owner_email',
					'value'   => $email,
					'compare' => 'LIKE'
				)
			)
		);

		return new WP_Query( $args );
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
