<?php
/**
 * The template for displaying Tag archives
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
			<h1 class="archive-title">	<?php printf( __('Tag Archives: %s', 'zilla'), single_tag_title( '', false ) ); ?></h1>
			<?php if ( tag_description() ) { ?>
				<div class="archive-meta"><?php echo tag_description(); ?></div>
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