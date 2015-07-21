<?php
/**
 * The template to display post content for gallery post format
 *
 * @package Base
 * @since 1.0
 */

zilla_post_before(); ?>
<!--BEGIN .post -->
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<?php zilla_post_start(); ?>

	<?php if( is_singular('post') ) {
		echo base_post_gallery($post->ID, 'full');
	} else {
		echo base_post_gallery($post->ID, 'blog-thumb');
	} ?>

	<!--BEGIN .entry-header-->
	<header class="entry-header">
		<?php
		if ( is_singular() ) {
			base_post_title();
			base_post_meta_header();
		} else {
			base_post_meta_header();
			base_post_title();
		}
		?>
	<!--END .entry-header-->
	</header>

	<?php if( is_singular() ) {

		base_the_content();
		base_post_footer();

	} else { ?>

		<!--BEGIN .entry-summary -->
		<div class="entry-summary">
			<?php the_excerpt(); ?>
		<!--END .entry-summary -->
		</div>

	<?php } ?>

<?php zilla_post_end(); ?>
<!--END .post-->
</article>
<?php zilla_post_after(); ?>