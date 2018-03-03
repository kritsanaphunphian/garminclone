<?php 
/**
 * Product Attribute Group Integration on Product Screen
 *
 * @author James Collings <james@jclabs.co.uk>
 * @version 0.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class JCAA_Admin_Product_Attribute_Groups{

	public function __construct(){

		// display attribute group toolbar beneath wc attribute section
		add_action( 'woocommerce_product_options_attributes', array( $this, 'attribute_group_toolbar'));

		// enqueue admin hs
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );
	}

	public function add_scripts(){

		// only show if on product attribute add/edit screen
		$screen = get_current_screen();
		if($screen->id !== 'product'){
			return;
		}

		$attribute_groups = $this->get_attribute_groups();
		$attribute_groups_json = array();

		if($attribute_groups->have_posts()){
			foreach($attribute_groups->posts as $post){

				$slug = substr($post->post_name, strlen('jcaa_'));
				$temp = array(
					'label' => $post->post_title,
					'name' => $slug,
					'attributes' => unserialize($post->post_content)
				);
				$attribute_groups_json[] = $temp;
			}
		}

		wp_register_script( 'jcaa-product-attribute-group', JCAA()->get_plugin_url() . 'assets/js/admin/product-attribute-groups.js', array('jquery'), JCAA()->get_version(), true );
		wp_localize_script( 'jcaa-product-attribute-group', 'jcaa_attribute_groups', $attribute_groups_json );
		wp_enqueue_script( 'jcaa-product-attribute-group' );



	}

	/**
	 * Get attribute groups from database
	 * @return WP_Query
	 */
	public function get_attribute_groups(){
		$query = new WP_Query(array(
			'post_type' => 'attribute_group',
			'post_status' => 'publish',
			'posts_per_page' => -1
		));

		return $query;
	}

	/**
	 * Ouput attribute group toolbar beneath product attribute section
	 *
	 * Display attribute group toolbar to load and save groups, move form fields from footer into attribute form
	 * @return void
	 */
	public function attribute_group_toolbar(){

		add_thickbox(); ?>	
		
		<p class="toolbar">
			
			<button type="button" class="button button-primary jcaa_load_attribute_group" style="float: right;margin: 0 0 0 6px;"><?php _e('Load','jcaa'); ?></button>
			<select id="jcaa_attribute_groups" name="jcaa_attribute_groups" class="jcaa_attribute_groups" style="float: right;margin: 0 0 0 6px;">
				<option value=""><?php _e('Attribute Groups','jcaa'); ?></option>
			</select>
			
			<a href="<?php echo admin_url('edit.php?post_type=attribute_group' ); ?>" class="button" onclick="return confirm('<?php _e('Are you sure you want to navigate away.','jcaa'); ?>');"><?php _e('Manage Attribute Groups','jcaa'); ?></a>
		</p>
		<?php
	}
}

new JCAA_Admin_Product_Attribute_Groups();