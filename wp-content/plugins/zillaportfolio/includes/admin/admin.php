<?php
/**
 * Functions for customizing the WP Admin with regards to Portfolio posts
 *
 * @package  ZillaPortfolio
 * @subpackage Includes/Admin
 * @since  0.1.0
 */

/**
 * Add a sortable columns to the Portfolio Posts page
 * 
 * @param  array $columns The sortable columns
 * @return array          The sortable columns with the new column added
 */
function tzp_add_portfolio_sortable_columns($columns){
  $columns['menu_order'] = 'menu_order';
  return $columns;
}
add_filter('manage_edit-portfolio_sortable_columns','tzp_add_portfolio_sortable_columns');

/**
 * Add a column to the portfolio posts page
 * @param  string $name The name of the column
 * @return void
 */
function tzp_show_custom_portfolio_column_content($name){
  global $post;

  switch ($name) {
    case 'menu_order':
      echo $post->menu_order;
			break;
		default:
      break;
   }
}
add_action('manage_portfolio_posts_custom_column','tzp_show_custom_portfolio_column_content');

/**
 * Set the column title for the menu order column
 * @param  array $column_titles The column titles
 * @return array                The column titles with the new column title added
 */
function tzp_add_portfolio_column_header_text($column_titles) {
  $column_titles['menu_order'] = __('Order', 'zilla-portfolio');
  return $column_titles;
}
add_action('manage_edit-portfolio_columns', 'tzp_add_portfolio_column_header_text');

/**
 * Set the default order on the portfolio posts page to display by
 * menu order and not date
 * 
 * @param obj $query The original query object
 * @return void
 */
function set_portfolio_post_type_admin_order( $query ) {
	if( is_admin() ) {
		$post_type = $query->query['post_type'];

		if( $post_type == 'portfolio' && empty($_GET['orderby']) ) {
			$query->set('orderby', 'menu_order');
			$query->set('order', 'ASC');
		}
	}
}
add_filter( 'pre_get_posts', 'set_portfolio_post_type_admin_order' );

/**
 * Reorder the portfolio admin columns
 * @param  array $columns The admin columns array
 * @return array          The reordered columns array
 */
function tzp_reorder_portfolio_admin_columns($columns) {
  $new = array();
  foreach($columns as $key => $title) {
    if ($key=='date') // Put the menu_order column before the Date column
      $new['menu_order'] = 'Order';
    $new[$key] = $title;
  }
  return $new;
}
add_filter('manage_portfolio_posts_columns', 'tzp_reorder_portfolio_admin_columns');