<?php

function custom_shop_display_post_meta( $atts ) {
    
        global $product;
        
        // replace the custom field name with your own
        $catalog_description = get_post_meta( $product->id, 'catalog_description', true );
        
        // Add these fields to the shop loop if set
        if ( ! empty( $catalog_description ) ) {
            echo '<div class="box-excerpt is-small catalog-short">' . $catalog_description . '</div>';
            echo '<p>';
        }if( empty( $catalog_description ) ){
            echo '<p><p>';
        }
    }
add_action('flatsome_product_box_after', 'custom_shop_display_post_meta', 20);
