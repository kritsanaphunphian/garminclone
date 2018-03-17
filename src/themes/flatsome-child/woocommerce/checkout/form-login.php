<?php
/**
 * @see wp-content/plugins/woocommerce/templates/checkout/form-login.php
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( is_user_logged_in() || 'no' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) {
    return;
}

$info_message  = '<div class="garminbygis-wc-checkout-must-be-logged-in-message">';
$info_message .= apply_filters( 'woocommerce_checkout_login_message', __( 'Returning customer?', 'woocommerce' ) );
$info_message .= ' <a href="#" class="showlogin">' . __( 'Click here to login', 'woocommerce' ) . '</a>';
$info_message .= '</div>';
wc_print_notice( $info_message, 'notice' );

woocommerce_login_form(
    array(
        'message'  => __( 'If you have shopped with us before, please enter your details in the boxes below. If you are a new customer, please proceed to the Billing &amp; Shipping section.', 'woocommerce' ),
        'redirect' => wc_get_page_permalink( 'checkout' ),
        'hidden'   => true,
    )
);
