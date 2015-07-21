<?php
/**
 * The Header template for our theme
 *
 * @package Base
 * @since 1.0
 */

$theme_options = get_theme_mod('zilla_theme_options');
?><!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->

<!-- A ThemeZilla design (http://www.themezilla.com) - Proudly powered by WordPress (http://wordpress.org) -->

<!-- BEGIN head -->
<head>

	<!-- Meta Tags -->
	<meta charset="<?php bloginfo('charset'); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php zilla_meta_head(); ?>

	<!-- Title -->
	<title><?php wp_title( '|', true, 'right' ); ?></title>

	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

	<?php wp_head(); ?>
	<?php zilla_head(); ?>

<!-- END head -->
</head>

<!-- BEGIN body -->
<body <?php body_class(); ?>>
<?php zilla_body_start(); ?>

	<!-- BEGIN #container -->
	<div id="container" class="hfeed site">

		<?php zilla_header_before(); ?>
		<!-- BEGIN #masthead .site-header -->
		<header id="masthead" class="site-header" role="banner">
		<?php zilla_header_start(); ?>

			<!-- BEGIN #logo .site-logo-->
			<div id="logo" class="site-logo">
				<?php /*
				If "plain text logo" is set in theme options then use text
				if a logo url has been set in theme options then use that
				if none of the above then use the default logo.png */
				if ($theme_options['general_text_logo']) { ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html( bloginfo( 'name' ) ); ?></a>
					<p class="site-tagline"><?php esc_html( bloginfo( 'description' ) ); ?></p>
				<?php } elseif ($theme_options['general_custom_logo']) { ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( $theme_options['general_custom_logo'] ); ?>" alt="<?php esc_attr( bloginfo( 'name' ) ); ?>"/></a>
				<?php } else { ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/logo.png" alt="<?php esc_attr( bloginfo( 'name' ) ); ?>" width="76" height="22" /></a>
				<?php } ?>
			<!-- END #logo .site-logo-->
			</div>

			<?php zilla_nav_before(); ?>
			<!-- BEGIN #primary-navigation -->
			<nav id="primary-navigation" class="site-navigation" role="navigation">
			<?php if( has_nav_menu( 'primary-menu' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary-menu',
					'menu_id' => 'primary-menu',
					'menu_class' => 'primary-menu zilla-sf-menu',
					'container' => ''
				) );
			} ?>
			<!-- END #primary-navigation -->
			</nav>
			<?php zilla_nav_after(); ?>

		<?php zilla_header_end(); ?>
		<!--END #masthead .site-header-->
		</header>
		<?php zilla_header_after(); ?>

		<!--BEGIN #content .site-content-->
		<div id="content" class="site-content">
		<?php zilla_content_start(); ?>