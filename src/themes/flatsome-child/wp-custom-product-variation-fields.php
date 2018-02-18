<?php
//Display Fields
add_action( 'woocommerce_product_after_variable_attributes', 'variable_fields', 10, 3 );
//JS to add fields for new variations
add_action( 'woocommerce_product_after_variable_attributes_js', 'variable_fields_js' );
//Save variation fields
add_action( 'woocommerce_process_product_meta_variable', 'save_variable_fields', 10, 1 );
add_action( 'woocommerce_save_product_variation', 'save_variable_fields', 10, 1 );

/**
 * Create new fields for variations
 *
*/
function variable_fields( $loop, $variation_data, $variation ) {
?>
	<tr>
		<td>
			<?php
			// Serial Number Field
			woocommerce_wp_text_input( 
				array( 
					'id'          => '_product_old_price_variation['.$loop.']', 
					'label'       => __( 'Old Price', 'woocommerce' ), 
					'type' 		  => 'number',
					'desc_tip'    => 'true',
					'description' => __( 'Enter the custom value here.', 'woocommerce' ),
					'value'       => get_post_meta( $variation->ID, '_product_old_price_variation', true )
				)
			);
			?>
		</td>
    </tr>
    <tr>
		<td>
			<?php
			// Part Number Field
			woocommerce_wp_text_input( 
				array( 
					'id'          => '_partnumber_text_field_variation['.$loop.']', 
					'label'       => __( 'Part Number', 'woocommerce' ), 
                    'placeholder' => 'e.g. 088-23919392-123',
					'desc_tip'    => 'true',
					'description' => __( 'Enter the custom value here.', 'woocommerce' ),
					'value'       => get_post_meta( $variation->ID, '_partnumber_text_field_variation', true )
				)
			);
			?>
		</td>
	</tr>
	<tr>
		<td>
			<?php
			// Hidden field
			woocommerce_wp_hidden_input(
			array( 
				'id'    => '_hidden_field[ + loop + ]', 
				'value' => 'hidden_value'
				)
			);
			?>
		</td>
	</tr>
<?php
}

/**
 * Save new fields for variations
 *
*/
function save_variable_fields( $post_id ) {
	if (isset( $_POST['variable_sku'] ) ) :

		$variable_sku          = $_POST['variable_sku'];
		$variable_post_id      = $_POST['variable_post_id'];
		
		//SerialNumber Text Field
		$_product_old_price_variation = $_POST['_product_old_price_variation'];
		for ( $i = 0; $i < sizeof( $variable_sku ); $i++ ) :
			$variation_id = (int) $variable_post_id[$i];
			if ( isset( $_product_old_price_variation[$i] ) ) {
				update_post_meta( $variation_id, '_product_old_price_variation', stripslashes( $_product_old_price_variation[$i] ) );
			}
        endfor;
        
        //PartNumber Text Field
		$_partnumber_text_field_variation = $_POST['_partnumber_text_field_variation'];
		for ( $i = 0; $i < sizeof( $variable_sku ); $i++ ) :
			$variation_id = (int) $variable_post_id[$i];
			if ( isset( $_partnumber_text_field_variation[$i] ) ) {
				update_post_meta( $variation_id, '_partnumber_text_field_variation', stripslashes( $_partnumber_text_field_variation[$i] ) );
			}
		endfor;
		
		// Hidden field
		$_hidden_field = $_POST['_hidden_field'];
		for ( $i = 0; $i < sizeof( $variable_sku ); $i++ ) :
			$variation_id = (int) $variable_post_id[$i];
			if ( isset( $_hidden_field[$i] ) ) {
				update_post_meta( $variation_id, '_hidden_field', stripslashes( $_hidden_field[$i] ) );
			}
		endfor;
		
	endif;
}
