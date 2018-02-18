<?php

function product_register_shortcode( $atts ){

    ob_start();
    get_template_part('product-register');
    return ob_get_clean();
    
}
add_shortcode('register_product','product_register_shortcode');
