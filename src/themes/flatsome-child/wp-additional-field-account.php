<?php

 function my_woocommerce_edit_account_form() {
 
         $user_id = get_current_user_id();
         $user = get_userdata( $user_id );
 
         if ( !$user )
                 return;
 
         $birthday = get_user_meta( $user_id, 'birthday', true );
         $gender = get_user_meta( $user_id, 'gender' , true );
         $phoneno = get_user_meta( $user_id, 'billing_phone', true);
 
 ?>
         <fieldset>
                 <legend><?php _e('Additional Information', 'woocommerce' ); ?></legend>
                 <?php wp_enqueue_script('jquery-ui-datepicker'); ?>
                 <p class="form-row form-row-thirds">
                         <label for="birthday"><?php _e('Birth date : ', 'woocommerce' ); ?><span class="required">*</span></label>
                         <input type="text" id="birth-date" name="birthday" value="<?php echo esc_attr( $birthday ); ?>" required/>
                         <span style="font-size: 12px;"><?php _e('(Birth date format: DD-MM-YYYY. eg: 01/12/1990)', 'woocommerce' ); ?></span>
                 </p>
                 <p class="form-row form-row-thirds">
                         <label for="gender"><?php _e('Gender : ', 'woocommerce' ); ?></label>
                         <select name="gender" class="input-text" value="<?php echo esc_attr( $gender ); ?>">
                         <option value="male" <?php if ( $gender == 'male' ) echo 'selected="selected"'; ?>><?php _e('male','woocommerce') ?></option>
                         <option value="female" <?php if ( $gender == 'female' ) echo 'selected="selected"'; ?>><?php _e('female','woocommerce') ?></option>
                         </select>
                         <span style="font-size: 12px;"><?php _e('(Gender format: male/female)', 'woocommerce' ); ?></span>
                 </p>
                 <p class="form-row form-row-thirds">
                         <label for="phoneno"><?php _e('Phone no : ', 'woocommerce' ); ?></label>
                         <input type="text" id="ssn" name="billing_phone" value="<?php echo esc_attr( $phoneno ); ?>"/>
                         <span style="font-size: 12px;"><?php _e('(Phone no. format: 088-664-4321)', 'woocommerce' ); ?></span>
                 </p>
         </fieldset>

        <script type="text/javascript">
                jQuery(document).ready(function($) {

                <?php if(get_locale() == 'th') : ?>

                $.datepicker.regional['th'] = {
                        closeText: "ปิด",
                        prevText: "&#xAB;&#xA0;ย้อน",
                        nextText: "ถัดไป&#xA0;&#xBB;",
                        currentText: "วันนี้",
                        monthNames: [ "มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน",
                        "กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม" ],
                        monthNamesShort: [ "ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.",
                        "ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค." ],
                        dayNames: [ "อาทิตย์","จันทร์","อังคาร","พุธ","พฤหัสบดี","ศุกร์","เสาร์" ],
                        dayNamesShort: [ "อา.","จ.","อ.","พ.","พฤ.","ศ.","ส." ],
                        dayNamesMin: [ "อา.","จ.","อ.","พ.","พฤ.","ศ.","ส." ],
                        weekHeader: "Wk",
                        dateFormat: "dd/mm/yy",
                        firstDay: 0,
                        isRTL: false,
                        showMonthAfterYear: false,
                        yearSuffix: "" };
                $.datepicker.setDefaults($.datepicker.regional['th']);

                <?php endif; ?>

                $('#birth-date').datepicker({
                        yearRange: "-100:+0",
                        changeMonth: true, 
                        changeYear: true,
                        dateFormat : 'dd/mm/yy',
                        maxDate: new Date
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

                update_user_meta( $user_id, 'birthday', htmlentities( $_POST[ 'birthday' ] ) );
                update_user_meta( $user_id, 'gender', htmlentities( $_POST[ 'gender' ] ) );
                update_user_meta( $user_id, 'billing_phone', htmlentities( $_POST[ 'billing_phone' ] ) );

 } // end func

 add_action( 'woocommerce_edit_account_form', 'my_woocommerce_edit_account_form' );
 add_action( 'woocommerce_save_account_details', 'my_woocommerce_save_account_details' );

 
 