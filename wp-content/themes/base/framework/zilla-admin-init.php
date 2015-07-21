<?php

/**
 * Set some basic info
 */
function zilla_admin_init()
{
    
    // Get theme and framework info
    $data = get_option( 'zilla_framework_options' );
	
    if( is_child_theme() ) {
        $temp_obj = wp_get_theme();
        $theme_obj = wp_get_theme( $temp_obj->get('Template') );
    } else {
        $theme_obj = wp_get_theme();    
    }
    
    $data['theme_name'] = $theme_obj->get('Name');
    $data['theme_version'] = $theme_obj->get('Version');

	$data['framework_version'] = ZILLA_FRAMEWORK_VERSION;

	update_option( 'zilla_framework_options', $data );
    
}
add_action( 'init', 'zilla_admin_init', 2 );

/**
 * Load admin CSS
 */
function zilla_admin_styles() {
	wp_enqueue_style('zilla_admin_css', ZILLA_URL .'/styles/zilla-admin.css');
    wp_enqueue_style('wp-color-picker');
}
add_action('admin_print_styles', 'zilla_admin_styles');
 
/**
 * Load admin JS
 */
function zilla_admin_scripts() {
    wp_enqueue_script('zilla-framework-admin', ZILLA_URL .'/scripts/zilla-admin.js', array('jquery','wp-color-picker'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'zilla_admin_scripts');

/**
 * Add the support forums link to the menu
 */
function zilla_menu(){
	// Support link/page
	add_theme_page( 'Theme Zilla Support Forums', __( 'Support Forums', 'zilla'), 'edit_themes', 'zillaframework-support', 'zilla_support_page');
}
add_action('admin_menu', 'zilla_menu');


/**
 * Output CSS based on the use of the zilla_custom_styles filter
 *
 * @since 1.1
 * @return void
 */
function zilla_output_custom_styles() {
	$styles = '';
	$output = apply_filters('zilla_custom_styles', $styles);

	if( empty($output) ) return false;

	echo "<style type='text/css' id='zilla-custom-styles'>\n" . $output . "</style>";
}
add_action( 'zilla_head', 'zilla_output_custom_styles' );

?>