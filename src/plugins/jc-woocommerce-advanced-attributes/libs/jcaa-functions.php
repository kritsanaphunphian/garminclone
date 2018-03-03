<?php
/**
 * Get attribute name by attribute_id
 * @param  string $taxonomy 
 * @return boolean
 */
function jcaa_get_attribute_id_by_name($taxonomy){

	global $wpdb;
	$taxonomy_name = substr($taxonomy, strlen('pa_'));
	$result = $wpdb->get_col( $wpdb->prepare("SELECT attribute_id FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name = %s", $taxonomy_name ) );
	if($result){
		$attribute_id = isset($result[0]) ? absint($result[0]) : false;
		return $attribute_id;
	}

	return false;
}

/**
 * Remove grouped attributes from attribute list, and add as element to group attribute
 *
 * @param WC_Product_Attribute[] $attributes
 *
 * @return mixed
 */
function jcaa_get_product_attributes($attributes){


	// list of groups
	$found = array();

	foreach( $attributes as $rawName => $data){

		// decode encoded attribute names
		$name = urldecode($rawName);

		$id = jcaa_get_attribute_id_by_name($name);
		$jcaa_attribute_grouped = JCAA()->get_attr_setting($id, 'jcaa_attribute_grouped');

		if( ($jcaa_attribute_grouped === 'yes' || !$data->is_taxonomy()) && $data->get_visible() == 1 ){

			// make all attributes JCAA_Product_Attributes
			$pa_name = $data->get_name();
			
			$data = new JCAA_Product_Attribute($data->get_data());
			if(strpos($pa_name,"pa_") === 0){
				$data->set_taxonomy($pa_name);
			}

			// remove label filter so custom label is not applied yet
			remove_filter( 'woocommerce_attribute_label', 'jcaa_attribute_label', 10, 2);

			// used get the correct translation
			$label = __(wc_attribute_label($data->get_name()), 'jcaa');

			// re-add filter
			add_filter( 'woocommerce_attribute_label', 'jcaa_attribute_label', 10, 2);

			$jcaa_attribute_group = '';
			$dotPos = strpos($label, '.');
			if($dotPos >= 3 && $dotPos < (strlen($label)+1)){
				$jcaa_attribute_group = substr($label, 0, $dotPos);

				// on non taxonomy attributes, allowing grouping
				if(!$data->is_taxonomy()){
					$data->set_name(substr($label, $dotPos+1));
				}

			}

			if(!empty($jcaa_attribute_group)){

				if(!isset($found[$jcaa_attribute_group])){

					$attributes[$jcaa_attribute_group] = new JCAA_Product_Attribute(array(
						'name' => $jcaa_attribute_group,
						'value' => '',
						'position' => '0',
						'is_visible' => 1,
						'is_variation' => 0,
						'is_taxonomy' => 0,
						'jcaa_attributes' => array()
					));

					$attributes[$jcaa_attribute_group]->set_visible(true);

					$found[$jcaa_attribute_group] = true;
				}

				$attributes[$jcaa_attribute_group]->add_grouped_term($data);

				unset($attributes[$rawName]);
			}
		}
	}

//	var_dump($attributes);
//	die();

	return $attributes;
}

if(!is_admin()){
	add_action( 'woocommerce_single_product_summary', 'jcaa_enable_output_grouped_attrs', 999 );
	add_action( 'woocommerce_single_product_summary', 'jcaa_disable_output_grouped_attrs', 0 );
	add_filter( 'woocommerce_attribute', 'jcaa_attribute', 10, 2 );
	add_filter( 'woocommerce_attribute_label', 'jcaa_attribute_label', 10, 2);
	add_action( 'woocommerce_single_product_summary', 'jcaa_show_simple_attributes', 25 );
}

/**
 * Enable Attribute Groups Output on product attributes list: $product->list_attributes()
 */
function jcaa_enable_output_grouped_attrs(){
	add_filter( 'woocommerce_product_get_attributes', 'jcaa_get_product_attributes', 10, 1 );
}

/**
 * Disable Attribute Groups Output at the end of product attributes list: $product->list_attributes()
 */
function jcaa_disable_output_grouped_attrs(){
	remove_filter( 'woocommerce_product_get_attributes', 'jcaa_get_product_attributes', 10, 1 );
}

/**
 * Output group attribites in product information
 *
 * @param $attribute
 * @param JCAA_Product_Attribute $values
 *
 * @return string
 */
function jcaa_attribute($attribute, $values){

	remove_filter( 'woocommerce_attribute', 'jcaa_attribute', 10, 2 );
	$term_seperator = '</span><span class="jcaa_attr_seperator">' . apply_filters( 'jcaa/term_seperator', ', ' ) . '</span><span class="jcaa_attr_value">';
	$attribute_seperator = apply_filters( 'jcaa/attribute_seperator', ': ' );

	$output_table = JCAA()->get_settings('group_attr_term_display') == 'table' ? true : false;

	/** @var WC_Product $product */
	global $product;
	if(get_class($values) !== 'JCAA_Product_Attribute'){
		add_filter( 'woocommerce_attribute', 'jcaa_attribute', 10, 2 );
		return $attribute;
	}

	$grouped_terms = $values->get_grouped_terms();
	if(is_array($grouped_terms) && !empty($grouped_terms)){

		if( $output_table ){
			// TABLE opening tabs
			$output = '<table class="jcaa_group_attr jcaa_group_table"><tbody>';
		}else{
			// LIST opening tabs
			$output = '<ul class="jcaa_group_attr jcaa_group_list">';
		}

        $counter = 0;

		foreach($grouped_terms as $attr_grouped){

            $counter++;

			// todo: allow custom display type
			$attr_values = wc_get_product_terms( $product->get_id(), $attr_grouped['name'], array( 'fields' => 'names' ) );
			$attr_label = wc_attribute_label($attr_grouped['name']);
			$attr_terms = apply_filters( 'woocommerce_attribute', wptexturize( implode( $term_seperator, $attr_values )  ), $attr_grouped, $attr_values );

            $row_class = ($counter % 2) == 0 ? 'alt' : '';

			if( $output_table ){
				// TABLE row
				$output .= '<tr class="jcaa_group_attr_term ' . $row_class . '"><th class="jcaa_attr_label">' . __($attr_label, 'jcaa') . '</th><td class="jcaa_attr_value">' . $attr_terms . '</td></tr>';
			}else{
				// LIST row
				$output .= '<li class="jcaa_group_attr_term ' . $row_class . '"><span class="jcaa_attr_label">'. __($attr_label, 'jcaa')  . $attribute_seperator .'</span> <span  class="jcaa_attr_value">' . $attr_terms .'</span></li>';
			}			
		}

		if( $output_table ){
			// TABLE closing tabs
			$output .= "</tbody></table>";
		}else{
			// LIST closing tabs
			$output .= '</ul>';
		}

		$attribute = $output;
	}

	add_filter( 'woocommerce_attribute', 'jcaa_attribute', 10, 2 );
	
	return $attribute;
}

/**
 * Replace label with custom label from attribute add/edit screen
 */

function jcaa_attribute_label($label, $name){

	// show full name in admin screens
	if(is_admin()){
		return $label;
	}

	global $wpdb;
	$id = $wpdb->get_var( $wpdb->prepare( "SELECT attribute_id FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s;", $name ) );
	$jcaa_attribute_label = JCAA()->get_attr_setting($id, 'jcaa_attribute_label');
	if(! empty($jcaa_attribute_label) ){
		return $jcaa_attribute_label;
	}	

	return $label;
}

/**
 * Show product attributes above add to cart button on non variable products
 * Request feature by ursacescu
 */
function jcaa_show_simple_attributes(){

	$enable_simple_attr_output = JCAA()->get_settings('enable_simple_attr_output');
	if($enable_simple_attr_output !== 'yes'){
		return;
	}

	/** @var WC_Product $product */
	global $product;

	if(!$product->is_type('variable')){
		jcaa_enable_output_grouped_attrs();
		$heading = apply_filters( 'woocommerce_product_additional_information_heading', __( 'Additional Information', 'woocommerce' ) );
		echo '<h2>' . $heading . '</h2>';
		$product->list_attributes();
		jcaa_disable_output_grouped_attrs();
	}
}

 // quickfix: reduce get product attribute calls
 // todo: restructure and stop using globals
 global $jcaa_product_attrs, $jcaa_product_id;
 $jcaa_product_attrs = $jcaa_product_id = null;


/**
 * Get advanced attribute settings for the passed attribute
 *
 * Convert the attribute into an advanced attribute, otherwise return false
 * @param  array  	$attr 	WC_Attribute
 * @return array 	JCAA_Attribute
 */
function jcaa_get_advanced_attribute($name){

	/** @var WC_Product $product */
	global $product, $jcaa_product_attrs, $jcaa_product_id;

	$id = jcaa_get_attribute_id_by_name($name);
	$settings = JCAA()->get_attr_setting($id);

	$result = array();
	$result['name'] = sanitize_title( $name ); //str_replace( 'pa_', '', sanitize_title( $name ) );
	$result['size'] = isset($settings['jcaa_attribute_size']) && !empty($settings['jcaa_attribute_size']) ? $settings['jcaa_attribute_size'] : 'small';
	$result['type'] = isset($settings['jcaa_attribute_type']) && !empty($settings['jcaa_attribute_type']) ? $settings['jcaa_attribute_type'] : 'default';
	$result['label'] = isset($settings['jcaa_attribute_label']) && !empty($settings['jcaa_attribute_label']) ? $settings['jcaa_attribute_label'] : 'show';
	$result['style'] = isset($settings['jcaa_attribute_style']) && !empty($settings['jcaa_attribute_style']) ? $settings['jcaa_attribute_style'] : 'default';
	$result['catalog'] = isset($settings['jcaa_attribute_catalog']) && !empty($settings['jcaa_attribute_catalog']) ? $settings['jcaa_attribute_catalog'] : 'no';
	$result['options'] = array();

	$terms = wc_get_product_terms( $product->get_id(), $name, array( 'fields' => 'all') );

	if($jcaa_product_attrs == null || $jcaa_product_id != $product->get_id()){
 		$jcaa_product_attrs = false;
 		$jcaa_product_id = $product->get_id();
 		if($product->is_type('variable')){

 			/** @var WC_Product_Variable $product */
			$jcaa_product_attrs = $product->get_variation_attributes();    
 		}
	}

	foreach($terms as $term){

		// is visible on product
//		if( $product->product_type == 'variable' && ( !isset( $jcaa_product_attrs[ $term->taxonomy ] ) || !in_array( $term->slug, $jcaa_product_attrs[ $term->taxonomy ] ) ) ){
//			continue;
//		}

		$option = array();

		// wpml check parent for attribute settings
//		$translated_term_id = false;

		// removed 22/05/2015 as this is no longer needed
//		if(function_exists('icl_object_id'))
//			$translated_term_id = icl_object_id($term->term_id, $name, true, icl_get_default_language());
//		$term_id = $translated_term_id > 0 ? $translated_term_id : $term->term_id;
		$term_id = $term->term_id;

		$option['name'] = $term->name;
		$option['value'] = $term->slug;
		$option['img'] =  wp_get_attachment_thumb_url( get_woocommerce_term_meta( $term_id, '_jcaa_product_attr_thumbnail_id', true ) );
		$option['color'] = get_woocommerce_term_meta( $term_id, '_jcaa_product_attr_color', true );
		$option['alt'] = $term->slug;			

		$result['options'][] = new JCAA_Product_Attribute($option);
	}

	// new way to define an attribute
	return new JCAA_Product_Attribute($result);
}

class JCAA_Product_Attribute extends WC_Product_Attribute {

	protected $sub_attributes = array();

	public function __construct($data) {

		$data['size'] = isset($data['size']) ? $data['size']: '';
		$data['type'] = isset($data['type']) ? $data['type']: '';
		$data['label'] = isset($data['label']) ? $data['label']: '';
		$data['style'] = isset($data['style']) ? $data['style']: '';
		$data['catalog'] = isset($data['catalog']) ? $data['catalog']: '';
		$data['value'] = isset($data['value']) ? $data['value']: '';
		$data['img'] = isset($data['img']) ? $data['img']: '';
		$data['color'] = isset($data['color']) ? $data['color']: '';

		// set default params
		$this->set_name($data['name']);
		$this->set_options(isset($data['options']) ? $data['options'] : array());

		// set JCAA options
		$this->set_size($data['size']);
		$this->set_type($data['type']);
		$this->set_label($data['label']);
		$this->set_style($data['style']);
		$this->set_catalog($data['catalog']);
		$this->set_value($data['value']);
		$this->set_img($data['img']);
		$this->set_color($data['color']);

		if(isset($data['position'])){
			$this->data['position'] = $data['position'];
		}
		if(isset($data['visible'])){
			$this->data['visible'] = $data['visible'];
		}
		if(isset($data['variation'])){
			$this->data['variation'] = $data['variation'];
		}
		if(isset($data['variation'])){
			$this->data['variation'] = $data['variation'];
		}
		if(isset($data['is_variation'])){
			$this->data['is_variation'] = $data['is_variation'];
		}
		if(isset($data['is_visible'])){
			$this->data['is_visible'] = $data['is_visible'];
		}
		if(isset($data['is_taxonomy'])){
			$this->data['is_taxonomy'] = $data['is_taxonomy'];
		}

		if(isset($data['jcaa_attributes']) && !empty($data['jcaa_attributes'])){
			array_merge($this->sub_attributes, $data['jcaa_attributes']);
		}
	}

	public function add_grouped_term($data){
		$this->sub_attributes[] = $data;
	}

	public function has_grouped_terms(){
		return !empty($this->sub_attributes);
	}

	public function get_grouped_terms(){
		return $this->sub_attributes;
	}

	public function is_type($type){
		if($this->data['type'] === $type){
			return true;
		}

		return false;
	}

	public function is_taxonomy(){
		return isset($this->data['is_taxonomy']) && $this->data['is_taxonomy'] == 1 ? true : false;
	}

	public function get_taxonomy() {

		if($this->is_taxonomy() && isset($this->data['taxonomy'])){
			return $this->data['taxonomy'];
		}

		return parent::get_taxonomy();
	}

	public function get_taxonomy_object() {
		
		if($this->is_taxonomy() && isset($this->data['taxonomy'])){

			global $wc_product_attributes;
			return $this->is_taxonomy() ? $wc_product_attributes[ $this->get_taxonomy() ] : null;
		}
		return parent::get_taxonomy_object();
	}

	/*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	*/

	public function set_size($value){
		$this->data['size'] = $value;
	}

	public function set_type($value){
		$this->data['type'] = $value;
	}

	public function set_label($value){
		$this->data['label'] = $value;
	}

	public function set_style($value){
		$this->data['style'] = $value;
	}

	public function set_catalog($value){
		$this->data['catalog'] = $value;
	}

	public function set_value($value){
		$this->data['value'] = $value;
	}

	public function set_img($value){
		$this->data['img'] = $value;
	}

	public function set_color($value){
		$this->data['color'] = $value;
	}

	public function set_taxonomy($value){
		$this->data['taxonomy'] = $value;
	}

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	*/

	public function get_size(){
		return $this->data['size'];
	}

	public function get_type(){
		return $this->data['type'];
	}

	public function get_label(){
		return $this->data['label'];
	}

	public function get_style(){
		return $this->data['style'];
	}

	public function get_catalog(){
		return $this->data['catalog'];
	}

	public function get_value(){
		return $this->data['value'];
	}

	public function get_img(){
		return $this->data['img'];
	}

	public function get_color(){
		return $this->data['color'];
	}

	public function export(){
		return $this->data;
	}
}