<?php
/**
 * Add custom fields to qtranslate config
 */
add_filter('qtranslate_load_admin_page_config','jcaa_add_admin_page_config', 20);
function jcaa_add_admin_page_config($page_configs){

	if(!empty($page_configs)){
		foreach($page_configs as &$config){
			if(isset($config['pages']['edit.php']) && $config['pages']['edit.php'] == 'post_type=product&page=product_attributes'){
				$config['forms']['all']['fields']['jcaa_attribute_label'] = array();
			}
		}
	}

	return $page_configs;
}