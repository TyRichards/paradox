<?php
/**
 * The template to display search results
 *
 * @package Base
 * @since 1.0
 */
?>

<li>
	<?php zilla_post_before(); ?>
	<!--BEGIN .post -->
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php zilla_post_start(); ?>

		<!--BEGIN .entry-header-->
		<header class="entry-header">
			<?php base_post_title(); ?>
		<!--END .entry-header-->
		</header>

		<!--BEGIN .entry-summary -->
		<div class="entry-summary">
			<?php the_excerpt(); ?>
		<!--END .entry-summary -->
		</div>

	<?php zilla_post_end(); ?>
	<!--END .post-->
	</article>
	<?php zilla_post_after(); ?>
</li>