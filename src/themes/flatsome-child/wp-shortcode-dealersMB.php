<?php 
function add_shortcode_dealersMB( $atts ) {
    ob_start();
    get_template_part( 'query-shop-dealerMB' );
    return ob_get_clean();
}
add_shortcode( 'dealer_shortcodeMB', 'add_shortcode_dealersMB' );
