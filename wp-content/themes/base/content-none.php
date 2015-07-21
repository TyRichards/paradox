<?php
/**
 * The template for unfound content
 *
 * @package Base
 * @since 1.0
 */
?>

<header class="page-header">
	<h1 class="page-title">
		<?php if( is_404() ) { _e('Error 404: ', 'zilla'); } ?><?php _e('Nothing Found', 'zilla'); ?>
	</h1>
</header>

<!--BEGIN #post-0-->
<article id="post-0" class="page">
	<!--BEGIN .entry-content-->
	<div class="entry-content">

		<?php if ( is_home() && current_user_can('publish_posts') ) { ?>

			<p><?php printf( __('Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'zilla'), admin_url('post-new.php') ); ?></p>

		<?php } elseif ( is_search() ) { ?>

			<p><?php _e('Sorry, but nothing matched your search terms. Please try again with different keywords.', 'zilla'); ?></p>
			<?php get_search_form(); ?>

		<?php } else { ?>

			<p><?php _e('It seems we cannot find what you are looking for. Perhaps searching can help.', 'zilla'); ?></p>
			<?php get_search_form(); ?>

		<?php } ?>

	<!--END .entry-content-->
	</div>

<!--END #post-0-->
</article>