<?php
/**
 * The sidebar containing the footer widget area.
 *
 * @package Base
 * @since 1.0
 */
?>

<?php if( is_active_sidebar( 'footer-column' ) || is_active_sidebar( 'footer-column-2' ) || is_active_sidebar( 'footer-column-3' ) ) { ?>

	<!--BEGIN .footer-widgets-->
	<div class="footer-widgets clearfix">
	<?php

		if( is_active_sidebar( 'footer-column' ) ) {
			echo '<div class="footer-column">';
				dynamic_sidebar( 'footer-column' );
			echo '</div>';
		}

		if( is_active_sidebar( 'footer-column-2' ) ) {
			echo '<div class="footer-column">';
				dynamic_sidebar( 'footer-column-2' );
			echo '</div>';
		}

		if( is_active_sidebar( 'footer-column-3' ) ) {
			echo '<div class="footer-column">';
				dynamic_sidebar( 'footer-column-3' );
			echo '</div>';
		}
	?>

	<!--END .footer-widgets-->
	</div>
<?php } ?>