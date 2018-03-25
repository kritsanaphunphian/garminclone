<?php 
function add_shortcode_dealersTH_MB( $atts ) {
    ob_start();
    get_template_part( 'query-shop-dealerTH-MB' );
    return ob_get_clean();
}
add_shortcode( 'dealer_shortcodeTH-MB', 'add_shortcode_dealersTH_MB' );
