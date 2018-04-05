<?php
function add_searchbox_faq_shortcode( $atts ) {
	ob_start();
	
	global $wp;
	$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );

	// $current_url = home_url( add_query_arg( array(), $wp->request ) );
	?>
	<form method="get" class="searchform" action="<?php echo esc_url( $current_url ); ?>" role="search">
		<div class="flex-row relative">
			<div class="flex-col flex-grow">
				<input type="search" class="search-field mb-0" name="search" value="<?php echo esc_attr( get_search_query() ); ?>" id="search" placeholder="<?php echo __( 'Search', 'woocommerce' ) . '&hellip;'; ?>" />
			</div><!-- .flex-col -->
			<div class="flex-col">
				<button type="submit" class="ux-search-submit submit-button secondary button icon mb-0">
					<?php echo get_flatsome_icon( 'icon-search' ); ?>
				</button>
			</div><!-- .flex-col -->
		</div><!-- .flex-row -->
		<div class="live-search-results text-left z-top"></div>
	</form>
	<?php
	// get_search_form();
	return ob_get_clean();
}
add_shortcode( 'searchbox_faq_shortcode', 'add_searchbox_faq_shortcode' );
