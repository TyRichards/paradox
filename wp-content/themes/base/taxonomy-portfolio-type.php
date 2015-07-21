<?php
/**
 * Template to show portfolios from a portfolio type
 *
 * @package  Base
 * @since  1.0
 */

get_header(); ?>

	<!--BEGIN #primary .site-main-->
	<div id="primary" class="site-main" role="main">

	<?php if( have_posts() ) : ?>

		<header class="archive-header">
			<h1 class="archive-title"><?php single_term_title(); ?></h1>
			<?php echo term_description(); ?>
		</header>

		<div id="portfolio-feed" class="portfolio-feed clearfix">

			<?php while( have_posts() ) : the_post() ; ?>

				<?php get_template_part( 'content', 'portfolio' ); ?>

			<?php endwhile;

				base_paging_nav();

			?>

		</div>

	<?php else :

		get_template_part( 'content', 'none' );

	endif; ?>

	<!--END #primary .site-main-->
	</div>

<?php get_footer(); ?>