<?php
/**
 * JCAA Product attributes
 *
 * Used by list_attributes() in the products class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$has_row    = false;
$alt        = 1;
$attributes = $product->get_attributes();
$grouped_attributes = array();

$dimension_group = JCAA()->get_settings('product_dimension_group');
if(empty($dimension_group)){
	$dimension_group = false;
}

$dimension_output = false;

/** @var WC_Product $product */

ob_start();

?>
<table class="shop_attributes">

	<?php if ( $display_dimensions && $product->has_weight() && !$dimension_group ) : $dimension_output = true; ?>
		<tr>
			<th width="60%"><?php _e( 'Weight', 'woocommerce' ) ?></th>
			<td class="product_weight"><?php echo esc_html( wc_format_weight( $product->get_weight() ) ); ?></td>
		</tr>
	<?php endif; ?>

	<?php if ( $display_dimensions && $product->has_dimensions() && !$dimension_group ) : $dimension_output = true; ?>
		<tr>
			<th width="60%"><?php _e( 'Dimensions', 'woocommerce' ) ?></th>
			<td class="product_dimensions"><?php echo esc_html( wc_format_dimensions( $product->get_dimensions( false ) ) ); ?></td>
		</tr>
	<?php endif; ?>

	<?php foreach ( $attributes as $attribute ) :
		if ( empty( $attribute['is_visible'] ) || ( $attribute['is_taxonomy'] && ! taxonomy_exists( $attribute['name'] ) ) ) {
			continue;
		} else {
			$has_row = true;
		}

		// hide grouped attributes
		/** @var JCAA_Product_Attribute $attribute */
		if( method_exists($attribute, 'has_grouped_terms') && $attribute->has_grouped_terms() ){
			$grouped_attributes[] = $attribute;
			continue;
		}
		?>
		<tr class="<?php if ( ( $alt = $alt * -1 ) == 1 ) echo 'alt'; ?>">
			<th width="60%"><?php echo wc_attribute_label( $attribute['name'] ); ?></th>
			<td><?php
				if ( $attribute['is_taxonomy'] ) {

					$values = wc_get_product_terms( $product->get_id(), $attribute['name'], array( 'fields' => 'names' ) );
					echo apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $attribute, $values );

				} else {

					// Convert pipes to commas and display values
					$values = array_map( 'trim', explode( WC_DELIMITER, $attribute['value'] ) );
					echo apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $attribute, $values );

				}
			?></td>
		</tr>
	<?php endforeach; ?>

</table>

<?php
/** @var JCAA_Product_Attribute $grouped */
foreach($grouped_attributes as $grouped):


	if(!method_exists($grouped, 'has_grouped_terms') || !$grouped->has_grouped_terms()){
		continue;
	}

	$alt = 1;
	?>
<h2><?php echo wc_attribute_label( $grouped['name'] ); ?></h2>
<table class="shop_attributes">

	<?php if ( $display_dimensions && $product->has_weight() && $dimension_group == $grouped['name'] ) : $dimension_output = true; ?>
		<tr>
			<th width="60%"><?php _e( 'Weight', 'woocommerce' ) ?></th>
			<td class="product_weight"><?php echo esc_html( wc_format_weight( $product->get_weight() ) ); ?></td>
		</tr>
	<?php endif; ?>

	<?php if ( $display_dimensions && $product->has_dimensions() && $dimension_group == $grouped['name'] ) : $dimension_output = true; ?>
		<tr>
			<th width="60%"><?php _e( 'Dimensions', 'woocommerce' ) ?></th>
			<td class="product_dimensions"><?php echo esc_html( wc_format_dimensions( $product->get_dimensions( false ) ) ); ?></td>
		</tr>
	<?php endif; ?>

	<?php foreach($grouped->get_grouped_terms() as $attribute): ?>

		<tr class="<?php if ( ( $alt = $alt * -1 ) == 1 ) echo 'alt'; ?>">
			<th width="60%"><?php echo wc_attribute_label( $attribute['name'] ); ?></th>
			<td><?php

				$values = array();

				if ( $attribute->is_taxonomy() ) {

					$attribute_taxonomy = $attribute->get_taxonomy_object();
					$attribute_values = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'all' ) );

					foreach ( $attribute_values as $attribute_value ) {
						$value_name = esc_html( $attribute_value->name );

						if ( $attribute_taxonomy->attribute_public ) {
							$values[] = '<a href="' . esc_url( get_term_link( $attribute_value->term_id, $attribute->get_name() ) ) . '" rel="tag">' . $value_name . '</a>';
						} else {
							$values[] = $value_name;
						}
					}

				} else {

					$values = $attribute->get_options();

					foreach ( $values as &$value ) {
						$value = make_clickable( esc_html( $value ) );
					}
				}

				if ( is_array( $values ) && 1 < count( $values ) ) {
					echo '<ul style="margin-bottom: 0;">';
						foreach ( $values as $attribute_value ) {
							echo '<li>' . wptexturize( $attribute_value ) . '</li>';
						}
					echo '</ul>';
				} else {
					echo apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $attribute, $values );
				}
			?></td>
		</tr>

	<?php endforeach; ?>

</table>
<?php endforeach; ?>

<?php if(!$dimension_output && $display_dimensions): ?>

	<h2><?php echo wc_attribute_label( $dimension_group ); ?></h2>
	<table class="shop_attributes">

	<?php if ( $display_dimensions && $product->has_weight()) : ?>
		<tr>
			<th width="60%"><?php _e( 'Weight', 'woocommerce' ) ?></th>
			<td class="product_weight"><?php echo esc_html( wc_format_weight( $product->get_weight() ) ); ?></td>
		</tr>
	<?php endif; ?>

	<?php if ( $display_dimensions && $product->has_dimensions()) : ?>
		<tr>
			<th width="60%"><?php _e( 'Dimensions', 'woocommerce' ) ?></th>
			<td class="product_dimensions"><?php echo esc_html( wc_format_dimensions( $product->get_dimensions( false ) ) ); ?></td>
		</tr>
	<?php endif; ?>
	</table>

<?php endif; ?>

<?php
if ( $has_row ) {
	echo ob_get_clean();
} else {
	ob_end_clean();
}
