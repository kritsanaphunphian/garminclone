<?php 

function custom_shortcode( $atts ) {
    
    $value = get_field("telephone");

    echo $value;

}
add_shortcode( 'my-short-code', 'custom_shortcode' );

function custom_nostra_shortcode(){

echo '<iframe src="http://giswoocommerce.dev/wp-content/themes/flatsome-child/addPin.php" style="border:none;" height="600" width="100%"></iframe>';

}
add_shortcode( 'my-nostra-shortcode', 'custom_nostra_shortcode' );

