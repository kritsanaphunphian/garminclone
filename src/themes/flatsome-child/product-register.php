<?php
//sending mail
if(isset($_POST['send-serial']))
{
    
    add_filter( 'wp_mail_content_type', 'wpdocs_set_html_mail_content_type' );

    $user_id = get_current_user_id();
    $user = get_userdata( $user_id );

    $firstname = get_user_meta( $user_id, 'first_name', true );
    $lastname = get_user_meta( $user_id, 'last_name' , true );
    $tel = get_user_meta( $user_id, 'billing_phone' , true );
    $useremail = $user->user_email;

    $serailProduct = $_POST['serail-product']; 

    $headers[] = 'CC: kritsana.phunpian@gmail.com';
        
    $to = array(
        'kolokolo.jack@gmail.com'
        );
    $subject = 'Serial Number';
    $body = '<p>Serial Number product ' . $serailProduct . '</p> 
    <p>Name : ' . $firstname . ' ' . $lastname . '</p>
    <p>Email : ' . $useremail . '</p>
    <p>Tel : ' . $tel . '</p>';

    wp_mail( $to, $subject, $body, $headers );

    ?>
    <div class="woocommerce-message message-wrapper">
		<div class="message-container container success-color medium-text-center sb-custom-alert">
			<i class="icon-checkmark"><strong>Success:</strong> Your serial number product has sended.</i>
		</div>
	</div>
    <?php
    
    // Reset content-type to avoid conflicts -- https://core.trac.wordpress.org/ticket/23578
    remove_filter( 'wp_mail_content_type', 'wpdocs_set_html_mail_content_type' );
    function wpdocs_set_html_mail_content_type() {
        return 'text/html';
    }
}
?>
<form name="frm" method="post" action="#">
<p><h3>Register your GARMIN products to be eligible for benefits.</h3></p>
<label for="serail-product">Please specify your product's serial number:</label>
<input type="text" value="" name="serail-product" id="serail-product"><br/>
<input type="submit" value="Submit" name="send-serial" id="send-serial">
</form>
<?php
