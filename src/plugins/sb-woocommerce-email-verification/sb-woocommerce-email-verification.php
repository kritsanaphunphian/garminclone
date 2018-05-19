<?php
/*
	Plugin Name: SB WooCommerce Email Verification
	Description: Force customers to verify account before login. Reduce spam users.
	Plugin URI: http://codecanyon.net/user/sbthemes/portfolio?ref=sbthemes
	Version: 1.3
	Author: SB Themes
	Author URI: http://codecanyon.net/user/sbthemes/portfolio?ref=sbthemes
*/
function sbwev_init() {
	ob_start();
}
add_action('init', 'sbwev_init');
require_once('admin/sb-admin-panel.php');

class SB_WooCommerce_Email_Verification {

	public $plugin_version 			= '1.3';
	public $db_version 				= '1.0';
	public $plugin_name		 		= 'WooCommerce Email Verification';
	public $menu_text 				= 'WooCommerce Email Verification';
	public $plugin_slug 			= 'sb-woocommerce-email-verification';

	public $plugin_dir_url;
	public $plugin_dir_path;

	public $menu_list_url;

	public $sb_admin;
	public $animation_styles;

	//Initialize plugin
	function __construct() {

		$this->plugin_dir_url 	= plugin_dir_url(__FILE__);
		$this->plugin_dir_path 	= plugin_dir_path(__FILE__);

		$this->menu_list_url	= admin_url('admin.php?page='.$this->plugin_slug);

		$this->sb_admin   = new SB_WooCommerce_Email_Verification_Admin($this);

		add_filter('plugin_action_links', array($this, 'sb_plugin_action_links'), 10, 2);						//Add settings link in plugins page
		add_action('wp_enqueue_scripts', array($this, 'sb_enqueue_scripts'));									//Including Required Scripts for Frontend
		add_filter('woocommerce_locate_template', array($this, 'sb_woocommerce_locate_template'), 10, 3);				//Overwrite WooCommerce templates
		add_action('woocommerce_registration_redirect', array($this, 'sb_user_autologout'), 2);
		add_action('woocommerce_thankyou', array($this, 'sb_user_checkout_autologout'), 2);
		add_action('woocommerce_before_customer_login_form', array($this, 'sb_reg_success_message'));
		add_action('woocommerce_before_customer_login_form', array($this, 'sb_resend_message'));
		add_action('woocommerce_before_customer_login_form', array($this, 'sb_verify_user'));
		add_action('woocommerce_before_customer_login_form', array($this, 'sb_resend_key'));
		add_filter('woocommerce_before_customer_login_form', array($this, 'sb_verification_message'), 10, 3);
		add_filter('wp_authenticate_user', array($this, 'sb_check_user_status_before_login'), 10, 3);
		add_filter('manage_users_columns', array($this, 'sb_manage_user_columns'));
		add_filter('manage_users_custom_column', array($this, 'sb_manage_user_column_row'), 10, 3);
		add_filter('user_row_actions', array($this, 'sb_user_table_action_links'), 10, 2);
		add_action('admin_action_sb_change_status', array($this, 'sb_change_status'));
		add_action('admin_notices', array($this, 'sb_change_status_notices'));
	}

	//Add settings link in plugins page
	function sb_plugin_action_links($links, $file) {
		if ($file == plugin_basename( __FILE__ )) {
			$sbsa_links = '<a href="'.$this->menu_list_url.'">'.__('Settings').'</a>';
			// Make the 'Settings' link appear first
			array_unshift( $links, $sbsa_links );
		}
		return $links;
	}

	//Including Required Scripts on Frontend
	function sb_enqueue_scripts() {
		global $woocommerce;
		wp_enqueue_style('sb-style', $this->plugin_dir_url.'assets/css/sb.css', array(), $this->plugin_version);
		wp_enqueue_script('jquery');
		wp_enqueue_script('sb-js', $this->plugin_dir_url.'assets/js/sb.js', array(), $this->plugin_version, true );

		$settings = $this->sb_admin->get_settings();
		$sbwucvobj = array(
			'ajax'		=>	admin_url('admin-ajax.php')
		);
		wp_localize_script('sb-js', 'sbwucvobj', $sbwucvobj);
	}

	//Locate email template
	function sb_woocommerce_locate_template($template, $template_name, $template_path) {
		global $woocommerce;

		$_template = $template;

		$plugin_path  = plugin_dir_path(__FILE__). 'woocommerce/';

		$template = $plugin_path . $template_name;
		if(!file_exists($template)) {
			$template = $_template;
		}

		return $template;
	}

	//Auto logout after registration
	function sb_user_autologout() {
		if(is_user_logged_in()) {
			wp_clear_auth_cookie();
			wp_redirect(get_permalink(get_option('woocommerce_myaccount_page_id')).'?registered=true');
			die;
		}
	}

	//Auto logout after registration on checkout
	function sb_user_checkout_autologout() {
		if(is_user_logged_in()) {
			$userdata = wp_get_current_user();
			$settings = $this->sb_admin->get_settings();
			$common_values = array_intersect($settings['restrict_roles'], $userdata->roles);
			if(count($common_values) == 0) {
				$is_approved = get_user_meta($userdata->ID, 'sbwev-approve-user', true);
				if(!$is_approved || $is_approved == 0) {
					wp_clear_auth_cookie();
				}
			}
		}
	}

	//Verify account
	function sb_verify_user() {
		if(!is_user_logged_in()) {
			if(isset($_GET['action']) && $_GET['action'] == 'verify_account') {
				$verify = 'false';
				if(isset($_GET['user_login']) && isset($_GET['key'])) {
					global $wpdb;
					$user = $wpdb->get_row($wpdb->prepare("select * from ".$wpdb->prefix."users where user_login = %s and user_activation_key = %s", $_GET['user_login'], $_GET['key']));
					if($user) {
						$verify = 'true';
						update_user_meta($user->ID, 'sbwev-approve-user', 1);
						$key = wp_generate_password(20, false);
						$wpdb->query($wpdb->prepare("update ".$wpdb->prefix."users set user_activation_key = %s where ID = %d", $key, $user->ID));
					}
				}
				wp_redirect(get_permalink(get_option('woocommerce_myaccount_page_id')).'?verify='.$verify);
				die;
			}
		}
	}

	// Custom code G-Able
	function sb_verification_message() {
		if(isset($_GET['verify'])) {
			$settings = $this->sb_admin->get_settings();
			if($_GET['verify'] == 'false') { ?>
			<ul class="woocommerce-error message-wrapper">
				<li>
					<div class="message-container container alert-color medium-text-center sb-custom-alert">
						<span class="message-icon icon-close"></span> <?php _e($settings['verification_fail'], 'woocommerce') ?>
					</div>
				</li>
			</ul>
			<?php
			}
			if($_GET['verify'] == 'true') { ?>
			<div class="woocommerce-message message-wrapper">
				<div class="message-container container success-color medium-text-center sb-custom-alert">
				  <i class="icon-checkmark"></i><?php _e($settings['verification_success'], 'woocommerce') ?>
				</div>
			</div>
			<?php
			}
		}
	}
	// Custom code G-Able
	//Registratin success message
	function sb_reg_success_message() {
		if(isset($_GET['registered']) && $_GET['registered'] == 'true') {
			$settings = $this->sb_admin->get_settings(); ?>
            <div class="woocommerce-message message-wrapper">
				<div class="message-container container success-color medium-text-center sb-custom-alert">
				  <i class="icon-checkmark"></i><?php _e($settings['registration_success'], 'woocommerce'); ?>
				</div>
			</div><?php
		}
	}
	// Custom code G-Able
	//Resend message
	function sb_resend_message() {
		if(isset($_GET['resend']) && $_GET['resend'] == 'true') {
			$settings = $this->sb_admin->get_settings(); ?>
			<div class="woocommerce-message message-wrapper">
				<div class="message-container container success-color medium-text-center sb-custom-alert">
				  <i class="icon-checkmark"></i><?php  _e($settings['new_verification_link'], 'woocommerce'); ?>
				</div><?php
		}
	}

	//Check user status before login
	function sb_check_user_status_before_login($userdata) {
		$settings = $this->sb_admin->get_settings();
		$common_values = array_intersect($settings['restrict_roles'], $userdata->roles);
		if(count($common_values) == 0) {
			$is_approved = get_user_meta($userdata->ID, 'sbwev-approve-user', true);
			if(!$is_approved || $is_approved == 0) {
				$regenerate_link = get_permalink(get_option('woocommerce_myaccount_page_id')).'?action=resend_key&user_login='.$userdata->user_login.'&nonce='.wp_create_nonce('resend_key'.$userdata->user_login);
				$userdata = new WP_Error(
					'sb_confirmation_error',
					__( str_replace('{{resend_verification_link}}', '<a href="'.$regenerate_link.'">'.$settings['resend_link_text'].'</a>', $settings['fail_login']), 'woocommerce' )
				);
			}
		}
		return $userdata;
	}

	//Resend user activation key
	function sb_resend_key() {
		if(!is_user_logged_in()) {
			if(isset($_GET['action']) && $_GET['action'] == 'resend_key') {
				$resend = 'false';
				if(isset($_GET['user_login']) && isset($_GET['nonce'])) {
					if(wp_verify_nonce($_GET['nonce'], 'resend_key'.$_GET['user_login'])) {
						global $wpdb;

						$user = get_user_by('login', $_GET['user_login']);
						if($user) {
							global $woocommerce;
							$mailer = $woocommerce->mailer();

							$resend = 'true';
							$verification_link = get_verification_link($user->user_login);

							$settings = $this->sb_admin->get_settings();

							$email_heading = __($settings['resend_email_heading'], 'woocommerce');
							$subject = __($settings['resend_email_subject'], 'woocommerce');

							$email_body = __($settings['resend_email_body'], 'woocommerce');
							$email_body = str_replace('{{verification_link}}', '<a target="_blank" href="'.$verification_link.'">'.$verification_link.'</a>', $email_body);
							$email_body = str_replace('{{verification_url}}', $verification_link, $email_body);
							$email_body = str_replace('{{user_email}}', $user->user_email, $email_body);
							$email_body = str_replace('{{user_login}}', $user->user_login, $email_body);
							$email_body = str_replace('{{user_firstname}}', $user->first_name, $email_body);
							$email_body = str_replace('{{user_lastname}}', $user->last_name, $email_body);
							$email_body = str_replace('{{user_displayname}}', $user->display_name, $email_body);

							ob_start();
							if(function_exists('wc_get_template')) {
								wc_get_template('emails/resend-verification-key.php', array(
									'email_heading' 	=> $email_heading,
									'email_body' 		=> $email_body
								));
							} else {
								woocommerce_get_template('emails/resend-verification-key.php', array(
										'email_heading' 	=> $email_heading,
										'email_body' 		=> $email_body
								));
							}
							$message = ob_get_clean();
							if(function_exists('wc_get_template')) {
								wc_mail($user->user_email, $subject, $message, $headers = "Content-Type: text/htmlrn", $attachments = "");
							} else {
								woocommerce_mail($user->user_email, $subject, $message, $headers = "Content-Type: text/htmlrn", $attachments = "");
							}

						}
						wp_redirect(get_permalink(get_option('woocommerce_myaccount_page_id')).'?resend='.$resend);
						die;
					}
				}
			}
		}
	}

	//Manage user list columns
	function sb_manage_user_columns($column) {
		$column['status'] = 'Status';
		return $column;
	}

	//Manage user column row
	function sb_manage_user_column_row($val, $column_name, $user_id) {
		$user = get_userdata($user_id);
		$return = '';
		switch($column_name) {
			case 'status' :
				$status = get_user_meta($user_id, 'sbwev-approve-user', true);
				$val = '<span style="color:red;">'.__('Not Verified', 'woocommerce').'</span>';
				if($status && $status == 1) {
					$val = '<span style="color:green;">'.__('Verified', 'woocommerce').'</span>';
				}
				return $val;
				break;
			default:
		}
		return $return;
	}

	//Add action link to user list table

	function sb_user_table_action_links($actions, $user) {
		$is_approved = get_user_meta($user->ID, 'sbwev-approve-user', true);
		if(!$is_approved || $is_approved == 0) {
			$label = 'Approve';
			$color = 'green';
		} else {
			$label = 'Unapprove';
			$color = 'red';
		}
		$actions['sb_status'] = "<a style='color:".$color."' href='" . admin_url( "users.php?action=sb_change_status&users=".$user->ID."&nonce=".wp_create_nonce('sb_change_status_'.$user->ID)) . "'>" . __( $label, 'woocommerce' ) . "</a>";
		return $actions;
	}

	function sb_change_status() {
		if(isset($_REQUEST['users']) && isset($_REQUEST['nonce'])) {
			$nonce = $_REQUEST['nonce'];
			$users = $_REQUEST['users'];

			if(wp_verify_nonce($nonce, 'sb_change_status_'.$users)) {
				$is_approved = get_user_meta($users, 'sbwev-approve-user', true);
				if(!$is_approved || $is_approved == 0) {
					$new_status = 1;
					$message_param = 'approved';
				} else {
					$new_status = 0;
					$message_param = 'unapproved';
				}
				update_user_meta($users, 'sbwev-approve-user', $new_status);
				$redirect = admin_url('users.php?updated='.$message_param);
			} else {
				$redirect = admin_url('users.php?updated=sb_false');
			}
		} else {
			$redirect = admin_url('users.php?updated=sb_false');
		}
		wp_redirect($redirect);
	}

	function sb_change_status_notices() {
		global $pagenow;
		if($pagenow == 'users.php') {
			if(isset($_REQUEST['updated'])) {
				$message = $_REQUEST['updated'];
				if($message == 'sb_false') {
					echo '<div class="updated notice error is-dismissible"><p>Something wrong. Please try again.</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
				}
				if($message == 'approved') {
					echo '<div class="updated notice is-dismissible"><p>User approved.</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
				}
				if($message == 'unapproved') {
					echo '<div class="updated notice is-dismissible"><p>User unapproved.</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
				}
			}
		}
	}

}
$sbwucv = new SB_WooCommerce_Email_Verification();

//Generate verification link for user
function get_verification_link($user_login) {
	global $wpdb;
	$key = wp_generate_password(20, false);
	$wpdb->query($wpdb->prepare("update ".$wpdb->prefix."users set user_activation_key = %s where user_login = %s", $key, $user_login));
	return $verification_link = get_permalink(get_option('woocommerce_myaccount_page_id')).'?action=verify_account&user_login='.urlencode($user_login).'&key='.urlencode($key);
}
