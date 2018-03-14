<?php
global $wp_query;
$options = get_option('br_lgv_liststyle_option');
?>
<div class="berocket_lgv_additional_data">

    <?php do_action( 'lgv_simple_before' ); ?>

    <a class="<?php echo ( ( @ $options['button']['lgv_link_simple']['custom_class'] ) ? $options['button']['lgv_link_simple']['custom_class'] : 'lgv_link lgv_link_simple' ) ?>" href="<?php the_permalink(); ?>">
        <h3><?php the_title(); ?></h3>
    </a>

    <?php do_action( 'lgv_simple_after_product_name' ); ?>

    <div class="<?php echo ( ( @ $options['button']['lgv_description_simple']['custom_class'] ) ? $options['button']['lgv_description_simple']['custom_class'] : 'lgv_description lgv_description_simple' ) ?>">
        <?php woocommerce_template_single_excerpt(); ?>
    </div>

    <?php do_action( 'lgv_simple_after_description' ); ?>

    <div class="<?php echo ( ( @ $options['button']['lgv_meta_simple']['custom_class'] ) ? $options['button']['lgv_meta_simple']['custom_class'] : 'lgv_meta lgv_meta_simple' ) ?>">
        <?php woocommerce_template_single_meta(); ?>
    </div>

    <?php do_action( 'lgv_simple_after_meta' ); ?>

    <div class="garminbygis-additional-data-price-n-button-area">
        <div class="garminbygis-additional-data-action-button">
            <?php do_action( 'garminbygis_additional_data_button_area_before' ); ?>
            <?php do_action( 'flatsome_product_box_after' ); ?>
        </div>
        <div class="garminbygis-additional-data-price <?php echo ( ( @ $options['button']['lgv_price_simple']['custom_class'] ) ? $options['button']['lgv_price_simple']['custom_class'] : 'lgv_price lgv_price_simple' ) ?>">
            <?php woocommerce_template_loop_price(); ?>
        </div>
    </div>
    <?php do_action( 'lgv_simple_after' ); ?>
    <script>
        jQuery(document).ready( function () {
            var br_lgv_stat_cookie = set_get_lgv_cookie ( 0 );

            if ( br_lgv_stat_cookie && 'list' == br_lgv_stat_cookie ) {
                jQuery( '.box-text-products' ).addClass( 'hide-from-screen' );
            }

            br_lgv_style_set();

            jQuery(document).on( 'click', '.berocket_lgv_widget a.berocket_lgv_set', function ( event ) {
                event.preventDefault();

                if ( 'list' === jQuery( this ).data( 'type' ) ) {
                    jQuery( '.box-text-products' ).addClass( 'hide-from-screen' );
                } else {
                    jQuery( '.box-text-products' ).removeClass( 'hide-from-screen' );
                }
            });
        });
    </script>
</div>
