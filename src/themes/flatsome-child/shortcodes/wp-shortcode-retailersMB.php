<?php 
function add_shortcode_retailersMB( $atts ) {
    ob_start();
    get_template_part( 'query-shop-retailerMB' );
    return ob_get_clean();
}
add_shortcode( 'retailer_shortcodeMB', 'add_shortcode_retailersMB' );
