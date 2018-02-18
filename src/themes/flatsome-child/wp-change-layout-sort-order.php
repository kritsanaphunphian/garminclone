<?php
if ( function_exists( 'Woocommerce_Products_Per_Page' ) ) {
    add_action( 'flatsome_category_title_alt', array( Woocommerce_Products_Per_Page()->front_end, 'products_per_page_dropdown' ), 31 );
}
