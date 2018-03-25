<?php 
function add_shortcode_retailersTH( $atts ) {
    ob_start();
    get_template_part( 'query-shop-retailerTH' );
    return ob_get_clean();
}
add_shortcode( 'retailer_shortcodeTH', 'add_shortcode_retailersTH' );
