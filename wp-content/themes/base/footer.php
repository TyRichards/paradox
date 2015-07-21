<?php
/**
 * The template for showing our footer
 *
 * @package Base
 * @since 1.0
 */
?>

		<?php zilla_content_end(); ?>
		<!-- END #content .site-content-->
		</div>

		<?php if( ! is_singular('portfolio') ) {
			get_sidebar('footer');
		} ?>

		<?php zilla_footer_before(); ?>
		<!-- BEGIN #footer -->
		<footer id="footer" class="site-footer" role="contentinfo">
		<?php zilla_footer_start(); ?>

			<p class="copyright">&copy; <?php echo date( 'Y' ); ?> <a href="<?php echo home_url(); ?>"><?php bloginfo( 'name' ); ?></a>.  <?php _e('Powered by', 'zilla') ?> <a href="http://wordpress.org/">WordPress</a>.</p>

			<p class="credit"><a href="http://www.themezilla.com/themes/base">Base Theme</a> by <a href="http://www.themezilla.com/">ThemeZilla</a></p>

		<?php zilla_footer_end(); ?>
		<!-- END #footer -->
		</footer>
		<?php zilla_footer_after(); ?>

	<!-- END #container .hfeed .site -->
	</div>

	<!-- Theme Hook -->
	<?php wp_footer(); ?>
	<?php zilla_body_end(); ?>

	<!-- <?php echo 'Ran '. $wpdb->num_queries .' queries '. timer_stop(0, 2) .' seconds'; ?> -->
<!--END body-->
</body>
<!--END html-->
</html>