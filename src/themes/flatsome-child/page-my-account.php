<?php
/*
Template name: WooCommerce - My Account
This templates add My account to the sidebar.
*/

get_header();

do_action( 'flatsome_before_page' );

wc_get_template( 'myaccount/header.php' ); ?>

<div class="page-wrapper my-account mb">
    <div class="container" role="main">
        <?php if ( is_user_logged_in() ): ?>
            <div class="row vertical-tabs">
                <div class="large-12 col">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php the_content(); ?>
                    <?php endwhile; // end of the loop. ?>
                </div><!-- .large-12 -->
            </div><!-- .row .vertical-tabs -->
        <?php else: ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <?php the_content(); ?>
            <?php endwhile; // end of the loop. ?>
        <?php endif; ?>
    </div><!-- .container -->
</div><!-- .page-wrapper.my-account  -->

<?php
do_action( 'flatsome_after_page' );

get_footer(); ?>
