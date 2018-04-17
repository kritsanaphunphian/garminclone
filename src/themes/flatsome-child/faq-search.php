<?php get_header(); ?>

<div id="content" class="blog-wrapper blog-archive page-wrapper">
	<div class="row row-main">
		<div class="large-12 col">
			<div class="col-inner">
				<div class="container section-title-container">
					<h3 class="section-title section-title-normal">
						<span style="font-size:150%;">
							<?php _e( 'FAQ', 'garminbygis' ); ?>
						</span>
						<span style="border-bottom: 0; text-transform: none; margin-right: 0;">
							<?php
							printf( __( 'Search Results for: %s', 'flatsome' ), get_search_query() );
							?>
						</span>
					</h3>
				</div>

				<div class="row faqsearch-searchbox">
					<div class="large-12 col">
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
						<div class="text-center">
							<a href="/<?php echo pll_current_language() != pll_default_language() ? pll_current_language() . '/' : ''; ?>support/faq/"><?php _e( 'Back to FAQ page', 'garminbygis' ); ?></a>
						</div>
					</div>
				</div>

				<div class="row align-center">
					<div class="large-10 col">
						<?php if ( have_posts() ) : ?>
							<div id="post-list">

								<?php /* Start the Loop */ ?>
								<?php while ( have_posts() ) : the_post(); ?>
									<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
										<div class="article-inner <?php flatsome_blog_article_classes(); ?>">
											<header class="entry-header">
												<div class="entry-header-text entry-header-text-top text-<?php echo flatsome_option('blog_posts_title_align');?>">
													<?php get_template_part( 'template-parts/posts/partials/entry', 'title');  ?>
												</div><!-- .entry-header -->
											</header><!-- post-header -->

											<?php get_template_part('template-parts/posts/content', 'default' ); ?>
											<?php get_template_part('template-parts/posts/partials/entry-footer', 'default' ); ?>
										</div><!-- .article-inner -->
									</article><!-- #-<?php the_ID(); ?> -->

								<?php endwhile; ?>

								<?php flatsome_posts_pagination(); ?>

							</div>

						<?php else : ?>

							<?php get_template_part( 'template-parts/posts/content','none'); ?>

						<?php endif; ?>

					</div> <!-- .large-9 -->

				</div><!-- .row -->

				<?php do_action('flatsome_after_blog'); ?>
			</div>
		</div>
	</div>
</div><!-- .page-wrapper .blog-wrapper -->

<?php get_footer(); ?>