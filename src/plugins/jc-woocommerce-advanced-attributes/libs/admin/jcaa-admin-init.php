<?php
/**
 * Load all admin classes
 *
 * @author James Collings <james@jclabs.co.uk>
 * @version 0.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once 'class-jcaa-admin-update.php';

// include admin files
if(JCAA()->get_settings('enable_attr_output') === 'yes'){
	include_once 'class-jcaa-admin-product-attrs-settings.php';
}

if(JCAA()->get_settings('enable_attr_groups') === 'yes'){
	include_once 'class-jcaa-admin-attribute-groups.php';
	include_once 'class-jcaa-admin-product-attribute-groups.php';
}

/**
 * Advanced Attribute settings
 * 
 * @param  array  $pages 
 * @return array
 */
function jcaa_register_settings($pages = array()){
	$pages[] = include_once 'class-jcaa-admin-settings.php';
	return $pages;
}
add_filter( 'woocommerce_get_settings_pages', 'jcaa_register_settings' );