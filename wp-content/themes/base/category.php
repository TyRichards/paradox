<?php
/**
 * The template for displaying Category archives
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
			<h1 class="archive-title">	<?php printf( __('Category Archives: %s', 'zilla'), single_cat_title( '', false ) ); ?></h1>
			<?php if ( category_description() ) { ?>
				<div class="archive-meta"><?php echo category_description(); ?></div>
			<?php } ?>
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