<?php
/**
 * The template for displaying search results
 *
 * @package Base
 * @since 1.0
 */
get_header(); ?>

	<!--BEGIN #primary .site-main-->
	<div id="primary" class="site-main" role="main">
	<?php if (have_posts()) : ?>

		<header class="page-header">
			<h1 class="page-title"><?php _e('Search Results for', 'zilla') ?> &#8220;<?php the_search_query(); ?>&#8221;</h1>
		</header>

		<ol class="returned-search-results">
			<?php while (have_posts()) : the_post();

				get_template_part('content', 'search');

			endwhile; ?>
		</ol>

		<?php base_paging_nav();

	else :

		get_template_part('content', 'none');

	endif; ?>
	<!--END #primary .site-main-->
	</div>

<?php get_footer(); ?>