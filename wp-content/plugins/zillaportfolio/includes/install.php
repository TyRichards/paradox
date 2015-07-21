<?php
/**
 * Install Function
 *
 * @package  ZillaPortfolio
 * @subpackage Includes
 * @since  0.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Install
 *
 * Runs on the plugin install by setting up the post types, custom
 * taxnomies, and flushing rewrite rules.
 *
 * @since  0.1.0
 * @todo Grab the old meta fields for existing themes and port them
 *       to the new meta fields. Delete old meta fields. Neat and tidy.
 * @return  void
 */
function tzp_install() {
	// Setup the Portfolio Custom Post Type
	tzp_setup_portfolio_post_type();

	// Setup the Portfolio-Type Custom Taxonomy
	tzp_setup_portfolio_type_taxonomy();

	// Clear the permalinks
	flush_rewrite_rules();
}
register_activation_hook( TZP_PLUGIN_FILE, 'tzp_install' );