jQuery( document ).ready( function() {
	jQuery( document ).on( 'click', '.woocommerce-cart-form input[type=submit]', function( evt ) {
		jQuery( '.woocommerce-cart-form' ).trigger( 'submit' );
	});

	jQuery( document ).on( 'change', '.woocommerce-cart-form .qty', function( evt ) {
		jQuery( '.woocommerce-cart-form input[type=submit]' ).trigger( 'click' );
	});
});
