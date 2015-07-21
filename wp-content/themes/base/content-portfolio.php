<?php
/**
 * The template to display portfolio content
 *
 * @package Base
 * @since 1.0
 */

zilla_post_before(); ?>
<!--BEGIN .post -->
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<?php zilla_post_start(); ?>

	<?php if( is_singular('portfolio') ) { ?>

		<?php
		$portfolio_custom_fields = array();
		$portfolio_custom_fields['display_gallery'] = get_post_meta( $post->ID, '_tzp_display_gallery', true );
		$portfolio_custom_fields['display_audio'] = get_post_meta( $post->ID, '_tzp_display_audio', true );
		$portfolio_custom_fields['display_video'] = get_post_meta( $post->ID, '_tzp_display_video', true );
		?>

		<!--BEGIN .entry-header-->
		<header class="entry-header">
			<?php
			base_post_title();
			base_portfolio_meta( $post->ID );
			/* echo get_the_term_list( $post->ID, 'portfolio-type', '<div class="portfolio-types">', ', ', '</div>'); */
			?>
		<!--END .entry-header-->
		</header>

		<?php base_the_content(); ?>

		<?php base_portfolio_media_feature($post->ID, $portfolio_custom_fields); ?>

		<?php base_portfolio_media($post->ID, $portfolio_custom_fields); ?>

	<?php } else { ?>
		<div class="entry-thumbnail">
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail('portfolio-thumb'); ?>
			</a>
		</div>
	<?php } ?>

<?php zilla_post_end(); ?>
<!--END .post-->
</article>
<?php zilla_post_after(); ?>