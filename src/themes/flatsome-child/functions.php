<?php
// Add custom Theme Functions here

//Verify Email
//require_once('wp-verify-email.php');

/**
 * To display additional field at My Account page 
 * Once member login: edit account
 */
require_once('wp-additional-field-account.php');

//password confirmation
require_once('wp-confirm-password.php');

//change layout near sort order
require_once('wp-change-layout-sort-order.php');

//custom product data fields
require_once('wp-custom-product-data-fields.php');

//custom product variation field
require_once('wp-custom-product-variation-fields.php');

//phone format field
require_once('wp-phone-format-field.php');

//Add Nostra Shortcode
require_once('wp-nostra-pin-short-code.php');

//Additional information catalog page
require_once('wp-addtional-info-catalog-page.php');

//Add checkbox require tax field
require_once('wp-checkbox-field-checkout.php');

//shop dealer
require_once('wp-shortcode-dealers.php');

//shop dealerTH
require_once('wp-shortcode-dealersTH.php');

//shop retailer
require_once('wp-shortcode-retailers.php');

//shop retailerTH
require_once('wp-shortcode-retailersTH.php');

//shop dealerMB
require_once('wp-shortcode-dealersMB.php');

//shop dealerTH-MB
require_once('wp-shortcode-dealersTH-MB.php');

//shop retailerMB
require_once('wp-shortcode-retailersMB.php');

//shop retailerTH-MB
require_once('wp-shortcode-retailersTH-MB.php');

//product register
require_once('wp-shortcode-product-register.php');

//map register
require_once('wp-shortcode-map-register.php');


// Hooks near the bottom of profile page (if current user) 
add_action('show_user_profile', 'custom_user_profile_fields');

// Hooks near the bottom of the profile page (if not current user) 
add_action('edit_user_profile', 'custom_user_profile_fields');

// @param WP_User $user
function custom_user_profile_fields( $user ) {
    $gender = get_the_author_meta( 'gender', $user->ID );
    $birthday = get_the_author_meta('birthday', $user->ID);
?>
    <table class="form-table">
        <tr>
            <th>
                <label for="gender"><?php _e( 'Gerder' ); ?></label>
            </th>
            <td>
                <select name="gender" class="input-text" value="<?php echo esc_attr( $gender ); ?>">
                    <option value="male" <?php if ( $gender == 'male' ) echo 'selected="selected"'; ?>>male</option>
                    <option value="female" <?php if ( $gender == 'female' ) echo 'selected="selected"'; ?>>female</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>
                <label for="birthday"><?php _e( 'Birthday' ); ?></label>
            </th>
            <td>
                <?php wp_enqueue_script('jquery-ui-datepicker'); ?>
                <input type="text" id="birth-date" name="birthday" value="<?php echo esc_attr( $birthday ); ?>"/>
            </td>
        </tr>
    </table>
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
}


// Hook is used to save custom fields that have been added to the WordPress profile page (if current user) 
add_action( 'personal_options_update', 'update_extra_profile_fields' );

// Hook is used to save custom fields that have been added to the WordPress profile page (if not current user) 
add_action( 'edit_user_profile_update', 'update_extra_profile_fields' );

function update_extra_profile_fields( $user_id ) {
    if ( current_user_can( 'edit_user', $user_id ) )
        update_user_meta( $user_id, 'gender', $_POST['gender'] );
        update_user_meta( $user_id, 'birthday', $_POST['birthday'] );
}

add_filter('template_include', 'restict_by_category');

function check_user() {
  $user = wp_get_current_user();
  if ( ! $user->ID || in_array('subscriber', $user->roles) ) {
    // user is not logged or is a subscriber
    return false;
  }
  return true;
}

function restict_by_category( $template ) {
  if ( ! is_main_query() ) return $template; // only affect main query.
  $allow = true;
  $private_categories = array('special-deal-aia'); // categories subscribers cannot see
  if ( is_single() ) {
    $cats = wp_get_object_terms( get_queried_object()->ID, 'category', array('fields' => 'slugs') ); // get the categories associated to the required post
    if ( array_intersect( $private_categories, $cats ) ) {
      // post has a reserved category, let's check user
      $allow = check_user();
    }
  } elseif ( is_tax('category', $private_categories) ) {
    // the archive for one of private categories is required, let's check user
    $allow = check_user();
  }
  // if allowed include the required template, otherwise include the 'not-allowed' one
  return $allow ? $template : get_template_directory() . '/not-allowed.php';
}


// dont update user meta
add_filter('woocommerce_checkout_update_customer_data', '__return_false' );




function sv_remove_product_page_skus( $enabled ) {
    if ( ! is_admin() && is_product() ) {
        return false;
    }

    return $enabled;
}
add_filter( 'wc_product_sku_enabled', 'sv_remove_product_page_skus' );


// Add Part number variation product
add_filter( 'woocommerce_available_variation', 'load_variation_settings_fields' );
/**
 * Add custom fields for variations
 *
*/
function load_variation_settings_fields( $variations ) {
	
	// duplicate the line for each field
	$variations['_partnumber_text_field_variation'] = get_post_meta( $variations[ 'variation_id' ], '_partnumber_text_field_variation', true );
	
	return $variations;
}


//Add Part number simple product
add_action( 'woocommerce_single_product_summary', 'add_custom_field', 5 );

function add_custom_field() {

    global $product; 
    
        $product_id = $product->get_id(); // The product ID
    
        // Your custom field "Book author"
        $_partnumber_product_field_simple = get_post_meta($product_id, "_partnumber_product_field_simple", true);
    
        // Displaying your custom field under the title
        _e('Part Number : ','woocommerce');  
        echo $_partnumber_product_field_simple;
}

//Custom JS


// //custom endpoint
// function wk_custom_endpoint() {
//     add_rewrite_endpoint( 'custom', EP_ROOT | EP_PAGES );
//   }
// add_action( 'init', 'wk_custom_endpoint' );

// add_filter( 'woocommerce_account_menu_items', 'wk_new_menu_items' );
//  /**
//  * Insert the new endpoint into the My Account menu.
//  *
//  * @param array $items
//  * @return array
//  */
//  function wk_new_menu_items( $items ) {
//      $items[ 'custom1' ] = __( 'Custom1', 'webkul' );
//      $items[ 'custom2' ] = __( 'Custom2', 'webkul' );
//      return $items;
//  }

//  $endpoint = 'custom';
 
// add_action( 'woocommerce_account_' . $endpoint .  '_endpoint', 'wk_endpoint_content' );
// add_action( 'woocommerce_account_' . $endpoint .  '_endpoint', 'wk_endpoint_content' );
 
// function wk_endpoint_content() {
//     //content goes here
//     echo '//content goes here';    
// }

// Note: this is simple PHP that can be placed in your functions.php file
// Note: substr may give you problems, please check Option 3
add_filter( 'the_title', 'shorten_woo_product_title', 10, 2 );
function shorten_woo_product_title( $title, $id ) {
if ( is_shop() && get_post_type( $id ) === 'product' ) {
return substr( $title, 0, 25 ); // change last number to the number of characters you want
} else {
return $title;
}
}

add_action( 'wp_footer', 'cart_update_qty_script' );
function cart_update_qty_script() {
    if (is_cart()) :
        ?>
        <script type="text/javascript">
            (function($){
                $(function(){
                    $('div.woocommerce').on( 'change', '.qty', function(){
                        $("[name='update_cart']").removeProp("disabled");
                        $("[name='update_cart']").trigger('click');
                    });
                });
            })(jQuery);
        </script>
        <?php
    endif;
}
