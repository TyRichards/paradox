<?php
/**
 * The template for displaying default layout pages
 *
 * @package Base
 * @since 1.0
 */
get_header(); ?>

	<!--BEGIN #primary .site-main-->
	<div id="primary" class="site-main" role="main">
	<?php while (have_posts()) : the_post(); ?>

		<?php zilla_page_before(); ?>
		<!--BEGIN .page-->
		<article <?php post_class() ?> id="post-<?php the_ID(); ?>">
		<?php zilla_page_start(); ?>

			<?php
				base_post_thumbnail($post->ID);
				base_page_header();
				base_the_content();
			?>

		<?php zilla_page_end(); ?>
		<!--END .page-->
		</article>
		<?php zilla_page_after(); ?>

		<?php
			zilla_comments_before();
			comments_template('', true);
			zilla_comments_after();

	endwhile; ?>

	<!--END #primary .site-main-->
	</div>

<?php get_footer(); ?>