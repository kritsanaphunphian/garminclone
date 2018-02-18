<?php
// Prefix country code to customer phone number
// You can add a custom placeholder to add a hint for your CUs what you expect
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields2' );

// Our hooked in function - $fields is passed via the filter!
function custom_override_checkout_fields2( $fields ) {
    
   $fields['billing']['billing_phone'] = array(
    'label'     => __('Phone', 'woocommerce'),
    'required'  => true,
    'class'     => array('form-row-wide'),
    'clear'     => true,
    'id'        => 'ssn'
    );

   return $fields;
}

/****************************************************************/
/* VALIDATION FOR PHONE FIELD THIS WILL THROW AN ERROR MESSAGE  */
/****************************************************************/

// add_action('woocommerce_checkout_process', 'my_custom_checkout_field_process');

// function my_custom_checkout_field_process() {
//    global $woocommerce;

//    // Check if set, if its not set add an error. This one is only requite for companies
//    if ( ! (preg_match('/^[0-9]{10}$/D', $_POST['billing_phone'] ))){
//        wc_add_notice( "The Phone should contain only 10 digits"  ,'error' );
//    }

// }
