<?php
/**
 * Add meta boxes to product attrs
 * 
 * @author James Collings <james@jclabs.co.uk>
 * @version 0.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class JCAA_Admin_Product_Attrs_Settings{

	public function __construct(){

		add_action( 'admin_footer', array( $this, 'admin_footer' ) );

		// do_action( 'woocommerce_attribute_added', $wpdb->insert_id, $attribute );
		add_action( 'woocommerce_attribute_added', array( $this, 'woocommerce_attribute_added'), 10, 2 );

		// do_action( 'woocommerce_attribute_updated', $attribute_id, $attribute, $old_attribute_name );
		add_action( 'woocommerce_attribute_updated', array( $this, 'woocommerce_attribute_updated'), 10, 3 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// hook onto all attribute taxonomies regiestered with woocommerce
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		foreach($attribute_taxonomies as $attr_tax){
			$tax_name = 'pa_'.$attr_tax->attribute_name;
			add_action( $tax_name . '_add_form_fields', array( $this, 'add_attribute_term_fields' ) );
			add_action( $tax_name . '_edit_form_fields', array( $this, 'edit_attribute_term_fields' ), 20, 2 );
		}

		add_action( 'created_term', array( $this, 'save_attribute_term_fields' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'save_attribute_term_fields' ), 10, 3 );
	}

	public function admin_scripts(){

		// load iris on attribute term pages
		if ( isset($_GET['taxonomy']) && strpos($_GET['taxonomy'], 'pa_') !== -1){
			wp_enqueue_script( 'iris' );
		}
	}

	/**
	 * Attribute Parent Fields
	 */
	
	public function admin_footer(){

		// temp find page
		$page = isset($_GET['page']) ? $_GET['page'] : false;
		$edit = isset($_GET['edit']) ? $_GET['edit'] : false;
		$taxonomy = isset($_GET['taxonomy']) ? $_GET['taxonomy'] : false;

		if($page !== 'product_attributes' || $taxonomy !== false){
			return;
		}

		if(!$edit){
			
			// add screen
			ob_start();
			?>
			<div class="form-field">
				<h3><?php _e('Advanced Attributes Display Settings', 'jcaa'); ?></h3>
			</div>

			<div class="form-field">
				<label for="jcaa_attribute_label"><?php _e( 'Custom Label', 'jcaa' ); ?></label>
				<input type="text" name="jcaa_attribute_label" id="jcaa_attribute_label" value="" />
				<p class="description"><?php _e('Overwrite attribute name on front end.', 'jcaa'); ?></p>
			</div>
			<div class="form-field">
				<label for="jcaa_attribute_grouped"><?php _e( 'Grouped', 'jcaa' ); ?></label>
				<input type="checkbox" name="jcaa_attribute_grouped" id="jcaa_attribute_grouped" value="1" />
				<p class="description"><?php _e( 'Check this checkbox to enable group formatting on product information tab, groups are seperated from attribute . (Group Name.attribute name)', 'jcaa' ); ?></p>
			</div>
			<div class="form-field">
				<label for="jcaa_attribute_type"><?php _e( 'Display Type', 'jcaa' ); ?></label>
				<select name="jcaa_attribute_type" id="jcaa_attribute_type">
					<option value="default"><?php _e( 'Default', 'jcaa' ); ?></option>
					<option value="image"><?php _e( 'Image', 'jcaa' ); ?></option>
					<option value="color"><?php _e( 'Color', 'jcaa' ); ?></option>
					<option value="text"><?php _e( 'Text', 'jcaa' ); ?></option>					
				</select>
				<p class="description"><?php _e( 'Type of the attribute output (shown on the front-end variation form).', 'jcaa' ); ?></p>
			</div>
			<div class="form-field  is_jcaa_enabled is_jcaa_color is_jcaa_image is_jcaa_text">
				<label for="jcaa_attribute_size">Size</label>
				<select name="jcaa_attribute_size" id="jcaa_attribute_size">
					<option value="small"><?php _e( 'Small', 'jcaa' ); ?></option>
					<option value="medium"><?php _e( 'Medium', 'jcaa' ); ?></option>
					<option value="large"><?php _e( 'Large', 'jcaa' ); ?></option>
				</select>
				<p class="description"><?php _e( 'Size of the attribute image (shown on the front-end variation form).', 'jcaa' ); ?></p>
			</div>
			<div class="form-field  is_jcaa_enabled is_jcaa_color is_jcaa_image">
				<label for="jcaa_attribute_style">Style</label>
				<select name="jcaa_attribute_style" id="jcaa_attribute_style">
					<option value="default"><?php _e( 'Default', 'jcaa' ); ?></option>
					<option value="rounded"><?php _e( 'Rounded', 'jcaa' ); ?></option>
				</select>
				<p class="description"><?php _e( 'Style of the attribute image (shown on the front-end variation form).', 'jcaa' ); ?></p>
			</div>
			<div class="form-field">
				<label for="jcaa_attribute_catalog"><?php _e( 'Display in Product Catalog', 'jcaa' ); ?></label>
				<select name="jcaa_attribute_catalog" id="jcaa_attribute_catalog">
					<option value="no"><?php _e( 'No', 'jcaa' ); ?></option>
					<option value="above_btn"><?php _e( 'Yes', 'jcaa' ); ?></option>
				</select>
				<p class="description"><?php _e( 'Display this attribute on product catalog pages.', 'jcaa' ); ?></p>
			</div>
			<?php
			$contents = ob_get_clean();
			$contents = preg_replace( "/\r|\n/", "", $contents );
			$contents = preg_replace( "/'/", "\'", $contents );
			?>
			<script type="text/javascript">
			jQuery(function($){
				// insert at bottom of form
				$('.form-wrap .form-field:last').after('<?php echo $contents; ?>');
			});
			</script>
			<?php
		}else{

			$jcaa_attribute_size = JCAA()->get_attr_setting($edit, 'jcaa_attribute_size');
			$jcaa_attribute_style = JCAA()->get_attr_setting($edit, 'jcaa_attribute_style');
			$jcaa_attribute_type = JCAA()->get_attr_setting($edit, 'jcaa_attribute_type');
			$jcaa_attribute_label = JCAA()->get_attr_setting($edit, 'jcaa_attribute_label');
			$jcaa_attribute_grouped = JCAA()->get_attr_setting($edit, 'jcaa_attribute_grouped');
			$jcaa_attribute_catalog = JCAA()->get_attr_setting($edit, 'jcaa_attribute_catalog');
			
			// edit screen
			ob_start();
			?>
			<tr>
				<td colspan="2"><h3><?php _e( 'Advanced Attributes Display Settings', 'jcaa' ); ?></h3></td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row" valign="top">
					<label for="jcaa_attribute_type"><?php _e( 'Custom Label', 'jcaa' ); ?></label>
				</th>
				<td>
					<input type="text" name="jcaa_attribute_label" id="jcaa_attribute_label" value="<?php echo $jcaa_attribute_label; ?>" />
					<p class="description"><?php _e( 'Overwrite attribute name on front end.', 'jcaa' ); ?></p>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row" valign="top">
					<label for="jcaa_attribute_grouped"><?php _e( 'Grouped', 'jcaa' ); ?></label>
				</th>
				<td>
					<input type="checkbox" name="jcaa_attribute_grouped" id="jcaa_attribute_grouped" value="1" <?php checked( 'yes', $jcaa_attribute_grouped, true ); ?> />
					<p class="description"><?php _e( 'Check this checkbox to enable group formatting on product information tab, groups are seperated from attribute . (Group Name.attribute name)', 'jcaa' ); ?></p>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row" valign="top">
					<label for="jcaa_attribute_type"><?php _e( 'Display Type', 'jcaa' ); ?></label>
				</th>
				<td>
					<select name="jcaa_attribute_type" id="jcaa_attribute_type">
						<option value="default" <?php selected( 'default', $jcaa_attribute_type, true ); ?>><?php _e( 'Default', 'jcaa' ); ?></option>
						<option value="image" <?php selected( 'image', $jcaa_attribute_type, true ); ?>><?php _e( 'Image', 'jcaa' ); ?></option>
						<option value="color" <?php selected( 'color', $jcaa_attribute_type, true ); ?>><?php _e( 'Color', 'jcaa' ); ?></option>
						<option value="text" <?php selected( 'text', $jcaa_attribute_type, true ); ?>><?php _e( 'Text', 'jcaa' ); ?></option>						
					</select>
					<p class="description"><?php _e( 'Type of the attribute output (shown on the front-end variation form).', 'jcaa' ); ?></p>
				</td>
			</tr>
			<tr class="form-field form-required is_jcaa_enabled is_jcaa_color is_jcaa_image is_jcaa_text">
				<th scope="row" valign="top">
					<label for="jcaa_attribute_size"><?php _e( 'Size', 'jcaa' ); ?></label>
				</th>
				<td>
					<select name="jcaa_attribute_size" id="jcaa_attribute_size">
						<option value="small" <?php selected( 'small', $jcaa_attribute_size, true ); ?>><?php _e( 'Small', 'jcaa' ); ?></option>
						<option value="medium" <?php selected( 'medium', $jcaa_attribute_size, true ); ?>><?php _e( 'Medium', 'jcaa' ); ?></option>
						<option value="large" <?php selected( 'large', $jcaa_attribute_size, true ); ?>><?php _e( 'Large', 'jcaa' ); ?></option>
					</select>
					<p class="description"><?php _e( 'Size of the attribute image (shown on the front-end variation form).', 'jcaa' ); ?></p>
				</td>
			</tr>
			<tr class="form-field form-required  is_jcaa_enabled is_jcaa_color is_jcaa_image">
				<th scope="row" valign="top">
					<label for="jcaa_attribute_style">Style</label>
				</th>
				<td>
					<select name="jcaa_attribute_style" id="jcaa_attribute_style">
						<option value="default" <?php selected( 'default', $jcaa_attribute_style, true ); ?>><?php _e( 'Default', 'jcaa' ); ?></option>
						<option value="rounded" <?php selected( 'rounded', $jcaa_attribute_style, true ); ?>><?php _e( 'Rounded', 'jcaa' ); ?></option>
					</select>
					<p class="description"><?php _e( 'Style of the attribute image (shown on the front-end variation form).', 'jcaa' ); ?></p>
				</td>
			</tr>

			<tr class="form-field form-required">
				<th scope="row" valign="top">
					<label for="jcaa_attribute_catalog"><?php _e( 'Display in Product Catalog', 'jcaa' ); ?></label>
				</th>
				<td>
					<select name="jcaa_attribute_catalog" id="jcaa_attribute_catalog">
					<option value="no" <?php selected( 'no', $jcaa_attribute_catalog, true ); ?>><?php _e( 'No', 'jcaa' ); ?></option>
					<option value="above_btn" <?php selected( 'above_btn', $jcaa_attribute_catalog, true ); ?>><?php _e( 'Yes', 'jcaa' ); ?></option>
				</select>
				<p class="description"><?php _e( 'Display this attribute on product catalog pages.', 'jcaa' ); ?></p>
				</td>
			</tr>

			<?php 
			$contents = ob_get_clean();
			$contents = preg_replace( "/\r|\n/", "", $contents );
			$contents = preg_replace( "/'/", "\'", $contents );
			?>

			<script type="text/javascript">
			jQuery(function($){
				// insert at bottom of form
				$('.form-table tr:last').after('<?php echo $contents; ?>');
			});
			</script>
			<?php
		}

		?>
		<script type="text/javascript">
		jQuery(function($){

			// if javascript is enabled hide toggle fields
			

			// show toggle fields
			$('#jcaa_attribute_type').on('change', function(){

				$('.is_jcaa_enabled').hide();

				if($(this).val() === 'text'){
					$('.is_jcaa_text').show();
				}else if($(this).val() === 'color'){
					$('.is_jcaa_color').show();
				}else if($(this).val() === 'image'){
					$('.is_jcaa_image').show();
				}
			});

			// initialise fields
			$('#jcaa_attribute_type').trigger('change');
		});
		</script>
		<?php
	}

	public function woocommerce_attribute_added($attribute_id, $attribute){
		
		$jcaa_attribute_size = isset($_POST['jcaa_attribute_size']) ? esc_attr( $_POST['jcaa_attribute_size'] ) : false;
		$jcaa_attribute_style = isset($_POST['jcaa_attribute_style']) ? esc_attr( $_POST['jcaa_attribute_style'] ) : false;
		$jcaa_attribute_type = isset($_POST['jcaa_attribute_type']) ? esc_attr( $_POST['jcaa_attribute_type'] ) : false;
		$jcaa_attribute_label = isset($_POST['jcaa_attribute_label']) ? esc_attr( $_POST['jcaa_attribute_label'] ) : false;
		$jcaa_attribute_grouped = isset($_POST['jcaa_attribute_grouped']) ? 'yes' : 'no';
		$jcaa_attribute_catalog = isset($_POST['jcaa_attribute_catalog']) ? esc_attr( $_POST['jcaa_attribute_catalog'] ) : 'no';
		$jcaa_attribute_group = '';

		// group names larger than 3
		if($jcaa_attribute_grouped === 'yes'){
			$label = $attribute['attribute_label'];
			if(strpos($label, '.') >= 3){
				$jcaa_attribute_group = substr($label, 0, strpos($label, '.'));
			}
		}
		
		$attr_settings = array(
			'jcaa_attribute_size' => $jcaa_attribute_size,
			'jcaa_attribute_style' => $jcaa_attribute_style,
			'jcaa_attribute_type' => $jcaa_attribute_type,
			'jcaa_attribute_label' => $jcaa_attribute_label,
			'jcaa_attribute_grouped' => $jcaa_attribute_grouped,
			'jcaa_attribute_group' => $jcaa_attribute_group,
			'jcaa_attribute_catalog' => $jcaa_attribute_catalog
		);

		update_option( "_jcaa_attr_settings_{$attribute_id}", $attr_settings );
	}

	public function woocommerce_attribute_updated($attribute_id, $attribute, $old_attribute_name){

		$this->woocommerce_attribute_added($attribute_id, $attribute);
	}

	/**
	 * Attribute Taxonomy Fields
	 */
	
	/**
	 * Attribute term add screen
	 * @param  object $term     
	 * @param  string $taxonomy 
	 * @return void
	 */
	public function add_attribute_term_fields(){

		$taxonomy = isset($_GET['taxonomy']) ?  $_GET['taxonomy']: false;
		if(!$taxonomy){
			return;
		}

		// get attribute id
		$attribute_id = jcaa_get_attribute_id_by_name($taxonomy);
		if(!$attribute_id){
			return;
		}

		// attribute type
		$display_type = JCAA()->get_attr_setting($attribute_id, 'jcaa_attribute_type');
		if(!in_array($display_type, array('image', 'color'))){
			return;
		}

		// enque wp media popup
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );

		$image = wc_placeholder_img_src();

		?>
		<h3>Advanced Attribute Display Settings</h3>

		<?php if($display_type === 'color'): // display color picker?>
		<div class="form-field" >
			<label for="jcaa_product_attr_color"><?php _e( 'Color', 'jcaa'); ?></label>
			<input name="jcaa_product_attr_color" id="jcaa_product_attr_color" class="jcaa_colorpick" type="text" value="" size="40" style="max-width:200px">
			<p class="description"><?php _e( 'The color that will be output to select this variation', 'jcaa'); ?></p>
		</div>
		<script type="text/javascript">
			jQuery(window).load(function(){

				// Color picker
				jQuery('.jcaa_colorpick').iris( {
					change: function(event, ui){
						jQuery(this).css( { backgroundColor: ui.color.toString() } );
					},
					hide: true,
					border: true
				} ).each( function() {
					jQuery(this).css( { backgroundColor: jQuery(this).val() } );
				})
				.click(function(){
					jQuery('.iris-picker').show();
				});
			});
		</script>
		<?php elseif($display_type === 'image'): // display media button ?>

		<div class="form-field">
			<label><?php _e( 'Thumbnail', 'woocommerce' ); ?></label>
			<div id="jcaa_product_attr_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo $image; ?>" width="60px" height="60px" /></div>
			<div style="line-height:60px;">
				<input type="hidden" id="jcaa_product_attr_thumbnail_id" name="jcaa_product_attr_thumbnail_id" value="" />
				<button type="submit" class="upload_image_button button"><?php _e( 'Upload/Add image', 'woocommerce' ); ?></button>
				<button type="submit" class="remove_image_button button"><?php _e( 'Remove image', 'woocommerce' ); ?></button>
			</div>
			<p class="description"><?php _e( 'The image that will be used to select this variation', 'jcaa'); ?></p>
			<script type="text/javascript">

				// Uploading files
				var file_frame;

				jQuery(document).on( 'click', '.upload_image_button', function( event ){

					event.preventDefault();

					// If the media frame already exists, reopen it.
					if ( file_frame ) {
						file_frame.open();
						return;
					}

					// Create the media frame.
					file_frame = wp.media.frames.downloadable_file = wp.media({
						title: '<?php _e( 'Choose an image', 'woocommerce' ); ?>',
						button: {
							text: '<?php _e( 'Use image', 'woocommerce' ); ?>',
						},
						multiple: false
					});

					// When an image is selected, run a callback.
					file_frame.on( 'select', function() {
						attachment = file_frame.state().get('selection').first().toJSON();

						jQuery('#jcaa_product_attr_thumbnail_id').val( attachment.id );
						jQuery('#jcaa_product_attr_thumbnail img').attr('src', attachment.url );
						jQuery('.remove_image_button').show();
					});

					// Finally, open the modal.
					file_frame.open();
				});

				jQuery(document).on( 'click', '.remove_image_button', function( event ){
					jQuery('#jcaa_product_attr_thumbnail img').attr('src', '<?php echo wc_placeholder_img_src(); ?>');
					jQuery('#jcaa_product_attr_thumbnail_id').val('');
					jQuery('.remove_image_button').hide();
					return false;
				});

			</script>
			<div class="clear"></div>
		</div>
		<?php
		endif;
	}

	/**
	 * Attribute term edit screen
	 * @param  object $term     
	 * @param  string $taxonomy 
	 * @return void
	 */
	public function edit_attribute_term_fields($term, $taxonomy){

		$taxonomy = isset($_GET['taxonomy']) ?  $_GET['taxonomy']: false;
		if(!$taxonomy){
			return;
		}

		// get attribute id
		$attribute_id = jcaa_get_attribute_id_by_name($taxonomy);
		if(!$attribute_id){
			return;
		}

		// attribute type
		$display_type = JCAA()->get_attr_setting($attribute_id, 'jcaa_attribute_type');
		if(!in_array($display_type, array('image', 'color'))){
			return;
		}

		// enque wp media popup
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );

		$image 			= '';
		$thumbnail_id 	= absint( get_woocommerce_term_meta( $term->term_id, '_jcaa_product_attr_thumbnail_id', true ) );
		if ( $thumbnail_id )
			$image = wp_get_attachment_thumb_url( $thumbnail_id );
		else
			$image = wc_placeholder_img_src();

		$color = get_woocommerce_term_meta( $term->term_id, '_jcaa_product_attr_color', true );

		?>
		<tr>
			<td colspan="2"><h3><?php _e('Advanced Attribute Display Settings', 'jcaa'); ?></h3></td>
		</tr>

		<?php if($display_type === 'color'): // display color picker?>
		<tr class="form-field jcaa_is_color">
			<th scope="row"><label for="jcaa_product_attr_color"><?php _e('Color', 'jcaa'); ?></label></th>
			<td>
				<input name="jcaa_product_attr_color" id="jcaa_product_attr_color" class="jcaa_colorpick" type="text" value="<?php echo $color; ?>" size="40" style="max-width:200px">
				<p class="description"><?php _e('The color that will be output to select this variation', 'jcaa'); ?></p>
			</td>
		</tr>
		<script type="text/javascript">
			jQuery(window).load(function(){

				// Color picker
				jQuery('.jcaa_colorpick').iris( {
					change: function(event, ui){
						jQuery(this).css( { backgroundColor: ui.color.toString() } );
					},
					hide: true,
					border: true
				} ).each( function() {
					jQuery(this).css( { backgroundColor: jQuery(this).val() } );
				})
				.click(function(){
					jQuery('.iris-picker').show();
				});
			});
		</script>
		<?php elseif($display_type === 'image'): ?>

		<tr class="form-field jcaa_is_image">
			<th scope="row" valign="top"><label><?php _e( 'Thumbnail', 'woocommerce' ); ?></label></th>
			<td>
				<div id="jcaa_product_attr_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo $image; ?>" width="60px" height="60px" /></div>
				<div style="line-height:60px;">
					<input type="hidden" id="jcaa_product_attr_thumbnail_id" name="jcaa_product_attr_thumbnail_id" value="<?php echo $thumbnail_id; ?>" />
					<button type="submit" class="upload_image_button button"><?php _e( 'Upload/Add image', 'woocommerce' ); ?></button>
					<button type="submit" class="remove_image_button button"><?php _e( 'Remove image', 'woocommerce' ); ?></button>
				</div>
				<p class="description"><?php _e('The image that will be used to select this variation', 'jcaa'); ?></p>
				<script type="text/javascript">

					// Uploading files
					var file_frame;

					jQuery(document).on( 'click', '.upload_image_button', function( event ){

						event.preventDefault();

						// If the media frame already exists, reopen it.
						if ( file_frame ) {
							file_frame.open();
							return;
						}

						// Create the media frame.
						file_frame = wp.media.frames.downloadable_file = wp.media({
							title: '<?php _e( 'Choose an image', 'woocommerce' ); ?>',
							button: {
								text: '<?php _e( 'Use image', 'woocommerce' ); ?>',
							},
							multiple: false
						});

						// When an image is selected, run a callback.
						file_frame.on( 'select', function() {
							attachment = file_frame.state().get('selection').first().toJSON();

							jQuery('#jcaa_product_attr_thumbnail_id').val( attachment.id );
							jQuery('#jcaa_product_attr_thumbnail img').attr('src', attachment.url );
							jQuery('.remove_image_button').show();
						});

						// Finally, open the modal.
						file_frame.open();
					});

					jQuery(document).on( 'click', '.remove_image_button', function( event ){
						jQuery('#jcaa_product_attr_thumbnail img').attr('src', '<?php echo wc_placeholder_img_src(); ?>');
						jQuery('#jcaa_product_attr_thumbnail_id').val('');
						jQuery('.remove_image_button').hide();
						return false;
					});

				</script>
				<div class="clear"></div>
			</td>
		</tr>
		<?php
		endif;
	}

	/**
	 * Save Attribute term custom fields
	 * @param  int $term_id  
	 * @param  int $att_id    
	 * @param  string $taxonomy 
	 * @return void
	 */
	public function save_attribute_term_fields( $term_id, $att_id, $taxonomy ){

		if ( isset( $_POST['jcaa_product_attr_thumbnail_id'] ) ) {
			update_woocommerce_term_meta( $term_id, '_jcaa_product_attr_thumbnail_id', esc_attr( $_POST['jcaa_product_attr_thumbnail_id'] ) );
		}

		if ( isset( $_POST['jcaa_product_attr_color'] ) ) {
			update_woocommerce_term_meta( $term_id, '_jcaa_product_attr_color', esc_attr( $_POST['jcaa_product_attr_color'] ) );
		}
	}
}

new JCAA_Admin_Product_Attrs_Settings();