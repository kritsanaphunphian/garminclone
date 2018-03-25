<?php
function map_register_shortcode( $atts ) {
    ob_start();
    get_template_part( 'map-register' );
    return ob_get_clean();
}
add_shortcode( 'register_map', 'map_register_shortcode' );
