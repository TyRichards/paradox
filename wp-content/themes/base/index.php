<?php
/**
 * The main template file
 *
 * @package Base
 * @since 1.0
 */
get_header(); ?>

	<!--BEGIN #primary .site-main-->
	<div id="primary" class="site-main" role="main">
	<?php if( have_posts() ) :

		while (have_posts()) : the_post();

			get_template_part('content', get_post_format() );

		endwhile;

		base_paging_nav();

	else :

		get_template_part('content', 'none');

	endif; ?>
	<!--END #primary .site-main-->
	</div>

<?php get_footer(); ?>