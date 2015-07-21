<?php
/**
 * Register the custom post taxonomy 'portfolio-type'
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
 * Register the custom taxonomy 'portfolio-type'
 *
 * @since 0.1.0
 * @link http://codex.wordpress.org/Function_Reference/register_taxonomy
 * @return  void
 */
function tzp_setup_portfolio_type_taxonomy() {

	$slug = defined( 'TZP_TAX_SLUG' ) ? TZP_TAX_SLUG : 'portfolio-type';

	// Label for 'portfolio-type' taxonomy
	$labels = apply_filters( 'tzp_portfolio_type_labels', array(
		'name'							=> __( 'Portfolio Type', 'zilla-portfolio' ),
		'singular_name'					=> __( 'Portfolio Type', 'zilla-portfolio' ),
		'menu_name'						=> __( 'Portfolio Types', 'zilla-portfolio' ),
		'edit_item'						=> __( 'Edit Portfolio Type', 'zilla-portfolio' ),
		'update_item'					=> __( 'Update Portfolio Type', 'zilla-portfolio' ),
		'add_new_item'					=> __( 'Add New Portfolio Type', 'zilla-portfolio' ),
		'new_item_name'					=> __( 'New Portfolio Type Name', 'zilla-portfolio' ),
		'parent_item'					=> __( 'Parent Portfolio Type', 'zilla-portfolio' ),
		'parent_item_colon'				=> __( 'Parent Portfolio Type:', 'zilla-portfolio' ),
		'all_items'						=> __( 'All Portfolio Types', 'zilla-portfolio' ),
		'search_items'					=> __( 'Search Portfolio Types', 'zilla-portfolio' ),
		'popular_items'					=> __( 'Popular Portfolio Types', 'zilla-portfolio' ),
		'separate_items_with_commas'	=> __( 'Separate portfolio types with commas', 'zilla-portfolio' ),
		'add_or_remove_items'			=> __( 'Add or remove portfolio types', 'zilla-portfolio' ),
		'choose_from_most_used'			=> __( 'Choose from the most used portfolio types', 'zilla-portfolio' ),
		'not_found'						=> __( 'No portfolio types found', 'zilla' )
	));

	// Arguments for 'portfolio-type' taxonomy
	$args = apply_filters( 'tzp_portfolio_type_args', array(
		'labels'			=> $labels,
		'public'			=> true,
		'show_ui' 			=> true,
		'show_in_nav_menus'	=> true,
		'show_admin_column'	=> true,
		'show_tagcloud'		=> false,
		'hierarchical'		=> true,
		'query_var'			=> true,
		'rewrite'			=> array( 'slug' => $slug, 'with_front' => false, 'hierarchical' => true )
	));

	register_taxonomy( 'portfolio-type', array( 'portfolio' ), $args );
}
add_action( 'init', 'tzp_setup_portfolio_type_taxonomy' );