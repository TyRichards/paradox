<?php
/**
 * The template to display featured portfolio content
 *
 * @package Base
 * @since 1.0
 */

zilla_post_before(); ?>
<!--BEGIN .post -->
<article id="post-<?php the_ID(); ?>" <?php post_class('featured-portfolio'); ?>>
<?php zilla_post_start(); ?>

	<div class="entry-thumbnail">
		<a href="<?php the_permalink(); ?>">
			<?php the_post_thumbnail('full'); ?>
		</a>
	</div>

<?php zilla_post_end(); ?>
<!--END .post-->
</article>
<?php zilla_post_after(); ?>