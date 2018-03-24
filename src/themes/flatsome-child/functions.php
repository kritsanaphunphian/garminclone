<?php
// Add custom Theme Functions here

/**
 * Register strings to Polylang so we can translate it
 * at the Polylang 'Strings translations' page.
 */
pll_register_string( 'login-form-login-with-fb-button', 'Login with <strong>Facebook</strong>', 'garminbygis' );
pll_register_string( 'login-form-login-with-gplus-button', 'Login with <strong>Google +</strong>', 'garminbygis' );
pll_register_string( 'register-form-input-confirm-password-field', 'Confirm Password', 'garminbygis' );
pll_register_string( 'register-form-validation-confirm-password-not-match', 'Passwords do not match.', 'garminbygis' );

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

/**
 * To add 'login by Google' and 'login by Facebook' to the WooCommerce's form-login page.
 *
 * @see wp-content/themes/flatsome/woocommerce/myaccount/form-login.php
 */
function hook_flatsome_woocommerce_login_form_end() {
    echo '
    <a  href="' . wp_login_url() . '?loginFacebook=1&redirect=' . get_permalink() . '"
        class="button social-button large facebook circle"
        onclick="window.location=\'' . wp_login_url() . '?loginFacebook=1&redirect=\'+window.location.href; return false;">
        <i class="icon-facebook"></i>
        <span>' . pll__( 'Login with <strong>Facebook</strong>' ) . '</span>
    </a>

    <a  href="' . wp_login_url() . '?loginGoogle=1&redirect=' . get_permalink() . '"
        class="button social-button large google-plus circle"
        onclick="window.location = \'' . wp_login_url() . '?loginGoogle=1&redirect=\'+window.location.href; return false;">
        <i class="icon-google-plus"></i>
        <span>' . pll__( 'Login with <strong>Google +</strong>' ) . '</span></a>
    ';

    if ( isset( $login_text ) && $login_text ) {
        echo '<p>' . do_shortcode( $login_text ) . '</p>';
    }
}
add_action( 'woocommerce_login_form_end', 'hook_flatsome_woocommerce_login_form_end' );

/**
 * To remove filters from the Flatsome theme
 * that we can do override at the child-theme by ourselves.
 */
function hook_remove_faltsome_parent_filters() {
    /**
     * @see wp-content/themes/flatsome/woocommerce/myaccount/form-login.php
     */
    remove_filter( 'flatsome_footer','flatsome_page_footer', 10 );

    /**
     * @see flatsome/inc/integrations/wc-yith-wishlist/yith-wishlist.php
     * @see flatsome_wishlist_account_item()
     */
    remove_filter( 'flatsome_account_links', 'flatsome_wishlist_account_item' );
}
add_action( 'after_setup_theme', 'hook_remove_faltsome_parent_filters' );

/**
 * Override 'flatsome_page_footer' function
 * so we can dynamically handle the translated-block from Polylang.
 *
 * @see wp-content/themes/flatsome/inc/structure/structure-footer.php : flatsome_page_footer();
 */
function flatsomechild_page_footer() {
    global $page;
    $block = get_theme_mod( 'footer_block' );

    if ( is_page() && ! $block ) {
        // Custom Page footers
        $page_footer = get_post_meta( get_the_ID(), '_footer', true );

        if ( empty( $page_footer ) || 'normal' == $pagege_footer ) {
            echo get_template_part( 'template-parts/footer/footer' );
        } elseif ( ! empty( $page_footer ) && 'disabled' !== $page_footer ) {
            echo get_template_part( 'template-parts/footer/footer', $page_footer );
        }
    } else {
        // Global footer
        if ( $block ) {
            if ( '-lang-' . pll_default_language() === substr( $block, -8 ) ) {
                $block = substr_replace($block, '-lang-' . pll_current_language(), -8);
            }

            echo do_shortcode( sprintf( '[block id="%s"]', $block ) );
            echo get_template_part( 'template-parts/footer/footer-absolute' );
        } else {
            echo get_template_part( 'template-parts/footer/footer' );
        }
    }
}
add_filter( 'flatsome_footer','flatsomechild_page_footer', 10 );

/**
 * To add a custom element to YITH WooCommerce Wishlist plugin's 'added_to_cart_message'.
 *
 * @return string
 *
 * @see    wp-content/plugins/yith-woocommerce-wishlist/includes/class.yith-wcwl-init.php
 */
function hook_yith_wcwl_added_to_cart_message() {
    return '
    <div style="padding-bottom: 0.5em;" class="message-container container success-color medium-text-center">'
        . __( 'Product correctly added to cart', 'yith-woocommerce-wishlist' ) .
    '</div>';
}
add_filter( 'yith_wcwl_added_to_cart_message','hook_yith_wcwl_added_to_cart_message', 10 );

/**
 * Note, that because YITH WooCommerce Compare Premium plugin doesn't provide
 * a way to 'add' a comapre link manually, so it is always be hooked under
 * 'woocommerce_after_shop_loop_item' action.
 *
 * In order to custom it as designed, we are going to play some trick here.
 */
function hook_reposition_compare_button_if_switch_grid_list() {
    if ( 'yes' == get_option( 'yith_woocompare_compare_button_in_products_list' ) && ( is_shop() || is_product_category() ) ) :
    ?>
        <script>
            jQuery( document ).ready(function() {
                var br_lgv_stat_cookie = set_get_lgv_cookie( 0 );

                if ( br_lgv_stat_cookie && 'list' == br_lgv_stat_cookie ) {
                    jQuery( '.box-text-products' ).addClass( 'hide-from-screen' );

                    var compare_buttons = jQuery( '.shop-container' ).find( 'a.compare' );

                    jQuery.each( compare_buttons, function( key, button ) {
                        var button         = jQuery( button ),
                            parent_element = button.parents( '.product-small.berocket_lgv_list_grid' );

                        button.addClass( 'button' )
                              .detach()
                              .appendTo( parent_element.find( '.garminbygis-additional-data-compare-button-wrapper' ) );
                    });
                }

                jQuery( document ).on( 'click', '.berocket_lgv_widget a.berocket_lgv_set', function( event ) {
                    event.preventDefault();

                    var compare_buttons         = jQuery( '.shop-container' ).find( 'a.compare' ),
                        berocket_list_grid_type = jQuery( this ).data( 'type' );

                    if ( 0 >= compare_buttons.length ) {
                        return;
                    }

                    if ( br_lgv_stat_cookie && berocket_list_grid_type === br_lgv_stat_cookie ) {
                        return;
                    }

                    if ( 'list' === berocket_list_grid_type ) {
                        jQuery.each( compare_buttons, function( key, button ) {
                            var button         = jQuery( button ),
                                parent_element = button.parents( '.product-small.berocket_lgv_list_grid' );

                            button.addClass( 'button' )
                                  .detach()
                                  .appendTo( parent_element.next().find( '.garminbygis-additional-data-compare-button-wrapper' ) );
                        });

                        jQuery( '.box-text-products' ).addClass( 'hide-from-screen' );
                    } else {
                        jQuery.each( compare_buttons, function( key, button ) {
                            var button         = jQuery( button ),
                                parent_element = button.parents( '.berocket_lgv_additional_data' );

                            button.removeClass( 'button' )
                                  .detach()
                                  .appendTo( parent_element.prev() );
                        });

                        jQuery( '.box-text-products' ).removeClass( 'hide-from-screen' );
                    }

                    br_lgv_stat_cookie = berocket_list_grid_type;
                });
            });
        </script>
    <?php
    endif;
}
add_action( 'flatsome_before_header', 'hook_reposition_compare_button_if_switch_grid_list' );

/**
 * Originally, Flatsome always set 'orderby' parameter to 'title', causing that
 * a search result, kinda irrelevant with the keyword that user searches with,
 * and there is no way we can alter that parameter.
 *
 * Fortunately, we can still hook our function at the very last step of its function.
 * So here we hooked "flatsome_ajax_search_function" so we can alter the 'orderby' parameter
 * to 'relevance' (see garminbygis_search_products() function below).
 *
 * @hook   flatsome_ajax_search_function
 *
 * @param  string $search_query
 * @param  array  $args
 * @param  array  $defaults
 *
 * @see    wp-content/themes/flatsome/inc/extensions/flatsome-live-search/flatsome-live-search.php
 * @see    flatsome_ajax_search_products()
 *
 * @return string
 */
function hook_flatsome_ajax_search_function( $type, $search_query, $args, $defaults ) {
    return 'garminbygis_search_products';
}
add_filter( 'flatsome_ajax_search_function', 'hook_flatsome_ajax_search_function', 10, 4 );

/**
 * This function will be used only with 'hook_flatsome_ajax_search_function()' function.
 * To alter the original 'orderby' parameter.
 *
 * @param  string $search_query
 * @param  array  $args
 * @param  array  $defaults
 *
 * @see    wp-content/themes/flatsome/inc/extensions/flatsome-live-search/flatsome-live-search.php
 * @see    flatsome_ajax_search_products()
 *
 * @return array
 */
function garminbygis_search_products( $search_query, $args, $defaults ) {
    $args['orderby'] = 'relevance';
    return get_posts( $args );
}

/**
 * Note that the plugin "WooCommerce Multiple Customer Addresses" also hooks this action
 * and replace the string by a custom title from its plugin.
 *
 * So, that is why we have to set the priority to 12 instead of 10 (default).
 *
 * @hook   woocommerce_my_account_my_address_description -- 12
 *
 * @see    wp-content/plugins/woocommerce-multiple-customer-addresses/classes/frontend/WCMCA_MyAccountPage.php
 * @see    wp-content/plugins/woocommerce/templates/myaccount/my-address.php
 *
 * @return string
 */
function hook_woocommerce_my_account_my_address_description() {
    return __( 'My Address', 'garminbygis' );
}
add_action( 'woocommerce_my_account_my_address_description', 'hook_woocommerce_my_account_my_address_description', 12);

/**
 * Note that the reason we need to hook it because we cannot translate the original string.
 * The original string is a dynamic-variable which is not support for WordPress translation system.
 *
 * @see flatsome/inc/integrations/wc-yith-wishlist/yith-wishlist.php
 * @see flatsome_wishlist_account_item()
 */
function hook_flatsome_wishlist_account_item() {
    $wishlist_page = yith_wcwl_object_id( get_option( 'yith_wcwl_wishlist_page_id' ) );

    echo '  <li class="wishlist-account-element' . ( is_page( $wishlist_page ) ? ' active' : '' ) . '">';
    echo '      <a href="' . YITH_WCWL()->get_wishlist_url() . '">' . __( 'Wishlist', 'garminbygis' ) . '</a>';
    echo '  </li>';
}
add_filter( 'flatsome_account_links', 'hook_flatsome_wishlist_account_item' );

/**
 * @see woocommerce/includes/class-wc-countries.php
 * @see WC_Countries::get_default_address_fields()
 */
function hook_woocommerce_default_address_fields( $fields ) {
    $fields['postcode']['maxlength']         = 5;
    $fields['postcode']['custom_attributes'] = array(
        'onkeyup' => 'this.value=this.value.replace( /[^\d]/, "" )'
    );

    return $fields;
}
add_filter( 'woocommerce_default_address_fields', 'hook_woocommerce_default_address_fields' );

/**
 * Thailand postcode has to be 'all-number' only.
 *
 * @see woocommerce/includes/class-wc-validation.php
 * @see WC_Validation::is_postcode()
 */
function hook_woocommerce_validate_th_postcode( $valid, $postcode, $country ) {
    if ( $country !== 'TH' ) {
        return $valid;
    }

    if ( preg_match("/^[0-9]+$/", $postcode ) ) {
        return true;
    }

    return false;
}
add_filter( 'woocommerce_validate_postcode', 'hook_woocommerce_validate_th_postcode', 10, 3 );
