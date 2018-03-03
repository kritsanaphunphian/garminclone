<?php
/**
 * Advanced Attribute Settings 
 *
 * Add WooCommerce settings page for custom options
 *
 * @author James Collings <james@jclabs.co.uk>
 * @version 0.2
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class JCAA_Admin_Settings extends WC_Settings_Page {

	public function __construct(){
		$this->id    = 'advanced-attributes';
		$this->label = __( 'Advanced Attributes', 'jcaa' );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );

		add_action( 'woocommerce_sections_'.$this->id, array( $this, 'before_settings_output' ), 5 );
		add_action( 'woocommerce_settings_'.$this->id, array( $this, 'after_settings_output' ), 15 );
	}

	public function before_settings_output(){
		?>
		<style type="text/css">

		.jcaa_wrapper{
			position: relative;
		}

		.jcaa_wrapper .jcaa_right, .jcaa_wrapper .jcaa_left{
			width: 100%;
			position: static;
		}

		.jcaa_wrapper .jcaa_right{
			background: #FFF;
			border: 1px solid #CCC;
			margin-top: 10px;
		}

		.jcaa_inside{
			padding: 0 12px 12px;
			line-height: 1.4em;
			font-size: 13px;
		}

		.jcaa_inside p{
			margin:6px 0 0;
		}

		.jcaa_right h3{
			font-size: 14px;
			line-height: 1.4;
			padding: 8px 12px;
			margin: 0;
		}

		.jcaa_right ul{
			margin-bottom: 0;
		}

		.jcaa_version{
			margin:0;
			padding:10px;
			background: #5a9889;
			color: #FFF;
		}

		.jcaa_right hr{
			margin-top: 0;
		}

		@media screen and ( min-width: 782px ) {
			
			.jcaa_wrapper .jcaa_right{
				width: 250px;
				position: absolute;
				right: 0;	
				margin-top: 40px;		
			}

			

			.jcaa_wrapper .jcaa_left{
				padding-right: 270px;
				width: auto;
			}
		}

		</style>
		<div class="jcaa_wrapper">
		<div class="jcaa_right">
			<h3><span><?php _e('JC WooCommerce Advanced Attributes', 'jcaa'); ?></span></h3>
			<hr />
			<div class="jcaa_inside">
				<p><?php _e('Thank you for using this plugin, for more information on how to use it checkout the following links.', 'jcaa'); ?></p>
				<ul>
					<li><a href="http://jamescollings.co.uk/docs/v1/jc-woocommerce-advanced-product-attributes/?ref=<?php echo site_url('/'); ?>" target="_blank"><?php _e('Documentation', 'jcaa'); ?></a></li>
					<li><a href="http://jamescollings.co.uk/support/forum/jc-woocommerce-advanced-product-attributes/?ref=<?php echo site_url('/'); ?>" target="_blank"><?php _e('Support', 'jcaa'); ?></a></li>
					<li><a href="http://jamescollings.co.uk/wordpress-plugins/jc-woocommerce-advanced-product-attributes/?ref=<?php echo site_url('/'); ?>" target="_blank"><?php _e('About', 'jcaa'); ?></a></li>
				</ul>
			</div>
			<p class="jcaa_version">
				Version: <strong><?php echo JCAA()->get_version(); ?></strong>
			</p>
		</div>
		<div class="jcaa_left">
		<?php
	}

	public function after_settings_output(){
		?>
		</div><!-- end of jcaa_left -->
		</div><!-- end of jcaa_wrapper -->
		<div class="clear"></div>
		<?php
	}

	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			''  => __( 'General', 'jcaa' ),
		);

		if(JCAA()->get_settings('enable_attr_output') === 'yes'){
			$sections['attributes'] = __('Attribute Style', 'jcaa');
		}

		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		$settings = array();

		if( 'attributes' == $current_section){
			
			/**
			 * Default attribute state
			 */
			$settings[] = array(	
				'title' => __( 'Attribute Output', 'jcaa' ), 
				'desc' => __( 'Change how the default attribute state is output', 'jcaa'),
				'type' => 'title', 
				'id' => 'jcaa_default_attr_style'
			);
			
			$settings[] = array(
				'title'    => __( 'Border Size', 'jcaa' ),
				'desc'     => __( 'Size of pixel border in px', 'jcaa' ),
				'id'       => 'jcaa_attr_border_size',
				'default'  => JCAA()->get_settings('attr_border_size'),
				'type'     => 'number',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Default Border Color', 'jcaa' ),
				'desc'     => __( 'Default color of pixel border', 'jcaa' ),
				'id'       => 'jcaa_attr_border_color',
				'default'  => JCAA()->get_settings('attr_border_color'),
				'type'     => 'color',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Active Border Color', 'jcaa' ),
				'desc'     => __( 'Active color of pixel border', 'jcaa' ),
				'id'       => 'jcaa_attr_border_color_active',
				'default'  => JCAA()->get_settings('attr_border_color_active'),
				'type'     => 'color',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Padding Size', 'jcaa' ),
				'desc'     => __( 'Size of inside padding between border and attribute in px', 'jcaa' ),
				'id'       => 'jcaa_attr_padding_size',
				'default'  => JCAA()->get_settings('attr_padding_size'),
				'type'     => 'number',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Padding Color', 'jcaa' ),
				'desc'     => __( 'Colour of inside padding between border and attribute', 'jcaa' ),
				'id'       => 'jcaa_attr_padding_color',
				'default'  => JCAA()->get_settings('attr_padding_color'),
				'type'     => 'color',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Disabled Attribute Opacity', 'jcaa' ),
				'desc'     => __( 'Disabled opacity of attribute 0(0%)-1(100%)', 'jcaa' ),
				'id'       => 'jcaa_attr_opacity_disabled',
				'default'  => JCAA()->get_settings('attr_opacity_disabled'),
				'type'     => 'number',
				'custom_attributes' => array('step' => '0.1', 'min' => 0, 'max' => 1),				
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Enable on Additional Information Tab', 'jcaa' ),
				'desc'     => __( 'Enable advanced attributes on information tab, otherwise they will be displayed as default text', 'jcaa' ),
				'id'       => 'jcaa_enable_additional_info',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Choose Grouped Attributes Output', 'jcaa'),
				'desc'     => __( 'Choose how grouped attributes are displayed on the product information tab, by default they are displayed as a list', 'jcaa'),
				'id'       => 'jcaa_group_attr_term_display',
				'default'  => 'list',
				'type'     => 'select',
				'options'  => array( 'list' => __( 'List', 'jcaa' ), 'table' => __( 'Table', 'jcaa' ) ),
				'autoload' => false
			);
			// attr_opacity_disabled
			$settings[] = array( 'type' => 'sectionend', 'id' => 'jcaa_default_attr_style' );

			/**
			 * Attribute image size
			 */
			$settings[] = array(	
				'title' => __( 'Attribute Display Sizes', 'jcaa' ), 
				'desc' => __( 'Change the size how attributes are displayed', 'jcaa'),
				'type' => 'title', 
				'id' => 'jcaa_attr_image_size'
			);
			
			$settings[] = array(
				'title'    => __( 'Small Image Size', 'jcaa' ),
				'desc'     => __( 'Size of small image attribute in px', 'jcaa' ),
				'id'       => 'jcaa_image_size_small',
				'default'  => JCAA()->get_settings('image_size_small'),
				'type'     => 'number',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Medium Image Size', 'jcaa' ),
				'desc'     => __( 'Size of medium image attribute in px', 'jcaa' ),
				'id'       => 'jcaa_image_size_medium',
				'default'  => JCAA()->get_settings('image_size_medium'),
				'type'     => 'number',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Large Image Size', 'jcaa' ),
				'desc'     => __( 'Size of large image attribute in px', 'jcaa' ),
				'id'       => 'jcaa_image_size_large',
				'default'  => JCAA()->get_settings('image_size_large'),
				'type'     => 'number',
				'autoload' => false
			);
			
			$settings[] = array( 'type' => 'sectionend', 'id' => 'jcaa_attr_image_size' );

		}else{
			$settings[] = array(	
				'title' => __( 'General Settings', 'jcaa' ), 
				'desc' => __( 'Change settings to do with JC WooCommerce Advanced Attribute Plugin', 'jcaa'),
				'type' => 'title', 
				'id' => 'jcaa_general_options'
			);
			$settings[] = array(
				'title'    => __( 'Enable Attribute Display', 'jcaa' ),
				'desc'     => __( 'Enable attribute images and colour swatches', 'jcaa' ),
				'id'       => 'jcaa_enable_attr_output',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Display attributes on non variable products', 'jcaa' ),
				'desc'     => __( 'Output attributes on non variable products above the add to cart button', 'jcaa' ),
				'id'       => 'jcaa_enable_simple_attr_output',
				'default'  => 'no',
				'type'     => 'checkbox',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Enable Attribute Groups', 'jcaa' ),
				'desc'     => __( 'Enable attribute group management', 'jcaa' ),
				'id'       => 'jcaa_enable_attr_groups',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Enable Plugin Styles', 'jcaa' ),
				'desc'     => __( 'Enable css styles', 'jcaa' ),
				'id'       => 'jcaa_enable_css',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Enable Custom Plugin Validation', 'jcaa' ),
				'desc'     => __( 'Enable plugin attribute validation', 'jcaa' ),
				'id'       => 'jcaa_enable_validation',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Enable Click to Toggle Attribute Selection', 'jcaa' ),
				'desc'     => __( 'Allow the user to click to toggle the selection of an attribute, disable this if you want the user to not be able to clear there selection', 'jcaa' ),
				'id'       => 'jcaa_enable_attr_toggle',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Enable Theme Attribute Template Override', 'jcaa' ),
				'desc'     => __( 'Enable plugin to override woocommerce attribute list.', 'jcaa' ),
				'id'       => 'jcaa_enabled_attribute_template',
				'default'  => 'no',
				'type'     => 'checkbox',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Force Plugin Styles', 'jcaa' ),
				'desc'     => __( 'Check this option if your theme is overriding styles set in the Attribute Style tab.', 'jcaa' ),
				'id'       => 'jcaa_enabled_force_styles',
				'default'  => 'no',
				'type'     => 'checkbox',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Product Dimension Group', 'jcaa' ),
				'desc'     => __( 'Enter the Attribute group name you want to display product dimensions under (only works when Theme Attribute Template Override is Enabled).', 'jcaa' ),
				'id'       => 'jcaa_product_dimension_group',
				'default'  => '',
				'type'     => 'text',
				'autoload' => false
			);
			$settings[] = array( 'type' => 'sectionend', 'id' => 'jcaa_general_options' );
		}

		return $settings;
	}

	/**
	 * Save settings
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::save_fields( $settings );

		// force reload of settings
		JCAA()->load_settings();
	}

	/**
	 * Output the settings
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings($current_section);
		WC_Admin_Settings::output_fields( $settings );
	}
}

return new JCAA_Admin_Settings();