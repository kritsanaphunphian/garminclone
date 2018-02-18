<?php 

function add_shortcode_dealersTH( $atts ){

    ob_start();
    get_template_part('query-shop-dealerTH');
    return ob_get_clean();

}
add_shortcode('dealer_shortcodeTH','add_shortcode_dealersTH');