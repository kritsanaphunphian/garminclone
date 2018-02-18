<?php 

function add_shortcode_dealers( $atts ){

    ob_start();
    get_template_part('query-shop-dealer');
    return ob_get_clean();

}
add_shortcode('dealer_shortcode','add_shortcode_dealers');