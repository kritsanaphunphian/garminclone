<?php
function add_searchbox_faq_shortcode( $atts ) {
	ob_start();
	?>
	<form method="get" class="searchform" action="<?php echo home_url( '/' ); ?>" role="search">
		<div class="flex-row relative">
			<div class="flex-col flex-grow">
				<input type="search" class="search-field mb-0" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" id="s" placeholder="<?php echo __( 'Search', 'woocommerce' ) . '&hellip;'; ?>" />
				<input type="hidden" name="post_type" value="faq">
			</div><!-- .flex-col -->
			<div class="flex-col">
				<button type="submit" class="ux-search-submit submit-button secondary button icon mb-0">
					<?php echo get_flatsome_icon( 'icon-search' ); ?>
				</button>
			</div><!-- .flex-col -->
		</div><!-- .flex-row -->
	</form>
	<?php
	return ob_get_clean();
}
add_shortcode( 'searchbox_faq_shortcode', 'add_searchbox_faq_shortcode' );
