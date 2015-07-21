<?php
/**
 * The template for showing the single portfolio view
 *
 * @package Base
 * @since 1.0
 */

get_header(); ?>

	<!--BEGIN #primary .site-main-->
	<div id="primary" class="site-main" role="main">

	<?php
		// The loop
		while (have_posts()) : the_post();

			get_template_part('content', 'portfolio' );

			base_portfolio_nav();
		endwhile;
	?>

	<!--END #primary .site-main-->
	</div>

<?php get_footer(); ?>