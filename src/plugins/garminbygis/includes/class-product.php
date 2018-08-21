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
	protected $related_posts = null;

	const META_PRODUCT_OWNER_EMAIL  = 'gisc_reg_product_product_owner_email';
	const META_PRODUCT_OWNER_ID     = 'gisc_reg_product_product_owner_id';
	const META_RECEIPT_DOCUMENT_URL = 'gisc_reg_product_receipt_document_url';
	const META_SERIAL_NUMBER        = 'gisc_reg_product_serial_number';

	/**
	 * @return bool
	 *
	 * @since  0.1
	 */
	public function has_error() {
		if ( isset( $this->product['Message'] )
			|| is_null( $this->product )
			|| ( (int) $this->product['Flag'] > 6 && (int) $this->product['Flag'] != 99 )
		) {
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
			if ( isset( $this->product['Message'] ) ) {
				echo __( 'Error found, please contact customer service', 'garminbygis' );
			} else if ( is_null( $this->product ) ) {
				echo __( 'Cannot retrieve product\'s information. Please try again or contact our support team', 'garminbygis' );
			} else if ( $this->product['Flag'] == 102 ) {
				echo __( 'The serial number is already registered.', 'garminbygis' );
			} else {
				echo __( 'Serial Number not found.', 'garminbygis' );
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

		if ( ( $this->product['Flag'] >= 0 && $this->product['Flag'] <= 6 ) || $this->product['Flag'] == 99 ) {
			$this->insert_related_post( $this->product['ProductOwnerId'], $this->product['SerialNo'], $email );
		}

		return $this;
	}

	/**
	 * @since  0.1
	 */
	public function deregister( $productOwnerId, $serialNo, $email ) {
		$result = GISC()->get( 'remove_registed_product', array( 'productOwnerId' => $productOwnerId, 'Email' => $email ) );

		$args = array(
			'post_type'   => 'gis_reg_product',
			'post_status' => array( 'publish' ),
			'meta_query'  => array(
				array(
					'key'     => self::META_SERIAL_NUMBER,
					'value'   => $serialNo,
					'compare' => 'LIKE'
				),
				array(
					'key'     => self::META_PRODUCT_OWNER_EMAIL,
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

	public function insert_related_post( $productOwnerId, $serialNo, $userEmail ) {
		$post_id = wp_insert_post( array(
		    'post_title'  => 'GISC Product Receipt, serial: "' . $serialNo . '", email: " ' . $userEmail . ' "',
		    'post_status' => 'publish',
		    'post_type'   => 'gis_reg_product'
		) );

		garminbygis_update_post_meta( $post_id, self::META_PRODUCT_OWNER_EMAIL, $userEmail );
		garminbygis_update_post_meta( $post_id, self::META_PRODUCT_OWNER_ID, $productOwnerId );
		garminbygis_update_post_meta( $post_id, self::META_RECEIPT_DOCUMENT_URL, '' );
		garminbygis_update_post_meta( $post_id, self::META_SERIAL_NUMBER, $serialNo );

		$this->related_post_id = $post_id;

		return $this;
	}

	public function load_related_posts( $serialNo, $email ) {
		$args = array(
			'post_type'   => 'gis_reg_product',
			'post_status' => array( 'publish' ),
			'meta_query'  => array(
				array(
					'key'     => self::META_SERIAL_NUMBER,
					'value'   => $serialNo,
					'compare' => 'LIKE'
				),
				array(
					'key'     => self::META_PRODUCT_OWNER_EMAIL,
					'value'   => $email,
					'compare' => 'LIKE'
				)
			)
		);

		$this->related_posts = new WP_Query( $args );

		if ($this->related_posts->have_posts()) {
			$this->related_post_id = $this->related_posts->posts[0]->ID;
		}

		return $this;
	}

	public function have_related_posts() {
		return $this->related_posts->have_posts();
	}

	public function get_related_posts( $serialNo = null, $email = null ) {
		if ( ! is_null( $serialNo ) && ! is_null( $email ) ) {
			$this->load_related_posts( $serialNo, $email );
		}

		return $this->related_posts;
	}

	/**
	 * @param array $file of $_FILES
	 */
	public function attach_receipt($file) {
		$upload = wp_upload_bits(
			$_FILES['product-receipt']['name'],
			null,
			file_get_contents( $_FILES['product-receipt']['tmp_name'] )
		);

		garminbygis_update_post_meta( $this->related_post_id(), self::META_RECEIPT_DOCUMENT_URL, $upload['url'] );
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
