<?php
/*
Plugin Name: Seed Confirm Pro
Plugin URI: https://www.seedthemes.com/plugin/seed-confirm-pro
Description: Creates confirmation form for bank transfer payment. If using with WooCommerce, this plugin will get bank information from WooCommerce.
Version: 1.4.2
Author: SeedThemes
Author URI: https://www.seedthemes.com
License: GPL2
Text Domain: seed-confirm
*/

/*
Copyright 2016-2017 SeedThemes  (email : info@seedthemes.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once( dirname( __FILE__ ) . '/seed-confirm-pro-functions.php' );
require_once( dirname( __FILE__ ) . '/seed-confirm-pro-pending-to-cancelled.php' );

/**
* Load text domain.
*/
load_plugin_textdomain('seed-confirm', false, basename( dirname( __FILE__ ) ) . '/languages' );

// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define( 'EDD_SEED_CONFIRM_STORE_URL', 'https://th.seedthemes.com' );

// the name of your product. This should match the download name in EDD exactly
define( 'EDD_SEED_CONFIRM_ITEM_NAME', 'Seed Confirm Pro: ปลั๊กอินแจ้งชำระเงิน' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
// load our custom updater
	include( dirname( __FILE__ ) . '/seed-confirm-pro-updater.php' );
}

/**
* Updater.
*/
add_action( 'admin_init', 'edd_sl_seed_confirm_plugin_updater', 0 );

function edd_sl_seed_confirm_plugin_updater() {
	$status  = get_option( 'seed_confirm_license_status' );

	if($status == 'valid'){
		/* retrieve our license key from the DB */
		$license_key = trim( get_option( 'seed_confirm_license_key' ) );
		$edd_updater = new EDD_SL_Plugin_Updater( EDD_SEED_CONFIRM_STORE_URL, __FILE__, array(
			'version'   => '1.4.2',
			'license'   => $license_key,
			'item_name' => EDD_SEED_CONFIRM_ITEM_NAME,
			'author'    => 'SeedThemes'
		));
	}
}

if(!class_exists('Seed_Confirm')) {
	class Seed_Confirm {
		/* Construct the plugin object */
		public function __construct() {
			/* register actions */
		}

		/* Activate the plugin */
		public static function activate() {
			/* Add Default payment-confirm page. */
			$page = get_page_by_path('confirm-payment');
			if (!is_object($page)) {
				global $user_ID;
				$page = array(
					'post_type'      => 'page',
					'post_name'      => 'confirm-payment',
					'post_parent'    => 0,
					'post_author'    => $user_ID,
					'post_status'    => 'publish',
					'post_title'     => __('Confirm Payment', 'seed-confirm'),
					'post_content'   => '[seed_confirm]',
					'ping_status'    => 'closed',
					'comment_status' => 'closed',
				);
				$page_id = wp_insert_post($page);
			} else {
				$page_id = $page->ID;
			}

			/* Add default plugin's settings. */
			add_option( 'seed_confirm_page', $page_id);
			add_option( 'seed_confirm_notification_text', __( 'Thank you for your payment. We will process your order shortly.', 'seed-confirm' ) );
			add_option( 'seed_confirm_notification_bg_color', '#57AD68' );
			add_option( 'seed_confirm_required', json_encode( array(
				'seed_confirm_name' => 'true',
				'seed_confirm_contact' => 'true',
				'seed_confirm_amount' => 'true',
			) ) );
			add_option( 'seed_confirm_optional', json_encode( array(
				'optional_address' => '',
				'optional_information' => '',
			) ) );

			/* Add default schedule time for cancel order. */
			update_option('seed_confirm_schedule_status', 'false');

			$default_time = 1140; /* 1 day */
			update_option('seed_confirm_time', $default_time);

			/* Add default email template. */
			update_option('seed_confirm_email_template', '');

		} /* END public static function activate */

		/* Deactivate the plugin */
		public static function deactivate()
		{
			/* Clear schedule time for cancel order. */
			delete_option('seed_confirm_time');
			wp_clear_scheduled_hook('seed_confirm_schedule_pending_to_cancelled_orders');

		} /* END public static function deactivate */
	} /* END class Seed_Confirm */
} /* END if(!class_exists('Seed_Confirm')) */

if(class_exists('Seed_Confirm')) {
	register_activation_hook(__FILE__, array('Seed_Confirm', 'activate'));
	register_deactivation_hook(__FILE__, array('Seed_Confirm', 'deactivate'));
	$Seed_Confirm = new Seed_Confirm();
}

/**
* Remove all woocommerce_thankyou_bacs hooks.
* Cause we don't want to display all bacs from woocommerce.
* Web show new one that is better.
*/
add_action( 'template_redirect', 'seed_confirm_remove_hook_thankyou_bacs' );

function seed_confirm_remove_hook_thankyou_bacs() {
	if(is_woo_activated()){
		$gateways = WC()->payment_gateways()->payment_gateways();
		remove_action( 'woocommerce_thankyou_bacs', array( $gateways[ 'bacs' ], 'thankyou_page' ) );
	}
}

/**
* Remove the original bank details
* @link http://www.vanbodevelops.com/tutorials/remove-bank-details-from-woocommerce-order-emails
*/
add_action('init', 'seed_confirm_remove_bank_details', 100);

function seed_confirm_remove_bank_details() {
	if (!is_woo_activated()) {
		return;
	}

	$available_gateways = WC()->payment_gateways()->payment_gateways();

	if (isset($available_gateways['bacs'])) {
		/* If the gateway is available, remove the action hook*/
		remove_action('woocommerce_email_before_order_table', array($available_gateways['bacs'], 'email_instructions'), 10, 3);
	}
}

/**
* Register new status for WooCommerce
* Tutorial: https://www.sellwithwp.com/woocommerce-custom-order-status-2/
*/
add_action( 'init', 'seed_confirm_register_checking_payment_order_status' );

function seed_confirm_register_checking_payment_order_status() {
	register_post_status( 'wc-checking-payment', array(
		'label'                     => _x( 'Checking Payment', 'WooCommerce Order status', 'seed-confirm' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Checking Payment <span class="count">(%s)</span>', 'Checking Payment <span class="count">(%s)</span>', 'seed-confirm')
	) );
}

/**
* Add Checking Payment to list of WC Order statuses
* Tutorial: https://www.sellwithwp.com/woocommerce-custom-order-status-2/
*/
add_filter( 'wc_order_statuses', 'seed_confirm_add_checking_payment_to_order_statuses' );

function seed_confirm_add_checking_payment_to_order_statuses( $order_statuses ) {
	$new_order_statuses = array();
	/* add checking-payment order status after complete */
	foreach ( $order_statuses as $key => $status ) {
		$new_order_statuses[ $key ] = $status;
		if ( 'wc-processing' === $key ) {
			$new_order_statuses['wc-checking-payment'] = __('Checking Payment', 'seed-confirm');
		}
	}
	return $new_order_statuses;
}

/**
* Add a custom email to the list of emails WooCommerce should load
* tutorial from: https://www.skyverge.com/blog/how-to-add-a-custom-woocommerce-email/
*/
add_filter( 'woocommerce_email_classes', 'seed_confirm_woocommerce_add_checking_payment_email' );

function seed_confirm_woocommerce_add_checking_payment_email( $email_classes ) {
	/* include our custom email class */
	require_once( dirname( __FILE__ ) . '/class-wc-email-customer-checking-payment.php' );
	/* add the email class to the list of email classes that WooCommerce loads */
	$email_classes['WC_Email_Customer_Checking_Payment'] = new WC_Email_Customer_Checking_Payment();
	return $email_classes;
}

/**
* Display notice for admin when plugin has activated
* and admin don't do BACS Settings
*/
add_action( 'admin_notices', 'seed_confirm_notice', 99 );

function seed_confirm_notice() {
	$account_details = get_option('woocommerce_bacs_accounts');
	if ( isset($account_details) && is_array($account_details) ) {
		return;
	}
	if (is_woo_activated()){
		$bacs_setting_uri = admin_url('admin.php?page=wc-settings&tab=checkout&section=bacs');
	} else {
		$bacs_setting_uri = admin_url('edit.php?post_type=seed_confirm_log&page=seed-confirm-log-settings&tab=bacs');
	}
	?>
	<div class="notice notice-warning">
		<p><?php _e( 'There is no BACS setting. Please check' , 'seed-confirm' );?> <a href="<?php echo $bacs_setting_uri;?>"><?php _e('Settings - BACS', 'seed-confirm')?></a> </p>
	</div>
	<?php
}

/**
* Add bank lists to these pages.
* Confirm page
* Thankyou page
* Thankyou email - only first email
*/
add_shortcode( 'seed_confirm_banks', 'seed_confirm_banks' );
add_action( 'woocommerce_thankyou_bacs', 'seed_confirm_banks', 10);

/**
* Add bank lists to email only customer's first email.
*/
add_action( 'woocommerce_email_before_order_table', 'seed_confirm_banks_email', 10, 2);

function seed_confirm_banks_email($order, $sent_to_admin) {
	if(!$sent_to_admin && $order->has_status( 'on-hold' )) {
		/* If user select payment method not bacs - Don't add bank list to email. */
		$order_id = $order->get_order_number();
		$payment_method = get_post_meta( $order_id, '_payment_method', true );
		if($payment_method != 'bacs') return ;
		seed_confirm_banks($order_id);
	}
}

function seed_confirm_banks( $orderid ) {
	$thai_accounts = array();
	$gateways = WC()->payment_gateways->get_available_payment_gateways();
	$bacs_settings = $gateways['bacs'];
	$thai_accounts = seed_confirm_get_banks($bacs_settings->account_details);
	do_action('seed_confirm_before_banks', $orderid);
	?>
	<div id="seed-confirm-banks" class="seed-confirm-banks">
		<p class="instructions"><?php echo $bacs_settings->instructions;?></p>
		<h2><?php esc_html_e( 'Our Bank Details', 'seed-confirm' ); ?></h2>
		<h3><?php esc_html_e( 'Account : GIS Co.,Ltd. (Current account)', 'seed-confirm' ); ?></h3>
			<table class="table table-responsive _heading" width="100%">
				<thead>
					<tr>
					<th class="seed-confirm-bank-logo">&nbsp;</th>
					<th class="seed-confirm-bank-name"><?php esc_html_e( 'Bank Name', 'seed-confirm' ); ?></th>

					<th class="seed-confirm-bank-account-number"><?php esc_html_e( 'Account Number', 'seed-confirm' ); ?></th>
					<th class="seed-confirm-bank-account-name"><?php esc_html_e( 'Branch', 'seed-confirm' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach( $thai_accounts as $_account ): ?>
						<tr>
						<td class="seed-confirm-bank-logo"><?php if($_account['logo']) { echo '<img src="'. $_account['logo'] . '" width="32" height="32" style="border-radius:5px">';} ?></td>
						<td class="seed-confirm-bank-name"><?php echo $_account['bank_name']; ?></td>

						<td class="seed-confirm-bank-account-number"><?php echo $_account['account_number'];?></td>
						<td class="seed-confirm-bank-account-name"><?php echo $_account['account_name'];?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

	</div>
	<?php
	do_action('seed_confirm_after_banks', $orderid);
}

/**
* Enqueue css and javascript for confirmation payment page.
* CSS for feel good.
* javascript for validate data.
*/
add_action( 'wp_enqueue_scripts', 'seed_confirm_scripts' );

function seed_confirm_scripts() {
	if(!is_admin()) {
		wp_enqueue_style( 'seed-confirm', plugin_dir_url( __FILE__ ) . 'seed-confirm-pro.css' , array() );
		wp_enqueue_script( 'seed-confirm', plugin_dir_url( __FILE__ ) . 'seed-confirm-pro.js' , array('jquery'), '2016-1', true );
		wp_enqueue_script( 'seed-confirm-form-validator', plugin_dir_url( __FILE__ ) . 'vendor/jquery.form-validator.min.js' , array('jquery'), '2016-1', true );
	}
}

/**
* Enqueue javascript for settings on admin page.
*/
add_action( 'admin_enqueue_scripts', 'seed_confirm_admin_scripts' );

function seed_confirm_admin_scripts() {
	if(is_admin()){
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_style( 'seed-confirm-admin', plugin_dir_url( __FILE__ ) . 'seed-confirm-pro-admin.css', array());
		wp_enqueue_script( 'seed-confirm', plugin_dir_url( __FILE__ ) . 'seed-confirm-pro-admin.js' , array('wp-color-picker','jquery-ui-sortable'));
	}
}

add_filter( 'woocommerce_bacs_accounts', 'seed_confirm_bacs', 10 );

function seed_confirm_bacs( $accounts ) {

	$thai_accounts = seed_confirm_get_banks($accounts);

	return $thai_accounts;
}


/**
* Register seed_confirm shortcode.
* This shortcode display form for  payment confirmation.
* [seed_confirm]
*/
add_shortcode( 'seed_confirm', 'seed_confirm_shortcode' );

function seed_confirm_shortcode( $atts ) {
	global $post;
	$seed_confirm_name = '';
	$seed_confirm_contact = '';
	$seed_confirm_order = '';
	$seed_confirm_account_number = '';
	$seed_confirm_amount = '';
	$seed_confirm_date = '';
	$seed_confirm_hour = '';
	$seed_confirm_minute = '';

	$current_user = wp_get_current_user();

	$user_id = $current_user->ID;

	$seed_confirm_name = get_user_meta( $user_id, 'billing_first_name', true ) . ' ' . get_user_meta( $user_id, 'billing_last_name', true );
	$seed_confirm_contact = get_user_meta( $user_id, 'billing_phone', true );

	$seed_confirm_date = current_time('d-m-Y');
	$seed_confirm_hour = current_time('H');
	$seed_confirm_minute = current_time('i');

	ob_start();
	?>
	<?php if( !empty($_SESSION['resp_message']) ): ?>
		<div class="seed-confirm-message" style="background-color: <?php echo get_option( 'seed_confirm_notification_bg_color' ); ?>">
			<?php
			echo $_SESSION['resp_message'];
			unset($_SESSION['resp_message']);
			?>
		</div>
	<?php endif; ?>

	<?php if( !empty($_SESSION['resp_message_error']) ): ?>
		<div class="seed-confirm-message error">
			<?php
			echo $_SESSION['resp_message_error'];
			unset($_SESSION['resp_message_error']);
			?>
		</div>
	<?php endif; ?>

	<form method="post" id="seed-confirm-form" class="woocommerce seed-confirm-form _heading" enctype="multipart/form-data">
		<?php wp_nonce_field( 'seed-confirm-form-'.$post->ID ) ?>
		<?php
		$seed_confirm_required = json_decode( get_option( 'seed_confirm_required' ), true );
		$seed_confirm_optional = json_decode( get_option( 'seed_confirm_optional' ), true );
		$required_field_message = __( 'This is a required field.', 'seed-confirm' );

		do_action('seed_confirm_after_form_open');
		?>
		<div class="sc-row">
			<div class="sc-col">
				<label for="seed-confirm-name"><?php esc_html_e( 'Name', 'seed-confirm' ); ?></label>
				<input class="input-text form-control" type="text" id="seed-confirm-name" name="seed-confirm-name" value="<?php echo esc_html( $seed_confirm_name ); ?>" <?php echo isset($seed_confirm_required['seed_confirm_name']) ? 'data-validation="required" data-validation-error-msg-required="'.$required_field_message.'"' : ''; ?> />
			</div>
			<div class="sc-col">
				<label for="seed-confirm-contact"><?php esc_html_e( 'Contact', 'seed-confirm' ); ?></label>
				<input class="input-text form-control" type="text" id="seed-confirm-contact" name="seed-confirm-contact" value="<?php echo esc_html( $seed_confirm_contact ); ?>" <?php echo isset($seed_confirm_required['seed_confirm_contact']) ? 'data-validation="required" data-validation-error-msg-required="'.$required_field_message.'" data-validation-error-msg-required="'.$required_field_message.'"' : ''; ?> />
			</div>
		</div>
		<?php
		if(isset($seed_confirm_optional['optional_address']) && $seed_confirm_optional['optional_address'] == 'true') {
			?>
			<div class="seed-confirm-optional-address">
				<label><?php esc_html_e( 'Address', 'seed-confirm' ); ?></label>
				<textarea rows="7" class="input-text form-control" id="seed-confirm-optional-address" name="seed-confirm-optional-address" <?php echo isset($seed_confirm_required['seed-confirm-optional-address']) ? 'data-validation="required" data-validation-error-msg-required="'.$required_field_message.'"' : ''; ?>></textarea>
			</div>
			<?php
		}
		?>
		<div class="sc-row">
			<div class="sc-col">
				<label for="seed-confirm-order"><?php esc_html_e( 'Order', 'seed-confirm' ); ?></label>
				<?php
				$customer_orders = array();
				if( $user_id !== 0 && is_woo_activated()) {
					$customer_orders = get_posts( array(
						'numberposts' => -1,
						'meta_query' => array(
					        	array(
					            'key'     => '_customer_user',
					            'value'   => $user_id,
					        	),
					        	array(
					            'key'     => '_payment_method',
					            'value'   => 'bacs',
					        	),
					    ),
						'fields'      => 'ids', 	/* Grab order ids only. */
						'post_type'   => wc_get_order_types(),
						'post_status' => array( 'wc-on-hold', 'wc-processing', 'wc-checking-payment' ),
					));
				}
				if( !empty($customer_orders) ) {
					?>
					<select id="seed-confirm-order" name="seed-confirm-order" class="form-control" <?php echo isset($seed_confirm_required['seed_confirm_order']) ? 'data-validation="required" data-validation-error-msg-required="'.$required_field_message.'"' : ''; ?>>
						<?php
						foreach( $customer_orders as $order_id ):
							$order = new WC_Order( $order_id );
							?>
							<option value="<?php echo $order_id ?>"<?php if($seed_confirm_order == $order_id): ?> selected="selected"<?php endif ?>>
								<?php
								$seed_confirm_log_count = get_posts( array(
									'numberposts' => -1,
									'meta_key'    => 'seed-confirm-order',
									'meta_value'  => $order_id,
									'post_type'   => 'seed_confirm_log',
									'post_status' => array( 'publish' ),
								));
								if( count($seed_confirm_log_count) > 0 ) {esc_html_e( '[Paid] ', 'seed-confirm' ); };
								echo __('No. ', 'seed-confirm') . $order_id .__(' - Amount: ', 'seed-confirm') . $order->get_total() . ' '. get_woocommerce_currency_symbol();
								?>
							</option>
							<?php
						endforeach;
						?>
					</select>
					<?php
				} else {
					?>
					<input type="text" class="form-control" id="seed-confirm-order" name="seed-confirm-order" value="<?php echo esc_html( $seed_confirm_order ); ?>" <?php echo isset($seed_confirm_required['seed_confirm_order']) ? 'data-validation="required" data-validation-error-msg-required="'.$required_field_message.'"' : ''; ?> />
					<?php
				} ?>
			</div>
			<div class="sc-col">
				<label for="seed-confirm-amount"><?php esc_html_e( 'Amount', 'seed-confirm' ); ?></label>
				<input type="text" class="form-control" name="seed-confirm-amount" id="seed-confirm-amount" value="<?php echo esc_html( $seed_confirm_amount ); ?>" <?php echo isset($seed_confirm_required['seed_confirm_amount']) ? 'data-validation="required" data-validation-error-msg-required="'.$required_field_message.'"' : ''; ?> />
			</div>
		</div>
		<?php
		$account_details = get_option('woocommerce_bacs_accounts', true);
		if( !is_null( $account_details ) ) {
			$thai_accounts = seed_confirm_get_banks($account_details);
		}
		?>
		<div class="seed-confirm-bank-info bank-error-dialog">
			<label><?php esc_html_e( 'Bank Account', 'seed-confirm' ); ?></label>
			<?php if( count( $thai_accounts ) > 0 ): ?>
				<?php foreach( $thai_accounts as $_account ): ?>
					<div class="form-check">
						<label class="form-check-label">
							<span class="seed-confirm-check-wrap -logo">
								<input class="form-check-input" type="radio" id="bank-<?php echo $_account['account_number']; ?>" name="seed-confirm-account-number" value='<?php echo $_account['bank_name']; ?>,<?php echo $_account['account_number']; ?>' <?php if( $seed_confirm_account_number == $_account['bank_name'].','.$_account['account_number']): ?> selected="selected"<?php endif; ?> <?php echo isset($seed_confirm_required['seed_confirm_account_number']) ? 'data-validation="required" data-validation-error-msg-required="'.$required_field_message.'"' : ''; ?>  data-validation-error-msg-container=".bank-error-dialog">
								<span class="seed-confirm-bank-info-logo"><?php if($_account['logo']) { echo '<img src="'. $_account['logo'] . '" width="32" height="32">';} ?></span>
							</span>
							<span class="seed-confirm-check-wrap -detail">
								<span class="seed-confirm-bank-info-bank"><?php echo $_account['bank_name']; ?> <?php if($_account['sort_code']) { echo '<span>'. __('Branch: ', 'seed-confirm') . $_account['sort_code'] . '</span>';} ?></span>
								<span class="seed-confirm-bank-info-account-number"><?php echo $_account['account_number']; ?></span>
								<span class="seed-confirm-bank-info-account-name"><?php echo $_account['account_name']; ?></span>
							</span>
						</label>
					</div>
				<?php endforeach; ?>
				<div class="bank-error-dialog"></div>
			<?php else: ?>
				<tr>
					<td colspan="5"><?php _e('There is no BACS setting. Please contact administrator.', 'seed-confirm'); ?></td>
				</tr>
			<?php endif; ?>
		</div>
		<?php wp_enqueue_script('jquery-ui-datepicker'); ?>
		<div class="sc-row">
			<div class="sc-col seed-confirm-date">
				<label for="seed-confirm-date"><?php esc_html_e( 'Transfer Date', 'seed-confirm' ); ?></label>
				<input type="text" id="seed-confirm-date" name="seed-confirm-date" class="input-text form-control" value="<?php echo $seed_confirm_date ?>" <?php echo isset($seed_confirm_required['seed_confirm_date']) ? 'data-validation="required" data-validation-error-msg-required="'.$required_field_message.'"' : ''; ?> />
			</div>
			<div class="sc-col seed-confirm-time">
				<label><?php esc_html_e( 'Time', 'seed-confirm' ); ?></label>
				<div class="form-inline">

					<select name="seed-confirm-hour" id="seed-confirm-hour" class="form-control">
						<?php for ($i=0; $i <= 24; $i++) { $pad_couter = sprintf("%02d",$i); ?>
						<option value="<?php echo $pad_couter ?>"<?php selected( $seed_confirm_hour, $pad_couter ); ?>><?php echo $pad_couter ?></option>
						<?php } ?>
					</select>

					<select name="seed-confirm-minute" id="seed-confirm-minute" class="form-control">
						<?php for ($i=0; $i <= 60; $i++) { $pad_couter = sprintf("%02d",$i); ?>
						<option value="<?php echo $pad_couter ?>"<?php selected( $seed_confirm_minute, $pad_couter ); ?>><?php echo $pad_couter ?></option>
						<?php } ?>
					</select>

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
								dateFormat: "dd-mm-yy",
								firstDay: 0,
								isRTL: false,
								showMonthAfterYear: false,
								yearSuffix: ""
							};
							$.datepicker.setDefaults($.datepicker.regional['th']);
							<?php endif; ?>

							$('#seed-confirm-date').datepicker({
								dateFormat : 'dd-mm-yy',
								maxDate: new Date
							});
						});
					</script>
				</div>
			</div>
		</div>
		<div class="seed-confirm-slip">
			<label><?php esc_html_e( 'Payment Slip', 'seed-confirm' ); ?></label>
			<input type="file" id="seed-confirm-slip" name="seed-confirm-slip" class="form-control" accept=".png,.jpg,.gif,.pdf, image/png,image/vnd.sealedmedia.softseal-jpg,image/vnd.sealedmedia.softseal-gif,application/vnd.sealedmedia.softseal-pdf" <?php echo isset($seed_confirm_required['seed_confirm_slip']) ? 'data-validation="required" data-validation-error-msg-required="'.$required_field_message.'"' : ''; ?> />
		</div>
		<?php
		if(isset($seed_confirm_optional['optional_information']) && $seed_confirm_optional['optional_information'] == 'true'){
			?>
			<div class="seed-confirm-optional-information">
				<label><?php esc_html_e( 'Remark', 'seed-confirm' ); ?></label>
				<textarea rows="7" class="form-control" id="seed-confirm-optional-information" name="seed-confirm-optional-information"></textarea>
			</div>
			<?php
		}
		?>
		<?php do_action('google_invre_render_widget_action'); ?>
		<input type="hidden" name="postid" value="<?php echo $post->ID ?>" />
		<input <?php if(count($thai_accounts) <= 0){ ?> title="<?php _e('There is no BACS setting. Please contact administrator.', 'seed-confirm');?>" disabled="disabled" <?php } ?> id="seed-confirm-btn-submit" type="button" class="button alt btn btn-primary" value="<?php esc_html_e( 'Submit Payment Detail', 'seed-confirm' ); ?>" />
		<?php do_action('seed_confirm_before_form_close');?>
	</form>

	<?php
	return ob_get_clean();
}




add_action("woocommerce_order_status_changed", "seed_confirm_processing");

function seed_confirm_processing( $order_id , $checkout = null, $seed_confirm_change_status_to = null) {
	global $woocommerce;

	$order = new WC_Order( $order_id );

	$status = $order->status;

	if (!empty($seed_confirm_change_status_to)) {
		$status = $seed_confirm_change_status_to;
	}

	switch( $status ) {
		case 'checking-payment':
		/* Send email */
		WC()->mailer()->emails['WC_Email_Customer_Checking_Payment']->trigger($order_id);
		break;

		case 'processing':
		/* Send email */
		WC()->mailer()->emails['WC_Email_Customer_Processing_Order']->trigger($order_id);
		break;
	}
}

/**
* Grab POST from confirmation payment form and keep it in database.
*/
add_action( 'init', 'seed_confirm_init' , 11 );

function seed_confirm_init() {

	global $wpdb;

	if ( $_SERVER['REQUEST_METHOD'] === 'POST' ):

		if( array_key_exists( 'postid' , $_POST )
			&& array_key_exists( '_wpnonce' , $_POST )
			&& wp_verify_nonce( $_POST['_wpnonce'], 'seed-confirm-form-'.$_POST['postid'] )
			&& apply_filters('google_invre_is_valid_request_filter', true) ) {

		$name = $_POST['seed-confirm-name'];
		$contact = $_POST['seed-confirm-contact'];
		$order_id = $_POST['seed-confirm-order'];
		$bank = array_key_exists('seed-confirm-account-number', $_POST) ? $_POST['seed-confirm-account-number'] : '';
		$amount = $_POST['seed-confirm-amount'];
		$date = $_POST['seed-confirm-date'];
		$hour = $_POST['seed-confirm-hour'];
		$minute = $_POST['seed-confirm-minute'];
		$optional_information = array_key_exists('seed-confirm-optional-information', $_POST) ? $_POST['seed-confirm-optional-information'] : '';
		$optional_address = array_key_exists('seed-confirm-optional-address', $_POST) ? $_POST['seed-confirm-optional-address'] : '';
		$the_content = '<div class="seed_confirm_log">';

		$notify_value_meta = $wpdb->get_results(
			$wpdb->prepare("SELECT DISTINCT(meta_value) AS value FROM $wpdb->postmeta where meta_key = %s AND meta_value = %d", 'seed-confirm-order', $order_id)
			, ARRAY_A);

		if (!empty($notify_value_meta[0]['value']) && $notify_value_meta[0]['value'] === $order_id) {
			$order  = wc_get_order( $notify_value_meta[0]['value'] );
			$order_url = $order->get_view_order_url();
			$order_link = sprintf( wp_kses( __( 'Order number <a href="%s">%s</a> has been noted.', 'seed-confirm' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( $order_url ), $notify_value_meta[0]['value'] );
			$_SESSION['resp_message_error'] = $order_link;
			return;
		}

		if (trim($name) != '') {
			$the_content .= '<strong>' . esc_html__('Name', 'seed-confirm') . ': </strong>';
			$the_content .= '<span>' . $name . '</span><br>';
		}

		if (trim($contact) != '') {
			$the_content .= '<strong>' . esc_html__('Contact', 'seed-confirm') . ': </strong>';
			$the_content .= '<span>' . $contact . '</span><br>';
		}

		if (trim($optional_address) != '') {
			$the_content .= '<strong>' . esc_html__('Address', 'seed-confirm') . ': </strong>';
			$the_content .= '<span>' . $optional_address . '</span><br>';
		}

		if (trim($order_id) != '') {
			$the_content .= '<strong>' . esc_html__('Order no', 'seed-confirm') . ': </strong>';
			$the_content .= '<span><a href="' . get_admin_url() . 'post.php?post=' . $order_id . '&action=edit" target="_blank">' . $order_id . '</a></span><br>';
		}

		if (trim($bank) != '') {
			list($bank_name, $account_number) = explode(',', $bank);

			$the_content .= '<strong>' . esc_html__('Bank name', 'seed-confirm') . ': </strong>';
			$the_content .= '<span>' . $bank_name . '</span><br>';
			$the_content .= '<strong>' . esc_html__('Account no', 'seed-confirm') . ': </strong>';
			$the_content .= '<span>' . $account_number . '</span><br>';
		}

		if (trim($amount) != '') {
			$the_content .= '<strong>' . esc_html__('Amount', 'seed-confirm') . ': </strong>';
			$the_content .= '<span>' . $amount . '</span><br>';
		}

		if (trim($date) != '') {
			$the_content .= '<strong>' . esc_html__('Date', 'seed-confirm') . ': </strong>';
			$the_content .= '<span>' . $date;

			if (trim($hour) != '') {
				$the_content .= ' ' . $hour;

				if (trim($minute) != '') {
					$the_content .= ':' . $minute;
				} else {
					$the_content .= ':00';
				}
			}
			$the_content .= '</span><br>';
		}

		if (trim($optional_information) != '') {
			$the_content .= '<strong>' . esc_html__('Remark', 'seed-confirm') . ': </strong>';
			$the_content .= '<span>' . $optional_information . '</span><br>';
		}

		$the_content .= '</div>';

		$symbol = get_option('seed_confirm_symbol', (function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '฿'));

		$transfer_notification_id = wp_insert_post(array(
			'post_title' => __('Order no. ', 'seed-confirm') . $order_id . __(' by ', 'seed-confirm') . $name . ' (' . $amount . ' ' . $symbol . ')',
			'post_content' => $the_content,
			'post_type' => 'seed_confirm_log',
			'post_status' => 'publish'
		)
	);


		/* Upload slip file */
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$uploadedfile = $_FILES['seed-confirm-slip'];

		// Check file type allowed
		$allowed = array("image/jpeg", "image/png", "image/gif", "application/pdf");
		if (!empty($uploadedfile)) {
			if(!in_array($uploadedfile['type'], $allowed)) {
			  	$_SESSION['resp_message_error'] = __( 'This is not an allowed file type. Only JPG, PNG, GIF and PDF files are allowed.', 'seed-confirm' );
				return;
			}
		}

		$upload_overrides = array( 'test_form' => false, 'unique_filename_callback' => 'seed_unique_filename' );

		$file_upload = wp_handle_upload( $uploadedfile, $upload_overrides );

		if ( $file_upload && ! isset( $file_upload['error'] ) ) {

			$pos = strpos($file_upload['type'], 'application');

			if ($pos !== false) {
				$url = $file_upload['url'];
				$file_link = sprintf( wp_kses( __( '<a href="%s">View attatched file</a>', 'seed-confirm' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( $url ) );
				$the_content .= '<br>'.$file_link;
			} else {
				$the_content .= '<br><img class="seed-confirm-img" src="' . $file_upload['url'] . '" />';
			}

			$attrs = array(
				'ID' => $transfer_notification_id,
				'post_content' => $the_content,
			);

			wp_update_post($attrs);
			update_post_meta($transfer_notification_id, 'seed-confirm-image', $file_upload['url']);

		} else {

    		$_SESSION['resp_message_error'] = $file_upload['error'];
			return;

		}

		/* Send email to admin. */
		$headers = array('MIME-Version: 1.0','Content-Type: text/html; charset=UTF-8','From: GARMIN Thailand <' . get_option('admin_email') . '>', 'X-Mailer: PHP/' . phpversion());

		$mailsent = wp_mail(get_option('seed_confirm_email_notification', get_option('admin_email')), 'Bank transfer notification', $the_content, $headers);

		if (!add_post_meta($transfer_notification_id, 'seed-confirm-name', $_POST['seed-confirm-name'], true))
			update_post_meta($transfer_notification_id, 'seed-confirm-name', $_POST['seed-confirm-name']);

		if (!add_post_meta($transfer_notification_id, 'seed-confirm-contact', $_POST['seed-confirm-contact'], true))
			update_post_meta($transfer_notification_id, 'seed-confirm-contact', $_POST['seed-confirm-contact']);

		if (array_key_exists('seed-confirm-optional-address', $_POST)) {
			if (!add_post_meta($transfer_notification_id, 'seed-confirm-optional-address', $_POST['seed-confirm-optional-address'], true))
				update_post_meta($transfer_notification_id, 'seed-confirm-optional-address', $_POST['seed-confirm-optional-address']);
		}

		if (!add_post_meta($transfer_notification_id, 'seed-confirm-order', $_POST['seed-confirm-order'], true))
			update_post_meta($transfer_notification_id, 'seed-confirm-order', $_POST['seed-confirm-order']);

		if (array_key_exists('seed-confirm-account-number', $_POST)) {
			$bank = $_POST['seed-confirm-account-number'];
			list($bank_name, $account_number) = explode(',', $bank);

			if (!add_post_meta($transfer_notification_id, 'seed-confirm-bank-name', $bank_name, true))
				update_post_meta($transfer_notification_id, 'seed-confirm-bank-name', $bank_name);
			if (!add_post_meta($transfer_notification_id, 'seed-confirm-account-number', $account_number, true))
				update_post_meta($transfer_notification_id, 'seed-confirm-account-number', $account_number);
		}

		if (!add_post_meta($transfer_notification_id, 'seed-confirm-amount', $_POST['seed-confirm-amount'], true))
			update_post_meta($transfer_notification_id, 'seed-confirm-amount', $_POST['seed-confirm-amount']);

		if (!add_post_meta($transfer_notification_id, 'seed-confirm-date', $_POST['seed-confirm-date'], true))
			update_post_meta($transfer_notification_id, 'seed-confirm-date', $_POST['seed-confirm-date']);

		if (!add_post_meta($transfer_notification_id, 'seed-confirm-hour', $_POST['seed-confirm-hour'], true))
			update_post_meta($transfer_notification_id, 'seed-confirm-hour', $_POST['seed-confirm-hour']);

		if (!add_post_meta($transfer_notification_id, 'seed-confirm-minute', $_POST['seed-confirm-minute'], true))
			update_post_meta($transfer_notification_id, 'seed-confirm-minute', $_POST['seed-confirm-minute']);

		if (array_key_exists('seed-confirm-optional-information', $_POST)) {
			if (!add_post_meta($transfer_notification_id, 'seed-confirm-optional-information', $_POST['seed-confirm-optional-information'], true))
				update_post_meta($transfer_notification_id, 'seed-confirm-optional-information', $_POST['seed-confirm-optional-information']);
		}

		/* Automatic update woo order status, if woocommerce is installed and admin not check unautomatic */
		if (is_woo_activated() && get_option('seed_confirm_unchange_status', 'no') == 'no') {
			$post = get_post($order_id);

			if (!empty($post) && $post->post_type == 'shop_order') {
				$order = new WC_Order($order_id);
				$seed_confirm_change_status_to = get_option('seed_confirm_change_status_to');

				switch ($seed_confirm_change_status_to) {
					case 'checking-payment':
					$order->update_status('checking-payment', 'order_note');
					break;

					case 'processing':
					$order->update_status('processing', 'order_note');
					break;
				}

			}
		}

		/* Redirect... */
		$redirect_paget = get_option('seed_confirm_redirect_page', '');

		if(!empty($redirect_paget) && $redirect_paget>0){
			wp_redirect( get_permalink( $redirect_paget ) );
			die();
		}

		$_SESSION['resp_message'] = get_option('seed_confirm_notification_text');
	}

endif;
}

/**
* Register seed_confirm_log PostType.
* Store confirmation payment.
*/
add_action('init', 'seed_confirm_register_transfer_notifications_logs');

function seed_confirm_register_transfer_notifications_logs() {
	register_post_type('seed_confirm_log', array(
		'labels'	=> array(
			'name'		=> __('Confirm Logs', 'seed-confirm'),
			'singular_name' => __('Log'),
			'menu_name'	=> __('Confirm Logs','seed-confirm')
		),
		'capabilities' => array(
			'create_posts' => 'do_not_allow',
			'edit_posts' => 'manage_woocommerce',
		),
		'map_meta_cap'	=> true,
		'supports' => array( 'title', 'editor', 'custom-fields', 'thumbnail' ),
		'has_archive'	=> false,
		'menu_icon'   => 'dashicons-paperclip',
		'public'	=> true,
		'exclude_from_search' => true,
		'publicly_queryable'	=> false
	)
);
}

/**
* Adds a submenu page under a seed_confirm_log posttype.
*/
add_action('admin_menu', 'seed_register_confirm_log_settings_page');

function seed_register_confirm_log_settings_page() {
	add_submenu_page(
		'edit.php?post_type=seed_confirm_log',
		__( 'Settings', 'seed-confirm' ),
		__( 'Settings', 'seed-confirm' ),
		'manage_woocommerce',
		'seed-confirm-log-settings',
		'seed_confirm_log_settings_form'
	);
}

/**
* Callback for submenu page under a seed_confirm_log.
*/
function seed_confirm_log_settings_form() {

	/* Set default setting's tab */
	if(!isset($_GET['tab']) || $_GET['tab'] == '' || $_GET['tab'] == 'settings'){
		$nav_tab_active = 'settings';
	}elseif($_GET['tab'] == 'bacs'){
		$nav_tab_active = 'bacs';
	}elseif($_GET['tab'] == 'schedule'){
		$nav_tab_active = 'schedule';
	}elseif($_GET['tab'] == 'license'){
		$nav_tab_active = 'license';
	}else{
		$nav_tab_active = 'settings';
	}
	?>
	<form method="post" action="" name="form">
		<h2 class="nav-tab-wrapper seed-confirm-tab-wrapper">
			<a href="<?php echo admin_url('edit.php?post_type=seed_confirm_log&page=seed-confirm-log-settings&tab=settings'); ?>" class="nav-tab <?php if($nav_tab_active == 'settings') echo 'nav-tab-active'; ?>"><?php _e( 'Seed Confirm Settings', 'seed-confirm' ); ?></a>
			<?php if(!is_woo_activated()){ ?>
			<a href="<?php echo admin_url('edit.php?post_type=seed_confirm_log&page=seed-confirm-log-settings&tab=bacs'); ?>" class="nav-tab <?php if($nav_tab_active == 'bacs') echo 'nav-tab-active'; ?>"><?php _e( 'Bank Accounts', 'seed-confirm' ); ?></a>
			<?php } ?>
			<?php if(is_woo_activated()){ ?>
			<a href="<?php echo admin_url('edit.php?post_type=seed_confirm_log&page=seed-confirm-log-settings&tab=schedule'); ?>" class="nav-tab <?php if($nav_tab_active == 'schedule') echo 'nav-tab-active'; ?>"><?php _e( 'Auto Cancel Unpaid Orders', 'seed-confirm' ); ?></a>
			<?php } ?>
			<a href="<?php echo admin_url('edit.php?post_type=seed_confirm_log&page=seed-confirm-log-settings&tab=license'); ?>" class="nav-tab <?php if($nav_tab_active == 'license') echo 'nav-tab-active'; ?>"><?php _e( 'License', 'seed-confirm' ); ?></a>
		</h2>
		<?php if( isset($_SESSION['saved']) && $_SESSION['saved'] == 'true' ){ ?>
		<div class="updated inline">
			<p><strong><?php _e('Your settings have been saved.', 'seed-confirm'); ?></strong></p>
		</div>
		<?php unset($_SESSION['saved']); ?>
		<?php } ?>
		<!-- Settings tab -->
		<?php if($nav_tab_active == 'settings'){?>


		<h2 class="title"><?php _e('Confirm Payment Form', 'seed-confirm'); ?></h2>
		<table class="form-table" width="100%">
			<tbody>
				<tr>
					<th><label for="seed_notification_text"><?php _e( 'Page', 'seed-confirm' ) ?></label></th>
					<td>
						<select name="seed_confirm_page" id="seed_confirm_page">
							<?php
							$pages = get_pages();
							foreach ( $pages as $page ) {
								?>
								<option value="<?php echo $page->ID;?>" <?php if( get_option('seed_confirm_page') == $page->ID){ echo 'selected="selected"';} ?> ><?php echo $page->post_title;?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php _e('Required fields', 'seed-confirm'); ?></th>
						<td>
							<?php $seed_confirm_required = json_decode( get_option( 'seed_confirm_required' ), true ); ?>
							<label><input <?php if( isset( $seed_confirm_required['seed_confirm_name'] ) ){ ?> checked="checked" <?php } ?> type="checkbox" value="true" name="seed_confirm_required[seed_confirm_name]"> <?php _e( 'Name', 'seed-confirm' ); ?></label>
							<br/>
							<label><input <?php if( isset( $seed_confirm_required['seed_confirm_contact'] ) ){ ?> checked="checked" <?php } ?>  type="checkbox" value="true" name="seed_confirm_required[seed_confirm_contact]"> <?php _e( 'Contact', 'seed-confirm' ); ?></label>
							<br/>
							<label><input <?php if( isset( $seed_confirm_required['seed_confirm_order'] ) ){ ?> checked="checked" <?php } ?>  type="checkbox" value="true" name="seed_confirm_required[seed_confirm_order]"> <?php _e( 'Order', 'seed-confirm' ); ?></label>
							<br/>
							<label><input <?php if( isset( $seed_confirm_required['seed_confirm_amount'] ) ){ ?> checked="checked" <?php } ?>  type="checkbox" value="true" name="seed_confirm_required[seed_confirm_amount]"> <?php _e( 'Amount', 'seed-confirm' ); ?></label>
							<br/>
							<label><input <?php if( isset( $seed_confirm_required['seed_confirm_account_number'] ) ){ ?> checked="checked" <?php } ?>  type="checkbox" value="true" name="seed_confirm_required[seed_confirm_account_number]"> <?php _e( 'Bank Account', 'seed-confirm' ); ?></label>
							<br/>
							<label><input <?php if( isset( $seed_confirm_required['seed_confirm_date'] ) ){ ?> checked="checked" <?php } ?>  type="checkbox" value="true" name="seed_confirm_required[seed_confirm_date]"> <?php _e( 'Transfer Date', 'seed-confirm' ); ?></label>
							<br/>
							<label><input <?php if( isset( $seed_confirm_required['seed_confirm_slip'] ) ){ ?> checked="checked" <?php } ?>  type="checkbox" value="true" name="seed_confirm_required[seed_confirm_slip]"> <?php _e( 'Payment Slip', 'seed-confirm' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><?php _e('Optional fields', 'seed-confirm'); ?></th>
						<td>
							<?php $seed_confirm_optional = json_decode( get_option( 'seed_confirm_optional' ), true ); ?>
							<?php
							/* Not necessary to display if the woocommerce is installed. */
							$disabled = '';
							$disabled_note = '';
							if(is_woo_activated()){
								$disabled = ' disabled="disabled" ';
								$disabled_note = __(' <i>(Disable when WooCommerce is activated.)</i>', 'seed-confirm');
							}
							?>
							<label><input <?php echo $disabled ;?> <?php if( isset( $seed_confirm_optional['optional_address'] ) ){ ?> checked="checked" <?php } ?> type="checkbox" value="true" name="seed_confirm_optional[optional_address]"> <?php _e( 'Address', 'seed-confirm' ); ?><?php echo $disabled_note ;?></label>
							<br/>
							<label><input <?php if( isset( $seed_confirm_optional['optional_information'] ) ){ ?> checked="checked" <?php } ?> type="checkbox" value="true" name="seed_confirm_optional[optional_information]"> <?php _e( 'Remark', 'seed-confirm' ); ?></label>
							<br/>
						</td>
					</tr>
				</tbody>
			</table>

			<h2 class="title"><?php _e('After Submit', 'seed-confirm');?></h2>

			<table class="form-table" width="100%">
				<tbody>
					<tr>
						<th><?php _e('Page to redirect', 'seed-confirm'); ?></th>
						<td>
							<select name="seed_confirm_redirect_page" id="seed_confirm_redirect_page">
								<option value=""><?php _e('(Current Page)', 'seed-confirm'); ?></option>
								<?php
								$pages = get_pages();
								foreach ( $pages as $page ) {
									?>
									<option value="<?php echo $page->ID;?>" <?php if( get_option('seed_confirm_redirect_page') == $page->ID){ echo 'selected="selected"';} ?> ><?php echo $page->post_title;?></option>
									<?php
								}
								?>
							</select>
						</td>
					</tr>
					<tr class="seed_notification_text_row">
						<th><label for="seed_notification_text"><?php _e( 'Message (for Current Page)', 'seed-confirm' ) ?></label></th>
						<td><input type="text" class="large-text" value="<?php echo get_option( 'seed_confirm_notification_text' ); ?>" id="seed_confirm_notification_text" name="seed_confirm_notification_text"></td>
					</tr>
					<tr class="seed_notification_bg_color_row">
						<th><label for="seed_notification_bg_color"><?php _e( 'Background Color', 'seed-confirm' ); ?></label></th>
						<td><input type="text" class="color-picker" value="<?php echo get_option( 'seed_confirm_notification_bg_color' ); ?>" id="seed_confirm_notification_bg_color" name="seed_confirm_notification_bg_color"></td>
					</tr>

					<tr>
						<th><?php _e('Currency symbol in Log', 'seed-confirm'); ?></th>
						<td><input type="text" value="<?php echo get_option( 'seed_confirm_symbol' ); ?>" id="seed_confirm_symbol" name="seed_confirm_symbol" class="small-text"></td>
					</tr>

					<tr>
						<th><?php _e('Store Admin E-mail', 'seed-confirm'); ?></th>
						<td><input type="text" value="<?php echo get_option( 'seed_confirm_email_notification', get_option('admin_email') ); ?>" id="seed_confirm_email_notification" name="seed_confirm_email_notification" class="regular-text">
							<p class="description" id="seed_confirm_email_notification_description"><?php _e('Notify after submit. Seperate e-mail accounts by comma (,).', 'seed-confirm');?></p></td>
						</tr>
						<tr>
							<th><?php _e('Change Order Status?', 'seed-confirm'); ?></th>
							<td>
								<label><input type="radio" value="yes" id="seed_confirm_unchange_status_yes" name="seed_confirm_unchange_status" <?php if(get_option( 'seed_confirm_unchange_status', 'no' ) == 'yes'){ ?>checked="checked"<?php } ?>> <?php _e('Unchange', 'seed-confirm'); ?></label> <br/>
								<label><input type="radio" value="no" id="seed_confirm_unchange_status_no" name="seed_confirm_unchange_status" <?php if(get_option( 'seed_confirm_unchange_status', 'no' ) == 'no'){ ?>checked="checked"<?php } ?>> <?php _e('Change To', 'seed-confirm'); ?> </label>
								<select name="seed_confirm_change_status_to" id="seed_confirm_change_status_to" <?php if(get_option( 'seed_confirm_unchange_status', 'no' ) == 'yes'){ ?> disabled="disabled" <?php }?>>
									<option value="processing" <?php if(get_option( 'seed_confirm_change_status_to' ) == 'processing'){ ?> selected="selected" <?php } ?>><?php _e('Processing', 'seed-confirm');?></option>
									<option value="checking-payment" <?php if(get_option( 'seed_confirm_change_status_to' ) == 'checking-payment'){ ?> selected="selected" <?php } ?>><?php _e('Checking Payment', 'seed-confirm');?></option>
								</select>
							</td>
						</tr>

					</tbody>
				</table>


				<?php } ?>
				<!-- Bacs tab - hide if woocommerce is activated. -->
				<?php if(!is_woo_activated()){ ?>
				<?php if($nav_tab_active == 'bacs'){ ?>

				<?php $account_details = get_option( 'woocommerce_bacs_accounts'); ?>
				<h2><?php _e( 'Bank Accounts', 'seed-confirm' ); ?></h2>
				<p><?php _e('Direct bank/wire transfer account information.', 'seed-confirm'); ?></p>
				<table class="form-table" width="100%">
					<tbody>
						<tr valign="top">
							<th scope="row" class="titledesc"><?php _e( 'Account Details', 'seed-confirm' ); ?>:</th>
							<td id="bacs_accounts" class="forminp">
								<table class="widefat seed-confirm-table sortable" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th class="sort">&nbsp;</th>
											<th><?php _e( 'Account Name', 'seed-confirm' ); ?></th>
											<th><?php _e( 'Account Number', 'seed-confirm' ); ?></th>
											<th><?php _e( 'Bank Name', 'seed-confirm' ); ?></th>
											<th><?php _e( 'Branch', 'seed-confirm' ); ?></th>
											<th><?php _e( 'IBAN', 'seed-confirm' ); ?></th>
											<th><?php _e( 'BIC / Swift', 'seed-confirm' ); ?></th>
										</tr>
									</thead>
									<tbody class="accounts">
										<?php
										$i = -1;
										if ( isset($account_details) && is_array($account_details) ) {
											foreach ( $account_details as $account ) {
												$i++;

												echo '
												<tr class="account">
												<td class="sort"></td>
												<td><input type="text" value="' . esc_attr( wp_unslash( $account['account_name'] ) ) . '" name="bacs_account_name[' . $i . ']" /></td>
												<td><input type="text" value="' . esc_attr( $account['account_number'] ) . '" name="bacs_account_number[' . $i . ']" /></td>
												<td><input type="text" value="' . esc_attr( wp_unslash( $account['bank_name'] ) ) . '" name="bacs_bank_name[' . $i . ']" /></td>
												<td><input type="text" value="' . esc_attr( $account['sort_code'] ) . '" name="bacs_sort_code[' . $i . ']" /></td>
												<td><input type="text" value="' . esc_attr( $account['iban'] ) . '" name="bacs_iban[' . $i . ']" /></td>
												<td><input type="text" value="' . esc_attr( $account['bic'] ) . '" name="bacs_bic[' . $i . ']" /></td>
												</tr>';
											}
										}
										?>
									</tbody>
									<tfoot>
										<tr>
											<th colspan="7"><a href="#" class="add button"><?php _e( '+ Add Account', 'seed-confirm' ); ?></a> <a href="#" class="remove_rows button"><?php _e( 'Remove selected account(s)', 'seed-confirm' ); ?></a></th>
										</tr>
									</tfoot>
								</table>
							</td>
						</tr>
					</tbody>
				</table>

				<?php } ?>
				<?php } ?>
				<!-- Schedule tab - show if woocommerce is activated. -->
				<?php if(is_woo_activated()){ ?>
				<?php if($nav_tab_active == 'schedule'){ ?>

				<h2><?php _e( 'Auto Cancel Unpaid Orders', 'seed-confirm' ); ?></h2>
				<p><?php _e('Change order status from on-hold to cancelled automatically after x minutes.', 'seed-confirm'); ?></p>
				<table class="form-table" width="100%">
					<tbody>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e('Enable?', 'seed-confirm'); ?>
							</th>
							<td>
								<input id="seed_confirm_schedule_status" name="seed_confirm_schedule_status" type="checkbox" value="true" <?php if(get_option('seed_confirm_schedule_status') == 'true'){ ?> checked="checked" <?php } ?> />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e('Pending time', 'seed-confirm'); ?>
							</th>
							<td>
								<input id="seed_confirm_time" name="seed_confirm_time" type="text" class="small-text <?php if(get_option('seed_confirm_schedule_status') != 'true'){ ?> disabled <?php } ?>" value="<?php echo get_option('seed_confirm_time', 1440);?>" <?php if(get_option('seed_confirm_schedule_status') != 'true'){ ?> readonly="readonly" <?php } ?> />
								<label class="description" for="seed_confirm_time"> <?php _e('Minutes (60 minutes = 1 hour, 1440 minutes = 1 day)', 'seed-confirm'); ?></label>
							</td>
						</tr>
					</tbody>
				</table>

				<?php } ?>
				<?php } ?>
				<!-- License tab -->
				<?php
				if($nav_tab_active == 'license'){
					$license = get_option( 'seed_confirm_license_key' );
					$status  = get_option( 'seed_confirm_license_status' );
					?>
					<h2 class="title"><?php _e('License', 'seed-confirm');?></h2>
					<table class="form-table" width="100%">
						<tbody>
							<tr valign="top">
								<th scope="row" valign="top">
									<?php _e('License Key', 'seed-confirm'); ?>
								</th>
								<td>
									<input id="seed_confirm_license_key" name="seed_confirm_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
									<label class="description" for="seed_confirm_license_key"><?php _e('Enter your license key', 'seed-confirm'); ?></label>
								</td>
							</tr>
							<?php if( false !== $license ) { ?>
							<tr valign="top">
								<th scope="row" valign="top">
									<?php _e('Activate License', 'seed-confirm'); ?>
								</th>
								<td>
									<?php if( $status !== false && $status == 'valid' ) { ?>
									<span style="color:green;"><?php _e('active', 'seed-confirm'); ?></span>
									<input type="submit" class="button-secondary" name="seed_confirm_license_deactivate" value="<?php _e('Deactivate License', 'see-confirm'); ?>"/>
									<?php } else { ?>
									<input type="submit" class="button-secondary" name="seed_confirm_license_activate" value="<?php _e('Activate License', 'see-confirm'); ?>"/>
									<?php } ?>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					<?php } ?>

					<!-- Submit form -->
					<p class="submit">
						<?php wp_nonce_field( 'seed-confirm' ) ?>
						<?php submit_button(); ?>
					</p>
				</form>
				<?php
			}

/**
* Save settings and bacs into database.
* Bacs use wp_options.woocommerce_bacs_accounts to keep bacs values.
* Thus this plugin can share datas with woocommerce plugin.
* I copy this code from class-wc-gateway-bacs.php
* @copy wp-content/plugins/woocommerce/includes/gateways/bacs/class-wc-gateway-bacs.php
*/
add_action('init', 'seed_confirm_save_settings');

function seed_confirm_save_settings(){

	if(isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'seed-confirm')){

		/* Settings tab activate. */
		if(!isset($_GET['tab']) || $_GET['tab'] == '' || $_GET['tab'] == 'settings'){

			update_option( 'seed_confirm_page', $_POST['seed_confirm_page'] );
			update_option( 'seed_confirm_notification_text', $_POST['seed_confirm_notification_text'] );
			update_option( 'seed_confirm_notification_bg_color', $_POST['seed_confirm_notification_bg_color'] );
			update_option( 'seed_confirm_required', json_encode( isset($_POST['seed_confirm_required'])? $_POST['seed_confirm_required']: array() ) );
			update_option( 'seed_confirm_optional', json_encode( isset($_POST['seed_confirm_optional'])? $_POST['seed_confirm_optional']: array() ) );
			update_option( 'seed_confirm_symbol', $_POST['seed_confirm_symbol'] );
			update_option( 'seed_confirm_email_notification', $_POST['seed_confirm_email_notification'] );
			update_option( 'seed_confirm_unchange_status', $_POST['seed_confirm_unchange_status'] );
			update_option( 'seed_confirm_change_status_to', (isset($_POST['seed_confirm_change_status_to']))? $_POST['seed_confirm_change_status_to']:'' );
			update_option( 'seed_confirm_redirect_page', $_POST['seed_confirm_redirect_page'] );

			$_SESSION['saved'] = 'true';
		}

		/* Bacs tab activate. */
		if(isset($_GET['tab']) && $_GET['tab'] == 'bacs'){
			$accounts = array();

			if ( isset( $_POST['bacs_account_name'] ) ) {

				$account_names   = array_map( 'seed_confirm_clean', $_POST['bacs_account_name'] );
				$account_numbers = array_map( 'seed_confirm_clean', $_POST['bacs_account_number'] );
				$bank_names      = array_map( 'seed_confirm_clean', $_POST['bacs_bank_name'] );
				$sort_codes      = array_map( 'seed_confirm_clean', $_POST['bacs_sort_code'] );
				$ibans           = array_map( 'seed_confirm_clean', $_POST['bacs_iban'] );
				$bics            = array_map( 'seed_confirm_clean', $_POST['bacs_bic'] );

				foreach ( $account_names as $i => $name ) {
					if ( ! isset( $account_names[ $i ] ) ) {
						continue;
					}

					$accounts[] = array(
						'account_name'   => $account_names[ $i ],
						'account_number' => $account_numbers[ $i ],
						'bank_name'      => $bank_names[ $i ],
						'sort_code'      => $sort_codes[ $i ],
						'iban'           => $ibans[ $i ],
						'bic'            => $bics[ $i ]
					);
				}

				update_option( 'woocommerce_bacs_accounts', $accounts );

				$_SESSION['saved'] = 'true';
			}
		}

		/* Schedule tab activate */
		if(isset($_GET['tab']) && $_GET['tab'] == 'schedule'){

			$seed_confirm_schedule_status = (array_key_exists('seed_confirm_schedule_status', $_POST))? $_POST['seed_confirm_schedule_status']:'false';
			update_option( 'seed_confirm_schedule_status', $seed_confirm_schedule_status);

			$seed_confirm_time = absint($_POST['seed_confirm_time']);
			update_option( 'seed_confirm_time', $seed_confirm_time);

			/* Clear old schedule and add new one. If user set time to 0, remove schedule and not add it (meaning disable). */

			wp_clear_scheduled_hook('seed_confirm_schedule_pending_to_cancelled_orders');

			if ($seed_confirm_schedule_status == 'true' && $seed_confirm_time > 0) {
				wp_schedule_single_event(time() + ( $seed_confirm_time * 60 ), 'seed_confirm_schedule_pending_to_cancelled_orders');
			}

			$_SESSION['saved'] = 'true';
		}

		/* License tab activate */
		if(isset($_GET['tab']) && $_GET['tab'] == 'license'){
			/* Check to see if user change new license. */
			$old = get_option( 'seed_confirm_license_key' );

			if( $old && $old != $_POST['seed_confirm_license_key'] ) {
				/* new license has been entered, so must reactivate */
				delete_option( 'seed_confirm_license_status' );
			}

			update_option( 'seed_confirm_license_key', $_POST['seed_confirm_license_key'] );

			$_SESSION['saved'] = 'true';
		}
	}
}


/**
* Add order status column to seed_confirm_log table
* @ref https://gist.github.com/ckaklamanos/a9d6a7d8caa655d5ac8c
*/
add_filter( 'manage_edit-seed_confirm_log_columns', 'seed_confirm_add_order_status_column' );

function seed_confirm_add_order_status_column($columns){

	$new_columns = array();

	if(is_woo_activated()){
		foreach($columns as $key => $column){
			if($key == 'title'){
				$new_columns[$key] = $columns[$key];
				$new_columns['order_status'] = __('Order Status', 'seed-confirm');
			}else{
				$new_columns[$key] = $columns[$key];
			}
		}
	} else {
		$new_columns = $columns;
	}

	return $new_columns;
}

/**
* Set sorable to order_status column
*/
add_filter('manage_edit-seed_confirm_log_sortable_columns', 'seed_confirm_sortable_order_status');

function seed_confirm_sortable_order_status($columns){
	$columns['order_status'] = 'order_status';
	return $columns;
}

/**
* Show order status in seed_confirm_log table
*/
add_action( 'manage_seed_confirm_log_posts_custom_column', 'seed_confirm_show_order_status', 10, 2);

function seed_confirm_show_order_status($columns, $post_id){

	if(is_woo_activated() && $columns == 'order_status'){
		$order_id = get_post_meta($post_id, 'seed-confirm-order', true);

		$order = wc_get_order($order_id);

		if(!empty($order)){
			echo wc_get_order_status_name($order->get_status());
		}
	}
}

/**
* Add order status filters dropdown to seed confirm log post type
* @since 1.3.1
*/
function seed_confirm_order_status_filter_dropdown(){

	global $post_type;

	if (!is_admin() && !is_woo_activated())
		return;

	if($post_type == 'seed_confirm_log'){

		if(isset($_GET['seed_confirm_status_filters'])){
			$selected = sanitize_text_field($_GET['seed_confirm_status_filters']);
		}

		$woocommerce_status = wc_get_order_statuses();
		$only_status = array('wc-processing', 'wc-checking-payment', 'wc-on-hold');

		echo '<select name="seed_confirm_status_filters">';
		foreach ($woocommerce_status as $key => $status) {
			if (in_array($key, $only_status)) {
				echo '<option value="'.$key.'" '.selected($key, $selected).'>'.$status.'</option>';
			}
		}
		echo "</select>";

	}
}
add_action('restrict_manage_posts','seed_confirm_order_status_filter_dropdown');


/**
* Restrict the confirm log by the chosen order status
* @since 1.3.1
*/
function seed_confirm_order_status_query($query){

	global $post_type, $pagenow, $wpdb;

	if( !is_admin() && !is_woo_activated())
		return;

	/* if we are currently on the edit screen of the post type */
	if($pagenow == 'edit.php' && $post_type == 'seed_confirm_log'){
		if(isset($_GET['seed_confirm_status_filters'])){

			$status = sanitize_text_field($_GET['seed_confirm_status_filters']);

			$query_results = $wpdb->get_results(
				$wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'shop_order' AND post_status = %s", $status), ARRAY_A
			);

			/* Post ID Array */
			$post_id = wp_list_pluck( $query_results, 'ID' );

			$query->set( 'post_type', 'seed_confirm_log' );
			$query->set( 'meta_query', array(
				array(
					'key'   => 'seed-confirm-order',
					'value' => $post_id,
				),
			));

		}
	}
}
add_action('pre_get_posts','seed_confirm_order_status_query');


/**
* Add action button order status wc-checking-payment
* @param  array $actions
* @param  object $order
* @return array
* @since 1.3.1
*/
function seed_confirm_add_order_action_button ($actions, $order){
	if($order->get_status() == 'checking-payment') {
		$actions = array();
		$actions['processing'] = array(
			'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=processing&order_id=' . $order->get_id() ), 'woocommerce-mark-order-status' ),
			'name'      => __( 'Processing', 'woocommerce' ),
			'action'    => "processing",
		);
		$actions['complete'] = array(
			'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=completed&order_id=' . $order->get_id() ), 'woocommerce-mark-order-status' ),
			'name'      => __( 'Complete', 'woocommerce' ),
			'action'    => "complete",
		);
		$actions['view'] = array(
			'url'       => admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' ),
			'name'      => __( 'View', 'woocommerce' ),
			'action'    => "view",
		);
	}
	return $actions;
}
add_action( 'woocommerce_admin_order_actions', 'seed_confirm_add_order_action_button', 10, 2 );


/**
************************************
* Activate license key
************************************
*/

add_action('admin_init', 'seed_confirm_activate_license');

function seed_confirm_activate_license() {

	/* listen for our activate button to be clicked */
	if( isset( $_POST['seed_confirm_license_activate'] ) ) {

		/* run a quick security check */
		if( !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'seed-confirm') )
			return;

		/* retrieve the license from the database */
		$license = trim( get_option( 'seed_confirm_license_key' ) );


		/* data to send in our API request */
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
'item_name'  => urlencode( EDD_SEED_CONFIRM_ITEM_NAME ), // the name of our product in EDD
'url'        => home_url()
);

		/* Call the custom API. */
		$response = wp_remote_post( EDD_SEED_CONFIRM_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		/* make sure the response came back okay */
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.', 'seed-confirm' );
			}

		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {

				switch( $license_data->error ) {

					case 'expired' :

					$message = sprintf(
						__( 'Your license key expired on %s.', 'seed-confirm' ),
						date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
					);
					break;

					case 'revoked' :

					$message = __( 'Your license key has been disabled.', 'seed-confirm' );
					break;

					case 'missing' :

					$message = __( 'Invalid license.', 'seed-confirm' );
					break;

					case 'invalid' :
					case 'site_inactive' :

					$message = __( 'Your license is not active for this URL.', 'seed-confirm' );
					break;

					case 'item_name_mismatch' :

					$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'seed-confirm' ), EDD_SEED_CONFIRM_ITEM_NAME );
					break;

					case 'no_activations_left':

					$message = __( 'Your license key has reached its activation limit.', 'seed-confirm' );
					break;

					default :

					$message = __( 'An error occurred, please try again.', 'seed-confirm' );
					break;
				}

			}

		}

		/* Check if anything passed on a message constituting a failure */
		if ( ! empty( $message ) ) {
			$base_url = admin_url( 'edit.php?post_type=seed_confirm_log&page=seed-confirm-log-settings&tab=license' );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		/* $license_data->license will be either "valid" or "invalid" */

		update_option( 'seed_confirm_license_status', $license_data->license );
		wp_redirect( admin_url( 'edit.php?post_type=seed_confirm_log&page=seed-confirm-log-settings&tab=license' ) );
		exit();
	}
}

/**
**********************************************
* Deactivate license.
**********************************************
*/
add_action('admin_init', 'seed_confirm_deactivate_license');

function seed_confirm_deactivate_license() {

	/* listen for our activate button to be clicked */
	if( isset( $_POST['seed_confirm_license_deactivate'] ) ) {

		/* run a quick security check */
		if( !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'seed-confirm') )
			return;

		/* retrieve the license from the database */
		$license = trim( get_option( 'seed_confirm_license_key' ) );

		/* data to send in our API request */
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => urlencode( EDD_SEED_CONFIRM_ITEM_NAME ),
			'url'        => home_url()
		);

		/* Call the custom API. */
		$response = wp_remote_post( EDD_SEED_CONFIRM_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		/* make sure the response came back okay */
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.', 'seed-confirm' );
			}

			$base_url = admin_url( 'edit.php?post_type=seed_confirm_log&page=seed-confirm-log-settings&tab=license' );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		/* decode the license data */
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		/* $license_data->license will be either "deactivated" or "failed" */
		if( $license_data->license == 'deactivated' ) {
			delete_option( 'seed_confirm_license_status' );
		}

		wp_redirect( admin_url( 'edit.php?post_type=seed_confirm_log&page=seed-confirm-log-settings&tab=license' ) );
		exit();
	}
}

/**
* Show admin notice if activate/deactivate license is fail.
*/
add_action( 'admin_notices', 'seed_confirm_admin_notices' );

function seed_confirm_admin_notices() {
	if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

		switch( $_GET['sl_activation'] ) {

			case 'false':
			$message = urldecode( $_GET['message'] );
			?>
			<div class="error">
				<p><?php echo $message; ?></p>
			</div>
			<?php
			break;

			case 'true':
			default:
			/* Developers can put a custom success message here for when activation is successful if they way. */
			break;
		}
	}
}

/**
* Copy this function from woocommerce.
* @copy wp-content/plugins/woocommerce/includes/wc-formatting-functions.php
*/
function seed_confirm_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'wc_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
* Add confirm payment button into my oder page.
* For woocommerce only
* @param $actions
* @param $order woocommerce order
* @ref hook http://hookr.io/filters/woocommerce_my_account_my_orders_actions/
*/
add_filter('woocommerce_my_account_my_orders_actions', 'seed_add_confirm_button', 10, 2);

function seed_add_confirm_button($actions, $order){

	$page_id = get_option('seed_confirm_page', true);
	$page = get_post($page_id);
	if(!$page){
		return $actions;
	}

	$order_id = $order->get_id();
	$url = get_page_link($page->ID);

	/* Want to check this order has confirm-payment */
	$params = array(
		'post_type' => 'seed_confirm_log',
		'meta_key' => 'seed-confirm-order',
		'meta_value' => $order_id
	);

	$seed_confirm_log = get_posts( $params );

	/* Want to check this order already send product to user.*/
	$status = $order->get_status();

	if($status == 'completed'){
		$actions['-completed'] = array(
			'url'   => $url,
			'name'  => __('Confirm Payment', 'seed-confirm'),
		);
	}elseif(count($seed_confirm_log)>0){
		$actions['-noted'] = array(
			'url'   => $url,
			'name'  => __('Confirm Payment', 'seed-confirm'),
		);
	}else{
		$actions['confirm-payment'] = array(
			'url'   => $url . 'confirm-payment',
			'name'  => __('Confirm Payment', 'seed-confirm'),
		);
	}

	return $actions;
}
