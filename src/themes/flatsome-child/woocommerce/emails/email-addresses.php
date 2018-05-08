<?php
/**
 * Email Addresses
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-addresses.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     3.2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text_align = is_rtl() ? 'right' : 'left';

?><table id="addresses" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top; margin-bottom: 20px; padding:0;" border="0">
	<tr>
		<td style="text-align:<?php echo $text_align; ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; border:0; padding:0;" valign="top" width="50%">
			<h2><?php _e( 'Billing address', 'woocommerce' ); ?></h2>

			<address class="address">
				<?php echo ( $address = $order->get_formatted_billing_address() ) ? $address : __( 'N/A', 'woocommerce' ); ?>
				<?php if ( $order->get_billing_phone() ) : ?>
					<br/><?php echo esc_html( $order->get_billing_phone() ); ?>
				<?php endif; ?>
				<?php if ( $order->get_billing_email() ): ?>
					<p><?php echo esc_html( $order->get_billing_email() ); ?></p>
				<?php endif; ?>
			</address>
		</td>
		<?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && ( $shipping = $order->get_formatted_shipping_address() ) ) : ?>
			<td style="text-align:<?php echo $text_align; ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; padding:0;" valign="top" width="50%">
				<h2><?php _e( 'Shipping address', 'woocommerce' ); ?></h2>

				<address class="address"><?php echo $shipping; ?></address>
			</td>
		<?php endif; ?>
	</tr>
</table>

<?php
$require_tax     = get_post_meta( $order->id, '_require_tax_invoice', true );
$tax_personal_id = get_post_meta( $order->id, '_tax_personal_id', true );
$tax_company_id  = get_post_meta( $order->id, '_tax_company_id', true );
?>
<table id="addresses-tax" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top; margin-bottom: 40px; padding:0;" border="0">
	<tr>
		<td style="text-align:<?php echo $text_align; ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; border:0; padding:0;" valign="top">
			<h2>
				<?php _e( 'Require Tax Invoice', 'woocommerce' ); ?> :
				<?php echo ( '1' == $require_tax ? _x( 'Yes', 'additional checkout fields', 'garminbygis' ) : _x( 'No', 'additional checkout fields', 'garminbygis' ) ); ?>
				<?php echo ( ! empty( $tax_company_id ) ? _x( '(For Company)', 'additional checkout fields', 'garminbygis' ) : '' ); ?>
			</h2>

			<?php if ( '1' == $require_tax && ! empty( $tax_personal_id ) ): ?>
				<p>
					<strong><?php echo _x( 'Tax Id', 'additional checkout fields', 'garminbygis' ); ?></strong> : <?php echo $tax_personal_id; ?>
					<?php if ( ! empty( $tax_company_id ) ): ?>
						<br />
						<strong><?php echo _x( 'Head Office/Branch', 'additional checkout fields', 'garminbygis' ); ?></strong> : <?php echo $tax_company_id; ?>
					<?php endif; ?>
				</p>
			<?php endif; ?>
		</td>
	</tr>
</table>
