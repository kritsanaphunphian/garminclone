<?php
/**
 * Hook Faltsome, myaccount/account-links.php template.
 *
 * @see wp-content/themes/flatsome/templaes/myaccount/account-links.php
 */

if ( has_nav_menu( 'my_account' ) ) {
  echo wp_nav_menu( array(
    'theme_location' => 'my_account',
    'container'      => false,
    'items_wrap'     => '%3$s',
    'depth'          => 0,
    'walker'         => new FlatsomeNavSidebar
  ) );
} elseif ( ! get_theme_mod( 'wc_account_links', 1 ) ) { 
  echo '<li>Define your My Account dropdown menu in <b>Appearance > Menus</b> or enable default WooCommerce Account Endpoints.</li>';
}
?>

<?php if ( function_exists( 'wc_get_account_menu_items' ) && get_theme_mod( 'wc_account_links', 1 ) ) : ?>
  <?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
    <li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
      <?php if ( 'dashboard' == $endpoint ) : ?>
        <a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
        <!-- empty -->
      <?php else : ?>
        <a href="<?php echo esc_url( wc_get_endpoint_url( $endpoint, '', wc_get_page_permalink( 'myaccount' ) ) ); ?>"><?php echo esc_html( $label ); ?></a>
      <?php endif; ?>
    </li>
  <?php endforeach; ?>

  <?php do_action( 'flatsome_account_links' ); ?>

  <li class="woocommerce-MyAccount-navigation-link woocommerce-MyAccount-navigation-link--customer-logout">
    <a href="<?php echo wp_logout_url( '/my-account/' ) ?>"><?php _e( 'Log out', 'woocommerce' ); ?></a>
  </li>
<?php endif; ?>