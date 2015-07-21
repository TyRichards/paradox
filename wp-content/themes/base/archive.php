<?php
/**
 * The template for displaying the Archive pages.
 *
 * @package Base
 * @since 1.0
 */

get_header(); ?>

	<!--BEGIN #primary .site-main-->
	<div id="primary" class="site-main" role="main">

	<?php if (have_posts()) : ?>

		<!--BEGIN .archive-header-->
		<header class="archive-header">
			<h1 class="archive-title">
			<?php
				if( is_day() ) {
					printf( __('Archive for: %s', 'zilla'), get_the_date() );
				} elseif( is_month() ) {
					printf( __('Archive for: %s', 'zilla'), get_the_date( _x('F, Y', 'monthly archive date form', 'zilla') ) );
				} elseif( is_year() ) {
					printf( __('Archive for: %s', 'zilla'), get_the_date( _x('Y', 'yearly archive date form', 'zilla') ) );
				} else {
					_e('Archives', 'zilla');
				}
			?>
			</h1>
		<!--END .archive-header-->
		</header>

		<?php while (have_posts()) : the_post();

			get_template_part('content', get_post_format() );

		endwhile;

		base_paging_nav();

	else :

		get_template_part('content', 'none');

	endif; ?>

	<!--END #primary .site-main-->
	</div>

<?php get_footer(); ?>