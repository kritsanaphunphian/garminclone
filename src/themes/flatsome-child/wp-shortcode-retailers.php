<?php 
function add_shortcode_retailers( $atts ) {
    ob_start();
    get_template_part( 'query-shop-retailer' );
    return ob_get_clean();
}
add_shortcode( 'retailer_shortcode', 'add_shortcode_retailers' );
