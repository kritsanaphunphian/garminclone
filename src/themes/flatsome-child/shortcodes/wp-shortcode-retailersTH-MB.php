<?php 
function add_shortcode_retailersTH_MB( $atts ) {
    ob_start();
    get_template_part( 'query-shop-retailerTH-MB' );
    return ob_get_clean();
}
add_shortcode( 'retailer_shortcodeTH-MB', 'add_shortcode_retailersTH_MB' );
