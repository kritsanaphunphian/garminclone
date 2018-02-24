<!DOCTYPE html>
<!--[if IE 9 ]> <html <?php language_attributes(); ?> class="ie9 <?php flatsome_html_classes(); ?>"> <![endif]-->
<!--[if IE 8 ]> <html <?php language_attributes(); ?> class="ie8 <?php flatsome_html_classes(); ?>"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html <?php language_attributes(); ?> class="<?php flatsome_html_classes(); ?>"> <!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	<?php wp_head(); ?>

</head>

<body <?php body_class(); // Body classes is added from inc/helpers-frontend.php ?>>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'flatsome' ); ?></a>

<div id="wrapper">
<div class="ajax-loader">
<div class="sk-fading-circle">
<div class="sk-circle1 sk-circle"></div>
<div class="sk-circle2 sk-circle"></div>
<div class="sk-circle3 sk-circle"></div>
<div class="sk-circle4 sk-circle"></div>
<div class="sk-circle5 sk-circle"></div>
<div class="sk-circle6 sk-circle"></div>
<div class="sk-circle7 sk-circle"></div>
<div class="sk-circle8 sk-circle"></div>
<div class="sk-circle9 sk-circle"></div>
<div class="sk-circle10 sk-circle"></div>
<div class="sk-circle11 sk-circle"></div>
<div class="sk-circle12 sk-circle"></div>
</div>
</div>
<?php do_action('flatsome_before_header'); ?>

<header id="header" class="header <?php flatsome_header_classes();  ?>">
   <div class="header-wrapper">
	<?php
		get_template_part('template-parts/header/header', 'wrapper');
	?>
   </div><!-- header-wrapper-->
</header>

<?php do_action('flatsome_after_header'); ?>

<main id="main" class="<?php flatsome_main_classes();  ?>">
