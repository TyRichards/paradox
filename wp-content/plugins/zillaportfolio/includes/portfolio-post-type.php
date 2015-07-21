<?php
/**
 * Register the custom post type 'portfolio'
 *
 * @package  ZillaPortfolio 
 * @subpackage Includes
 * @since  0.1.0
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 * @return  void
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register 'portfolio' post type
 *
 * @since  0.1.0
 * @return void
 */
function tzp_setup_portfolio_post_type() {

	$archives	= defined( 'TZP_DISABLE_ARCHIVE' ) && TZP_DISABLE_ARCHIVE ? false : true;
	$slug		= defined( 'TZP_SLUG' ) ? TZP_SLUG : 'portfolio';
	$rewrite	= defined( 'TZP_DISABLE_REWRITE' ) && TZP_DISABLE_REWRITE ? false : array('slug' => $slug, 'with_front' => false);
	// Labels for display portfolio projects
	$labels = apply_filters( 'tzp_portfolio_labels', array(
		'name'					=> __( 'Portfolio Posts', 'zilla-portfolio' ),
		'singular_name'			=> __( 'Portfolio Post', 'zilla-portfolio' ),
		'menu_name'				=> __( 'Portfolio Posts', 'zilla-portfolio' ),
		'name_admin_bar' 		=> __( 'Portfolio Posts', 'zilla-portfolio' ),
		'add_new'				=> __( 'Add New', 'zilla-portfolio' ),
		'add_new_item'			=> __( 'Add New Portfolio Post', 'zilla-portfolio' ),
		'edit_item' 			=> __( 'Edit Portfolio Post', 'zilla-portfolio' ),
		'new_item' 				=> __( 'New Portfolio Post', 'zilla-portfolio' ),
		'view_item' 			=> __( 'View Portfolio Post', 'zilla-portfolio' ),
		'search_items' 			=> __( 'Search Portfolio Posts', 'zilla-portfolio' ),
		'not_found' 			=> __( 'No portfolio posts found', 'zilla-portfolio' ),
		'not_found_in_trash'	=> __( 'No portfolio posts found in trash', 'zilla-portfolio' ),
		'all_items' 			=> __( 'Portfolio Posts', 'zilla-portfolio' )
	));

	// Arguments for portfolio projects
	$args = array(
		'labels' 				=> $labels,
		'public' 				=> true,
		'publicly_queryable' 	=> true,
		'show_in_nav_menus' 	=> true,
		'show_in_admin_bar' 	=> true,
		'exclude_from_search'	=> false,
		'show_ui'				=> true,
		'show_in_menu'			=> true,
		'menu_position'			=> 5,
		'menu_icon'				=> 'dashicons-portfolio', //TZP_PLUGIN_URI . 'assets/img/portfolio-icon.png',
		'can_export'			=> true,
		'delete_with_user'		=> false,
		'hierarchical'			=> false,
		'has_archive'			=> $archives,
		'capability_type'		=> 'post',
		'rewrite'				=> $rewrite,
		'supports'				=> apply_filters( 'tzp_portfolio_supports', array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes' ))
	);

	// Register our portfolio post type
	register_post_type( 'portfolio', apply_filters( 'tzp_portfolio_post_type_args', $args ) );
}
add_action( 'init', 'tzp_setup_portfolio_post_type', 1 );