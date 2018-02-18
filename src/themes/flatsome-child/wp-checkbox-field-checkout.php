<?php
 
function my_custom_checkout_field( $checkout ) {
 
    echo '<div id="require_tax_checkfield"><h3>'.__('Require Tax Invoice : ').'</h3>';
 
    woocommerce_form_field( 'my_tax_invoice', array(
        'type'          => 'checkbox',
        'class'         => array('input-checkbox'),
        'label'         => __('I want tax invoice'),
        'required'  => false,
        ), $checkout->get_value( 'my_tax_invoice' ));

    echo '</div>';

    echo '<div id="tax_info" style="display: none"><h4>'.__('Tax Information : ').'</h4>';

    woocommerce_form_field( 'tax_personal_id', array(
        'type'          => 'text',
        'class'         => array('input-text'),
        'placeholder'         => __('Tax Id'),
        'required'  => false,
        ), $checkout->get_value( 'tax_personal_id' ));

    woocommerce_form_field( 'tax_company_id', array(
        'type'          => 'text',
        'class'         => array('input-text'),
        'placeholder'         => __('Head Office/Branch'),
        'required'  => false,
        ), $checkout->get_value( 'tax_company_id' ));
 
    echo '</div>';

}

add_action('wp_footer','visible_tax_fields');
function visible_tax_fields(){
    ?>
    <script type="text/javascript">
       (function($){
            $(function(){
                $('input[type="checkbox"]').click(function(){
                    if ($("#my_tax_invoice").is(":checked")) {
                    var taxinfo =  document.getElementById("tax_info");
                    taxinfo.style.display = 'block';

                    } else {

                    var taxinfo =  document.getElementById("tax_info");
                    taxinfo.style.display = 'none';
                    }
                
                });
            });
        })(jQuery);
    </script>
    <?php
}

add_action('woocommerce_after_order_notes', 'my_custom_checkout_field');

/**
 * Update the order meta with field value
 **/

function my_custom_checkout_field_update_order_meta( $order_id ) {
    if ($_POST['my_tax_invoice']) update_post_meta( $order_id, '_require_tax_invoice', esc_attr($_POST['my_tax_invoice']));
    if ($_POST['tax_personal_id']) update_post_meta( $order_id, '_tax_personal_id', esc_attr($_POST['tax_personal_id']));
    if ($_POST['tax_company_id']) update_post_meta( $order_id, '_tax_company_id', esc_attr($_POST['tax_company_id']));
}

add_action('woocommerce_checkout_update_order_meta', 'my_custom_checkout_field_update_order_meta');



function my_custom_checkout_field_display_admin_order_meta($order){
    $require_tax = get_post_meta( $order->id, '_require_tax_invoice', true );
    $tax_personal_id = get_post_meta( $order->id, '_tax_personal_id', true );
    $tax_company_id = get_post_meta( $order->id, '_tax_company_id', true );
    
    if($require_tax == '1' && !empty($tax_personal_id) && empty($tax_company_id)){
        echo '<div id="my-new-field"><h3>'.__('Require Tax Invoice : ').'Yes </h3></div>';
        echo '<div id="my-new-field">'.__('Tax Id : '). $tax_personal_id .'</div>';
    }else if($require_tax == '1' && !empty($tax_personal_id) && !empty($tax_company_id)){
        echo '<div id="my-new-field"><h3>'.__('Require Tax Invoice : ').'Yes (For Company)</h3></div>';
        echo '<div id="my-new-field">'.__('Tax Id : '). $tax_personal_id .'</div>';
        echo '<div id="my-new-field">'.__('Head Office/Branch : '). $tax_company_id .'</div>';
    }else{
        echo '<div id="my-new-field"><h3>'.__('Require Tax Invoice : ').'No </h3></div>';
    }   
}

add_action( 'woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );

