<?php
/**
 * The template for showing our searchform
 *
 * @package Base
 * @since 1.0
 */
?>

<!--BEGIN #searchform-->
<form method="get" id="searchform" action="<?php echo home_url(); ?>/">
	<fieldset>
		<?php if( is_404 ) { ?>
			<input type="text" name="s" id="s" placeholder="<?php _e('Search', 'zilla'); ?>">
			<input type="submit" value="<?php _e('Search', 'zilla'); ?>">
		<?php } else { ?>
			<input type="text" name="s" id="s" value="<?php _e('To search type and hit enter', 'zilla') ?>" onfocus="if(this.value=='<?php _e('To search type and hit enter', 'zilla') ?>')this.value='';" onblur="if(this.value=='')this.value='<?php _e('To search type and hit enter', 'zilla') ?>';" />
		<?php } ?>
	</fieldset>
<!--END #searchform-->
</form>