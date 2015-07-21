<?php
/**
 * Load scripts
 *
 * @package ZillaPortfolio
 * @subpackage Includes
 * @since 0.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load scripts
 *
 * Enqueue the required scripts
 *
 * @since 0.1.0
 * @return void
 */
function tzp_load_scripts() {
	wp_enqueue_script( 'wp-mediaelement' );
	$load_media_element_style = defined( 'TZP_DISABLE_MEDIAELEMENT_STYLE' ) && TZP_DISABLE_MEDIAELEMENT_STYLE ? false : true;
	if( $load_media_element_style ) {
		wp_enqueue_style( 'wp-mediaelement' );
	}
}
add_action( 'wp_enqueue_scripts', 'tzp_load_scripts' );

/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts
 *
 * @since 0.1.0
 * @return void
 */
function tzp_load_admin_scripts() {
	global $pagenow, $post;

	// Load our CSS code
	wp_enqueue_style( 'tzp_zillaportfolio_style', TZP_PLUGIN_URI . 'assets/css/admin.css');

	if( ! empty($post) && ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) ) {

		// Load our JS code
		wp_enqueue_media();
		wp_enqueue_script( 'tzp_zillaportfolio_script', TZP_PLUGIN_URI . 'assets/js/admin.js', array('jquery', 'backbone'), false, true );
		$params = array(
			'insertText' => __( 'Insert', 'zilla-portfolio' ),
			'createGalleryText' => __('Create Featured Gallery', 'zilla-portfolio'),
			'editGalleryText' => __('Edit Featured Gallery', 'zilla-portfolio'),
			'saveGalleryText' => __('Save Featured Gallery', 'zilla-portfolio'),
			'savingGalleryText' => __('Saving...', 'zilla-portfolio'),
			'post_id' => $post->ID,
	    	'nonce' => wp_create_nonce( 'tzp_ajax' )
		);
		wp_localize_script( 'tzp_zillaportfolio_script', 'zillaportfolio', $params );
	}
}
add_action( 'admin_enqueue_scripts', 'tzp_load_admin_scripts' );