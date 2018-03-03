<?php
/**
 * Attribute Group Custom Post Type Administration
 *
 * Register and process all actions within add/edit screen
 * 
 * @author James Collings <james@jclabs.co.uk>
 * @version 0.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class JCAA_Admin_Attribute_Groups{

	public function __construct(){

		//  add meta boxes
		add_action( 'add_meta_boxes', array( $this, 'jcaa_add_meta_boxes' ), 30 );

		// change screen id's before woocommerce outputs scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'jcaa_set_screen_id' ), 9 );

		// save meta boxes
		add_action( 'save_post', array( $this, 'jcaa_save_meta_boxes' ), 10, 2 );

		// add attribute group to woocommerce screen ids
		add_filter( 'woocommerce_screen_ids', array( $this, 'jcaa_woocommerce_screen_ids' ) );

		// append admin coloumn headers
		add_filter('manage_attribute_group_posts_columns', array( $this, 'jcaa_columns_head' ));

		// append admin coloumns
		add_action('manage_attribute_group_posts_custom_column', array( $this, 'jcaa_columns_content' ), 10, 1);

		// register custom post type
		add_action( 'init', array( $this, 'register_attribute_group' ) );
	}

	/**
	 * Register attribute group post type
	 * @return void
	 */
	public function register_attribute_group(){

		$labels = array(
			'name'                => __( 'Attribute Groups', 'jcaa' ),
			'singular_name'       => __( 'Attribute Group', 'jcaa' ),
			'add_new'             => __( 'Add New Attribute Group', 'jcaa' ),
			'add_new_item'        => __( 'Add New Attribute Group', 'jcaa' ),
			'edit_item'           => __( 'Edit Attribute Group', 'jcaa' ),
			'new_item'            => __( 'New Attribute Group', 'jcaa' ),
			'view_item'           => __( 'View Attribute Group', 'jcaa' ),
			'search_items'        => __( 'Search Attribute Groups', 'jcaa' ),
			'not_found'           => __( 'No Attribute Groups found', 'jcaa' ),
			'not_found_in_trash'  => __( 'No Attribute Groups found in Trash', 'jcaa' ),
			'parent_item_colon'   => __( 'Parent Attribute Group:', 'jcaa' ),
			'menu_name'           => __( 'Attribute Groups', 'jcaa' ),
		);

		$args = array(
	      'public' => false,
	      'labels' => $labels,
	      'show_ui' => true,
	      'supports' => array('title'),
	      'show_in_menu' => 'edit.php?post_type=product'
	    );
	    register_post_type( 'attribute_group', $args );
	}

	/**
	 * Add admin attributes column  
	 * @param  array $columns 
	 * @return array
	 */
	function jcaa_columns_head($columns){
		$output = array();
		foreach($columns as $col => $name){

			$output[$col] = $name;

			//  add column after title column
			if($col === 'title'){
				$output['attrs'] = __('Attributes','jcaa');
			}
		}
		return $output;
	}

	/**
	 * Output list of attribites and values in attributes admin column
	 * @param  string $column_name 
	 * @param  int $post_ID     
	 * @return void
	 */
	function jcaa_columns_content($column_name){

		if($column_name !== 'attrs'){
			return;
		}

		// display list of attributes and chosen terms
		global $post;
		$attrs = maybe_unserialize( $post->post_content );
		if( !empty($attrs) ) {
			foreach ( $attrs as $group ) {
				$name = $group['name'];

				// remove pa_
				if ( strpos( $name, 'pa_' ) === 0 ) {
					$name = substr( $name, strlen( 'pa_' ) );
				}

				// remove character encoding being output
				if(!empty($group['values'])){
					foreach($group['values'] as $k => $v){
						$group['values'][$k] = urldecode($v);
					}
				}

				echo "<strong>" . $name . ':</strong> ( ' . implode( ', ', (array) $group['values'] ) . ')<br />';
			}
		}
	}

	/**
	 * Save data submitted from attribute group meta box
	 * @param  int $post_id 
	 * @param  array $post    
	 * @return void
	 */
	function jcaa_save_meta_boxes($post_id, $post ) {

		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce
		if ( empty( $_POST['jcaa_attribute_group'] ) || ! wp_verify_nonce( $_POST['jcaa_attribute_group'], 'jcaa_meta' ) ) {
			return;
		}

		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check the post type and save
		if ( $post->post_type === 'attribute_group' && isset($_POST['attribute_names']) ) {

			$result = array();
			
			foreach($_POST['attribute_names'] as $index => $name){

				$name = sanitize_text_field( $name );
				$position = isset($_POST['attribute_position'][$index]) ? absint($_POST['attribute_position'][$index]) : 0;
				$is_taxonomy = isset($_POST['attribute_is_taxonomy'][$index]) && $_POST['attribute_is_taxonomy'][$index] == 1 ? 1 : 0;
				$is_visible = isset($_POST['attribute_visibility'][$index]) ? 'true' : 'false';
				$is_variation = isset($_POST['attribute_variation'][$index]) ? 'true' : 'false';
				$values = isset($_POST['attribute_values'][$index]) ? $_POST['attribute_values'][$index] : array();

				if(!is_array($values) && !empty($values)){
					$values = !is_array($values) ? explode('|', $values) : array();
					foreach($values as $i => $value){
						$values[$i] = esc_textarea($value);//sanitize_text_field($value);
					}
				}

				/*if(!$is_taxonomy){
					$values = !is_array($values) ? explode('|', $values) : array();
					// foreach($values as $i => $value){
					// 	$values[$i] = esc_attr($value);
					// }
				}*/

				// dont save attribute if no values selected
				if(empty($values) && !isset( $_POST['jcaa_attribute_visible'][$index] ) ){
					continue;
				}

				// escape if no name is present as this is required
				if(empty($name)){
					continue;
				}

				$result[] = array(
					'values' => $values,
					'name' => $name,
					'visible' => $is_visible,
					'variation' => $is_variation,
					'show_empty' => isset( $_POST['jcaa_attribute_visible'][$index] ) ? 'yes' : 'no'
				);
			}

			// stop nesting in wp_update_post function
			remove_action( 'save_post', array( $this, 'jcaa_save_meta_boxes' ), 10, 2 );
			wp_update_post( array('ID' => $post_id, 'post_content' => serialize($result), 'post_name' => 'jcaa_'.sanitize_title( $_POST['post_title'] )));
		}
	}

	/**
	 * Load attribute group meta box on add/edit screen
	 * @return void
	 */
	function jcaa_add_meta_boxes(){
		add_meta_box( 'attributes', __( 'Attributes', 'woocommerce' ), array( $this, 'jcaa_output_meta_box'), 'attribute_group', 'normal' );
	}

	/**
	 * Add attribute group screen to woocommerce screen ids
	 * @param  array $ids 
	 * @return array
	 */
	function jcaa_woocommerce_screen_ids($ids){
		$ids[] = 'attribute_group';
		return $ids;
	}

	/**
	 * Change screen id to load in woocommerce assets
	 * @return void
	 */
	function jcaa_set_screen_id(){
		global $current_screen;
		$screen = get_current_screen();
		
		// change screen id to force woo to include correct styles and scripts
		if($screen->id === 'attribute_group'){
			$current_screen->id = 'product';

			// reset screen id
			add_action( 'admin_enqueue_scripts', array( $this, 'jcaa_reset_screen_id' ), 11 );
			wp_enqueue_script( 'jcaa-attribute-group', JCAA()->get_plugin_url() . 'assets/js/admin/attribute-groups.js', array('jquery'), JCAA()->get_version() );
		}	
	}

	/**
	 * Reset screen back to default id
	 * @return void
	 */
	function jcaa_reset_screen_id(){
		global $current_screen;
		$current_screen->id = 'attribute_group';
	}

	/**
	 * Check to see if attribute is taxonomy by prefix pa_
	 * @param  string $name 
	 * @return boolean
	 */
	function jcaa_is_taxonomy_attribute($name){
		if(strpos($name, 'pa_') === 0){
			return true;
		}
		return false;
	}

	/**
	 * Display attribute meta box on add/edit screen
	 * @return void
	 */
	function jcaa_output_meta_box(){

		global $post;

		// Array of defined attribute taxonomies
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		$attributes = maybe_unserialize( $post->post_content );

		wp_nonce_field( 'jcaa_meta', 'jcaa_attribute_group', null , true );
		$active = array();
		?>
		<style type="text/css">
			.show_if_variable{
				display: block !important;
			}
		</style>
		<div id="product_attributes" class="panel wc-metaboxes-wrapper">

			<p class="toolbar">
				<a href="#" class="close_all"><?php _e( 'Close all', 'woocommerce' ); ?></a> <a href="#" class="expand_all"><?php _e( 'Expand all', 'woocommerce' ); ?></a>
			</p>

			<div class="product_attributes wc-metaboxes">

				<?php 

				// Array of defined attribute taxonomies
				$attribute_taxonomies = wc_get_attribute_taxonomies();

				$i = -1;

				// Taxonomies
				if ( $attribute_taxonomies ) {
					foreach ( $attribute_taxonomies as $tax ) {

						// Get name of taxonomy we're now outputting (pa_xxx)
						$attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );

						// Ensure it exists
						if ( ! taxonomy_exists( $attribute_taxonomy_name ) ) {
							continue;
						}

						$i++;

						// Get product data values for current taxonomy - this contains ordering and visibility data
						if ( isset( $attributes[ sanitize_title( $attribute_taxonomy_name ) ] ) ) {
							$attribute = $attributes[ sanitize_title( $attribute_taxonomy_name ) ];
						}

						$position = 0;

						// Get terms of this taxonomy associated with current product
						$post_terms = array();
						$is_visible = false;
						$is_variation = false;
						$show_empty = false;

						if($attributes){
							foreach($attributes as $index => $attr){
								if($attr['name'] == $attribute_taxonomy_name){
									$post_terms = $attr['values'];
									$is_visible = isset($attr['visible']) && $attr['visible'] === 'true' ? true : false;
									$is_variation = isset($attr['variation']) && $attr['variation'] === 'true' ? true : false;
									$position = absint($index);
									$show_empty = isset($attr['show_empty']) && $attr['show_empty'] == 'yes' ? true : false;
									break;
								}
							}
						}

						// Any set?
						$has_terms = ( is_wp_error( $post_terms ) || ! $post_terms || sizeof( $post_terms ) == 0 ) ? 0 : 1;
						if($has_terms){
							$active[] = $attr['name'];
						}

						$check_term_id = false;
						if(!empty($post_terms)){
							if(intval($post_terms[0]) > 0){
								// we are term_id's
								$check_term_id = true;
							}
						}
						?>
						<div class="woocommerce_attribute wc-metabox taxonomy <?php echo $attribute_taxonomy_name; ?>" rel="<?php echo $position; ?>" <?php if ( ! $has_terms && !$show_empty ) echo 'style="display:none"'; ?>>
							<h3>
								<button type="button" class="remove_row button"><?php _e( 'Remove', 'woocommerce' ); ?></button>
								<div class="handlediv" title="<?php _e( 'Click to toggle', 'woocommerce' ); ?>"></div>
								<strong class="attribute_name"><?php echo apply_filters( 'woocommerce_attribute_label', $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name, $tax->attribute_name, null ); ?></strong>
							</h3>
							<table cellpadding="0" cellspacing="0" class="woocommerce_attribute_data wc-metabox-content">
								<tbody>
									<tr>
										<td class="attribute_name">
											<label><?php _e( 'Name', 'woocommerce' ); ?>:</label>
											<strong><?php echo $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name; ?></strong>

											<input type="hidden" name="attribute_names[<?php echo $i; ?>]" value="<?php echo esc_attr( $attribute_taxonomy_name ); ?>" />
											<input type="hidden" name="attribute_position[<?php echo $i; ?>]" class="attribute_position" value="<?php echo esc_attr( $position ); ?>" />
											<input type="hidden" name="attribute_is_taxonomy[<?php echo $i; ?>]" value="1" />
										</td>
										<td rowspan="3">
											<label><?php _e( 'Value(s)', 'woocommerce' ); ?>:</label>
											<?php if ( 'select' == $tax->attribute_type ) : ?>
												<select multiple="multiple" data-placeholder="<?php _e( 'Select terms', 'woocommerce' ); ?>" class="multiselect attribute_values wc-enhanced-select" name="attribute_values[<?php echo $i; ?>][]">
													<?php
													$all_terms = get_terms( $attribute_taxonomy_name, 'orderby=name&hide_empty=0' );
													if ( $all_terms ) {
														foreach ( $all_terms as $term ) {
															if($check_term_id){
																$has_term = in_array($term->term_id, $post_terms ) ? 1 : 0;
															}else{
																$has_term = in_array($term->slug, $post_terms ) ? 1 : 0;
															}
															echo '<option value="' . esc_attr( $term->term_id ) . '" ' . selected( $has_term, 1, false ) . '>' . $term->name . '</option>';
														}
													}
													?>
												</select>

												<button class="button plus select_all_attributes"><?php _e( 'Select all', 'woocommerce' ); ?></button> <button class="button minus select_no_attributes"><?php _e( 'Select none', 'woocommerce' ); ?></button>

												<button class="button fr plus add_new_attribute" data-attribute="<?php echo $attribute_taxonomy_name; ?>"><?php _e( 'Add new', 'woocommerce' ); ?></button>

											<?php elseif ( 'text' == $tax->attribute_type ) : ?>
												<input type="text" name="attribute_values[<?php echo $i; ?>]" value="<?php

													// Text attributes should list terms pipe separated
													if ( $post_terms ) {
														$values = array();
														
														foreach ( $post_terms as $term )
															$values[] = $term;
														echo esc_attr( implode( ' ' . WC_DELIMITER . ' ', $values ) );
													}

												?>" placeholder="<?php _e( 'Pipe (|) separate terms', 'woocommerce' ); ?>" />
											<?php endif; ?>
											<?php do_action( 'woocommerce_product_option_terms', $tax, $i ); ?>
										</td>
									</tr>
									<tr>
										<td>
											<label><input type="checkbox" class="checkbox" <?php

												if ( $is_visible ) {
													checked( $is_visible, true );
												} else {
													checked( apply_filters( 'default_attribute_visibility', false, $tax ), true );
												}

											?> name="attribute_visibility[<?php echo $i; ?>]" value="1" /> <?php _e( 'Visible on the product page', 'woocommerce' ); ?></label>
										</td>
									</tr>
									<tr>
										<td>
											<div class="enable_variation show_if_variable">
											<label><input type="checkbox" class="checkbox" <?php

												if ( $is_variation ) {
													checked( $is_variation, true );
												} else {
													checked( apply_filters( 'default_attribute_variation', false, $tax ), true );
												}

											?> name="attribute_variation[<?php echo $i; ?>]" value="1" /> <?php _e( 'Used for variations', 'woocommerce' ); ?></label>
											</div>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<?php
					}
				}

				// Custom Attributes
				if ( ! empty( $attributes ) ) {

					foreach ( $attributes as $position => $attribute ) {

						$is_taxonomy = $this->jcaa_is_taxonomy_attribute($attribute['name']);
						if ( $is_taxonomy ) {
							continue;
						}

						$i++;

						$is_visible = isset($attribute['visible']) && $attribute['visible'] === 'true' ? true : false;
						$is_variation = isset($attribute['variation']) && $attribute['variation'] === 'true' ? true : false;
						
						// $position = empty( $attribute['position'] ) ? 0 : absint( $attribute['position'] );
						?>
						<div class="woocommerce_attribute wc-metabox " rel="<?php echo $position; ?>">
							<h3>
								<button type="button" class="remove_row button"><?php _e( 'Remove', 'woocommerce' ); ?></button>
								<div class="handlediv" title="<?php _e( 'Click to toggle', 'woocommerce' ); ?>"></div>
								<strong class="attribute_name"><?php echo apply_filters( 'woocommerce_attribute_label', esc_html( $attribute['name'] ), esc_html( $attribute['name'] ), null ); ?></strong>
							</h3>
							<table cellpadding="0" cellspacing="0" class="woocommerce_attribute_data wc-metabox-content">
								<tbody>
									<tr>
										<td class="attribute_name">
											<label><?php _e( 'Name', 'woocommerce' ); ?>:</label>
											<input type="text" class="attribute_name" name="attribute_names[<?php echo $i; ?>]" value="<?php echo esc_attr( $attribute['name'] ); ?>" />
											<input type="hidden" name="attribute_position[<?php echo $i; ?>]" class="attribute_position" value="<?php echo esc_attr( $position ); ?>" />
											<input type="hidden" name="attribute_is_taxonomy[<?php echo $i; ?>]" value="0" />
										</td>
										<td rowspan="3">
											<label><?php _e( 'Value(s)', 'woocommerce' ); ?>:</label>
											<textarea name="attribute_values[<?php echo $i; ?>]" cols="5" rows="5" placeholder="<?php _e( 'Enter some text, or some attributes by pipe (|) separating values.', 'woocommerce' ); ?>"><?php echo esc_textarea( implode('|', (array)$attribute['values'] ) ); ?></textarea>
										</td>
									</tr>
									<tr>
										<td>
											<label><input type="checkbox" class="checkbox" <?php checked( $is_visible, 1 ); ?> name="attribute_visibility[<?php echo $i; ?>]" value="1" /> <?php _e( 'Visible on the product page', 'woocommerce' ); ?></label>
										</td>
									</tr>
									<tr>
										<td>
											<div class="enable_variation show_if_variable">
											<label><input type="checkbox" class="checkbox" <?php checked( $is_variation, 1 ); ?> name="attribute_variation[<?php echo $i; ?>]" value="1" /> <?php _e( 'Used for variations', 'woocommerce' ); ?></label>
											</div>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<?php
					}
				}
				?>
			</div>

			<p class="toolbar">
				<button type="button" class="button button-primary add_attribute"><?php _e( 'Add', 'woocommerce' ); ?></button>
				<select name="attribute_taxonomy" class="attribute_taxonomy">
					<option value=""><?php _e( 'Custom product attribute', 'woocommerce' ); ?></option>
					<?php
						if ( $attribute_taxonomies ) {

							foreach ( $attribute_taxonomies as $tax ) {
								$attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );

								$label = $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name;
								echo '<option value="' . esc_attr( $attribute_taxonomy_name ) . '">' . esc_html( $label ) . '</option>';
							}
						}
					?>
				</select>
			</p>
		</div>
		<?php
	}
}

new JCAA_Admin_Attribute_Groups();