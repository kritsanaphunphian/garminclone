<?php do_action( 'wpo_wcpdf_before_document', $this->type, $this->order ); ?>

<?php do_action( 'wpo_wcpdf_after_document_label', $this->type, $this->order ); ?>

<table class="head container">
	<tr>
		<td></td>
		<td id="titlename">
			<?php  
				echo apply_filters( 'wpo_wcpdf_invoice_title', __( 'INVOICE', 'woocommerce-pdf-invoices-packing-slips' ) );
			?>
		</td>
	</tr>
	<tr>
		<td class="shop-info">
			<div class="shop-logo"> <?php $this->header_logo() ?> </div>			
		</td>
		<td class="header">
			<div><h3><?php _e('Order date : ', 'woocommerce-pdf-invoices-packing-slips' ); ?><?php $this->order_date(); ?></h3></div>
		</td>
		
	</tr>
	<tr>
		<td class="shop-info"> 
			<div class="shop-address">
			<?php _e('Sathorn Nakorn Tower,
					<br>100/38-39, 22nd Floor, 
					<br>North Sathon Road, 
					<br>Silom, Bangrak, 
					<br>Bangkok 10500', 'woocommerce-pdf-invoices-packing-slips' ); ?></div>
		</td>
		<td class="address billing-address">
			<?php $this->billing_address(); ?>
			<?php if ( isset($this->settings['display_email']) ) { ?>
			<div class="billing-email"><?php _e('Email : ', 'woocommerce-pdf-invoices-packing-slips' ); ?><?php $this->billing_email(); ?></div>
			<?php } ?>
			<?php if ( isset($this->settings['display_phone']) ) { ?>
			<div class="billing-phone"><?php _e('Tel : ', 'woocommerce-pdf-invoices-packing-slips' ); ?><?php $this->billing_phone(); ?></div>
			<?php } ?>
		</td>
	</tr>
</table>

<div><h3><?php _e('Payment method : ', 'woocommerce-pdf-invoices-packing-slips' ); ?><?php $this->payment_method(); ?> </h3> </div>

<?php do_action( 'wpo_wcpdf_before_order_details', $this->type, $this->order ); ?>

<table class="order-details">
	<thead>
		<tr>
			<th class="product"><?php _e('Product', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th class="quantity"><?php _e('Amount', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th class="price"><?php _e('Total', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php $items = $this->get_order_items(); if( sizeof( $items ) > 0 ) : foreach( $items as $item_id => $item ) : ?>
		<tr class="<?php echo apply_filters( 'wpo_wcpdf_item_row_class', $item_id, $this->type, $this->order, $item_id ); ?>">
			<td class="product">
				<?php $description_label = __( 'Description', 'woocommerce-pdf-invoices-packing-slips' ); // registering alternate label translation ?>
				<span class="item-name"><?php echo $item['name']; ?></span>
				<?php do_action( 'wpo_wcpdf_before_item_meta', $this->type, $item, $this->order  ); ?>
				<span class="item-meta"><?php echo $item['meta']; ?></span>
				<dl class="meta">
					<?php $description_label = __( 'SKU', 'woocommerce-pdf-invoices-packing-slips' ); // registering alternate label translation ?>
					<?php if( !empty( $item['sku'] ) ) : ?><dt class="sku"><?php _e( 'SKU:', 'woocommerce-pdf-invoices-packing-slips' ); ?></dt><dd class="sku"><?php echo $item['sku']; ?></dd><?php endif; ?>
					<?php if( !empty( $item['weight'] ) ) : ?><dt class="weight"><?php _e( 'Weight:', 'woocommerce-pdf-invoices-packing-slips' ); ?></dt><dd class="weight"><?php echo $item['weight']; ?><?php echo get_option('woocommerce_weight_unit'); ?></dd><?php endif; ?>
				</dl>
				<?php do_action( 'wpo_wcpdf_after_item_meta', $this->type, $item, $this->order  ); ?>
			</td>
			<td class="quantity"><?php echo $item['quantity']; ?></td>
			<td class="price"><?php echo $item['order_price']; ?></td>
		</tr>
		<?php endforeach; endif; ?>
	</tbody>
	<tfoot>
		<tr class="no-borders">
			<td class="no-borders">
				<div class="customer-notes">
					<?php do_action( 'wpo_wcpdf_before_customer_notes', $this->type, $this->order ); ?>
					<?php if ( $this->get_shipping_notes() ) : ?>
						<h3><?php _e( 'Customer Notes', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
						<?php $this->shipping_notes(); ?>
					<?php endif; ?>
					<?php do_action( 'wpo_wcpdf_after_customer_notes', $this->type, $this->order ); ?>
				</div>				
			</td>
			<td class="no-borders" colspan="2">
				<table class="totals">
					<tfoot>
						<?php foreach( $this->get_woocommerce_totals() as $key => $total ) : ?>
						<tr class="<?php echo $key; ?>">
							<td class="no-borders"></td>
							<th class="description"><?php echo $total['label']; ?></th>
							<td class="price"><span class="totals-price"><?php echo $total['value']; ?></span></td>
						</tr>
						<?php endforeach; ?> 

					</tfoot>
				</table>
			</td>
		</tr>
	</tfoot>
</table>

<?php do_action( 'wpo_wcpdf_after_order_details', $this->type, $this->order ); ?>

<?php if ( $this->get_footer() ): ?>
<div id="footer">
	<?php $this->footer(); ?>
</div><!-- #letter-footer -->
<?php endif; ?>
<?php do_action( 'wpo_wcpdf_after_document', $this->type, $this->order ); ?>
