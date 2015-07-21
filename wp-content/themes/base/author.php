<?php
/**
 * The template for displaying Author archive pages
 *
 * @package Base
 * @since 1.0
 */

get_header(); ?>

	<!--BEGIN #primary .site-main-->
	<div id="primary" class="site-main" role="main">

	<?php if ( have_posts() ) : ?>

		<?php the_post(); ?>

		<header class="archive-header">
			<h1 class="archive-title"><?php printf( __('All posts by %s', 'zilla'), get_the_author() ); ?></h1>
		<!--END .archive-header-->
		</header>

		<?php rewind_posts();

		if (get_the_author_meta('description') ) {
			zilla_author_bio();
		}

		while( have_posts() ) : the_post();

			get_template_part('content', get_post_format() );

		endwhile;

		base_paging_nav();

	else :

		get_template_part('content', 'none');

	endif; ?>

	<!--END #primary .site-main-->
	</div>

<?php get_footer(); ?>