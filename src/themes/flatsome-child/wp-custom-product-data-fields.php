<?php
// Display Fields
add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields');

// // Save Fields
add_action('woocommerce_process_product_meta', 'woocommerce_product_custom_fields_save');


function woocommerce_product_custom_fields()
{
    global $woocommerce, $post;
    echo '<div class="product_custom_field">';
    // Custom Product Text Field
    woocommerce_wp_text_input(
        array(
            'id' => '_product_old_price',
            'type' => 'number',
            'label' => __('Old Price', 'woocommerce'),
            'desc_tip' => 'true'
        )
    );
    //Custom Product Number Field
    woocommerce_wp_text_input(
        array(
            'id' => '_partnumber_product_field_simple',
            'placeholder' => 'e.g. 088-23919392-123',
            'label' => __('Part Number', 'woocommerce'),
            'custom_attributes' => array(
                'step' => 'any',
                'min' => '0'
            )
        )
    );
    //Custom Product  Textarea
    // woocommerce_wp_textarea_input(
    //     array(
    //         'id' => '_custom_product_textarea',
    //         'placeholder' => 'Custom Product Textarea',
    //         'label' => __('Custom Product Textarea', 'woocommerce')
    //     )
    // );
    echo '</div>';

}

//save custom data product
function woocommerce_product_custom_fields_save($post_id)
{
    // Custom Product Text Field
    $_product_old_price = $_POST['_product_old_price'];
    if (!empty($_product_old_price)){
        update_post_meta($post_id, '_product_old_price', esc_attr($_product_old_price));
    }else{
        update_post_meta($post_id, '_product_old_price', esc_attr($_product_old_price));
    }
    // Custom Product Number Field
    $_partnumber_product_field_simple = $_POST['_partnumber_product_field_simple'];
    if (!empty($_partnumber_product_field_simple)){
        update_post_meta($post_id, '_partnumber_product_field_simple', esc_attr($_partnumber_product_field_simple));
    }else{
        update_post_meta($post_id, '_partnumber_product_field_simple', esc_attr($_partnumber_product_field_simple));
    }
    // Custom Product Textarea Field
    // $woocommerce_custom_procut_textarea = $_POST['_custom_product_textarea'];
    // if (!empty($woocommerce_custom_procut_textarea))
    //     update_post_meta($post_id, '_custom_product_textarea', esc_html($woocommerce_custom_procut_textarea));

}




