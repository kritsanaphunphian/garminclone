<?php
/**
 * Plugin Name: JC WooCommerce Advanced Attributes
 * Plugin URI: http://jamescollings.co.uk/wordpress-plugins/jc-woocommerce-advanced-product-attributes/
 * Description: Add images to attributes and replace the dropdown with images
 * Version: 2.0.9
 * Author: James Collings
 * Author URI: http://www.jamescollings.co.uk
 * Text Domain: jcaa
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class JCWC_AdvancedAttrubutes {

	protected $version = '2.0.9';
	public $plugin_dir = false;
	public $plugin_url = false;
	protected $plugin_slug = false;
	protected $settings = false;

	/**
	 * Single instance of class
	 */
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct(){

		$this->plugin_dir =  plugin_dir_path( __FILE__ );
		$this->plugin_url = plugins_url( '/', __FILE__ );
		$this->plugin_slug = basename(dirname(__FILE__));

		include_once 'libs/jcaa-integrations.php';

		add_action( 'woocommerce_init', array( $this, 'init' ) );
	}

	public function includes(){

		include_once 'libs/jcaa-functions.php';

		if(is_admin()){
			include_once 'libs/admin/jcaa-admin-init.php';
		}
		
		if(JCAA()->get_settings('enable_attr_output') === 'yes'){
			include_once 'libs/class-jcaa-product-template.php';
		}
	}

	/**
	 * Initite donation
	 * @return void
	 */
	public function init(){

		$this->load_settings();	
		$this->includes();
	}

	/**
	 * Load plugin settings
	 * @return void
	 */
	public function load_settings(){

		// set default settings
		$this->settings = array(
			'attr_border_size' => '1',
			'attr_border_color' => '#DDDDDD',
			'attr_border_color_active' => '#333333',
			'attr_padding_size' => '2',
			'attr_padding_color' => '#FFFFFF',
			'attr_opacity_disabled' => '0.2',
			'enable_css' => 'yes',
			'enable_validation' => 'yes',
			'enable_attr_toggle' => 'yes',
			'image_size_small' => 20,
			'image_size_medium' => 30,
			'image_size_large' => 40,
			'enable_attr_output' => 'yes',
			'enable_attr_groups' => 'yes',
			'enable_additional_info' => 'yes',
			'enable_simple_attr_output' => 'no',
			'group_attr_term_display' => 'list',
			'enabled_attribute_template' => 'no',
			'enabled_force_styles' => 'no',
			'product_dimension_group' => ''
		);

		// load settings from db
		foreach($this->settings as $key => $default){
			$this->settings[$key] = get_option('jcaa_'.$key, $default);
		}

		// fetch image sizes from settings
		$small_image = $this->get_settings('image_size_small');
		$medium_image = $this->get_settings('image_size_medium');
		$large_image = $this->get_settings('image_size_large');

		// register image sizes
		add_image_size( 'jcaa_attribute_small', $small_image, $small_image, true );
		add_image_size( 'jcaa_attribute_medium', $medium_image, $medium_image, true );
		add_image_size( 'jcaa_attribute_large', $large_image, $large_image, true );
	}

	public function get_settings($key){
		return isset($this->settings[$key]) ? $this->settings[$key] : false;
	}

	public function get_version(){
		return $this->version;
	}
	public function get_plugin_slug(){
		return $this->plugin_slug;
	}
	public function get_plugin_url(){
		return $this->plugin_url;
	}
	public function get_plugin_dir(){
		return $this->plugin_dir;
	}

	/**
	 * Get attribute setting
	 * @param  int $attribute_id 
	 * @param  string $key          
	 * @return mixed
	 */
	public function get_attr_setting($attribute_id, $key = null){

		$settings = get_option("_jcaa_attr_settings_{$attribute_id}" );
		
		if($settings && isset($settings[$key])){
			return $settings[$key];
		}elseif($settings && $key == null){
			return $settings;
		}

		return false;
	}
}

function JCAA() {
	return JCWC_AdvancedAttrubutes::instance();
}

$GLOBALS['jcaa'] = JCAA();

// Localization
function jcaa_local_init(){

	$locale = apply_filters('plugin_locale', get_locale(), 'jcaa');
	load_textdomain('jcaa', WP_LANG_DIR.'/plugins/jcaa-'.$locale.'.mo');
	load_plugin_textdomain('jcaa', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
}
add_action('after_setup_theme', 'jcaa_local_init');