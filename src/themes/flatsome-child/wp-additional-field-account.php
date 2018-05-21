<?php

function my_woocommerce_edit_account_form() {
    $user_id = get_current_user_id();
    $user    = get_userdata( $user_id );
 
    if ( ! $user ) {
        return;
    }
 
    $birthday = get_user_meta( $user_id, 'birthday', true );
    $gender   = get_user_meta( $user_id, 'gender' , true );
    $phoneno  = get_user_meta( $user_id, 'billing_phone', true);
 
    ?>
    <fieldset>
        <legend><?php _e( 'Additional information', 'woocommerce' ); ?></legend>

        <?php wp_enqueue_script( 'jquery-ui-datepicker' ); ?>

        <p class="form-row form-row-thirds">
            <label for="birthday"><?php _ex( 'Birth date', 'myaccount additional information fields', 'garminbygis' ); ?> : <span class="required">*</span></label>
            <input type="text" id="birth-date" name="birthday" value="<?php echo esc_attr( $birthday ); ?>" />
            <span style="font-size: 12px;"><?php _ex( '(Birth date format: DD-MM-YYYY. eg: 01/12/1990)', 'myaccount additional information fields', 'garminbygis' ); ?></span>
        </p>

        <p class="form-row form-row-thirds">
            <label for="gender"><?php _ex( 'Gender', 'myaccount additional information fields', 'garminbygis' ); ?> : </label>
            <select name="gender" class="input-text" value="<?php echo esc_attr( $gender ); ?>">
                <option value="male" <?php if ( $gender == 'male' ) echo 'selected="selected"'; ?>><?php _ex( 'male', 'myaccount additional information fields', 'garminbygis' ); ?></option>
                <option value="female" <?php if ( $gender == 'female' ) echo 'selected="selected"'; ?>><?php _ex( 'female', 'myaccount additional information fields', 'garminbygis' ); ?></option>
            </select>
            <span style="font-size: 12px;"><?php _ex( '(Gender format: male/female)', 'myaccount additional information fields', 'garminbygis' ); ?></span>
        </p>

        <p class="form-row form-row-thirds">
            <label for="phoneno"><?php _ex( 'Phone no', 'myaccount additional information fields', 'garminbygis' ); ?> : </label>
            <input type="text" id="ssn" name="billing_phone" value="<?php echo esc_attr( $phoneno ); ?>"/>
            <span style="font-size: 12px;"><?php _ex( '(Phone no. format: 088-664-4321)', 'myaccount additional information fields', 'garminbygis' ); ?></span>
        </p>
    </fieldset>

    <script src="//cdnjs.cloudflare.com/ajax/libs/validate.js/0.12.0/validate.min.js"></script>
    <script type="text/javascript">
        jQuery( document ).ready(function( $ ) {

            <?php if ( get_locale() == 'th' ) : ?>
                $.datepicker.regional['th'] = {
                    closeText          : "ปิด",
                    prevText           : "&#xAB;&#xA0;ย้อน",
                    nextText           : "ถัดไป&#xA0;&#xBB;",
                    currentText        : "วันนี้",
                    monthNames         : [ "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม" ],
                    monthNamesShort    : [ "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค." ],
                    dayNames           : [ "อาทิตย์", "จันทร์", "อังคาร", "พุธ", "พฤหัสบดี", "ศุกร์", "เสาร์" ],
                    dayNamesShort      : [ "อา.", "จ.", "อ.", "พ.", "พฤ.", "ศ.", "ส." ],
                    dayNamesMin        : [ "อา.", "จ.", "อ.", "พ.", "พฤ.", "ศ.", "ส." ],
                    weekHeader         : "Wk",
                    dateFormat         : "dd/mm/yy",
                    firstDay           : 0,
                    isRTL              : false,
                    showMonthAfterYear : false,
                    yearSuffix         : ""
                };
                $.datepicker.setDefaults($.datepicker.regional['th']);
            <?php endif; ?>

            $('#birth-date').datepicker({
                yearRange   : "-100:+0",
                changeMonth : true,
                changeYear  : true,
                dateFormat  : 'dd/mm/yy',
                maxDate     : new Date
            });

            // Just do basic validation here.
            // We could move or use a 3rd-party lib to handle a form validation
            // later if we need to repeat this code again and again.
            jQuery( '.woocommerce-EditAccountForm' ).submit(function( e ) {
                var isValid   = true,
                    firstname = jQuery( '#account_first_name' ),
                    lastname  = jQuery( '#account_last_name' ),
                    email     = jQuery( '#account_email' ),
                    birthdate = jQuery( '#birth-date' );

                firstname.removeClass( 'input-field-invalid' );
                lastname.removeClass( 'input-field-invalid' );
                email.removeClass( 'input-field-invalid' );
                birthdate.removeClass( 'input-field-invalid' );

                jQuery( '.validation-message' ).remove();

                if ( validate.isEmpty( firstname.val() ) || ! validate.isString( firstname.val() ) ) {
                    firstname.addClass( 'input-field-invalid' );
                    firstname.parent().append( '<span class="validation-message text-warning"><?php _e( 'First name field cannot be blank.', 'garminbygis' ) ?></span>' );

                    if ( isValid ) firstname.focus();

                    isValid = false;
                }

                if ( validate.isEmpty( lastname.val() ) || ! validate.isString( lastname.val() ) ) {
                    lastname.addClass( 'input-field-invalid' );
                    lastname.parent().append( '<span class="validation-message text-warning"><?php _e( 'Last name field cannot be blank.', 'garminbygis' ) ?></span>' );

                    if ( isValid ) lastname.focus();

                    isValid = false;
                }

                if ( validate.isEmpty( email.val() ) || ! validate.isString( email.val() ) ) {
                    email.addClass( 'input-field-invalid' );
                    email.parent().append( '<span class="validation-message text-warning"><?php _e( 'Email field cannot be blank.', 'garminbygis' ) ?></span>' );
                    email.focus();

                    if ( isValid ) email.focus();

                    isValid = false;
                }

                if ( validate.isEmpty( birthdate.val() ) || ! validate.isString( birthdate.val() ) ) {
                    birthdate.addClass( 'input-field-invalid' );
                    birthdate.parent().append( '<span class="validation-message text-warning"><?php _e( 'Birth date field cannot be blank.', 'garminbygis' ) ?></span>' );

                    if ( isValid ) birthdate.focus();

                    isValid = false;
                }

                return isValid;
            });
        });
    </script>
    <?php
} // end func
 
 
/**
 * This is to save user input into database
 * hook: woocommerce_save_account_details
 */
function my_woocommerce_save_account_details( $user_id ) {
    update_user_meta( $user_id, 'birthday', htmlentities( $_POST['birthday'] ) );
    update_user_meta( $user_id, 'gender', htmlentities( $_POST['gender'] ) );
    update_user_meta( $user_id, 'billing_phone', htmlentities( $_POST['billing_phone'] ) );
} // end func

add_action( 'woocommerce_edit_account_form', 'my_woocommerce_edit_account_form' );
add_action( 'woocommerce_save_account_details', 'my_woocommerce_save_account_details' );
