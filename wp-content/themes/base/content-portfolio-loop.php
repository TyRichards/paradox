<?php
/**
 * The template for looping through portfolios and displaying their content.
 *
 * @package  Base
 * @since  1.0
 */

$exclude = array();

// featured portfolio
$args = array(
	'post_type' => 'portfolio',
	'meta_query' => array(
		array(
			'key' => '_zilla_base_featured_portfolio',
			'value' => 1,
			'compare' => '='
		)
	),
	'orderby' => 'menu_order',
	'order' => 'ASC',
	'posts_per_page' => 1,
	'update_post_meta_cache' => false
);

$query = new WP_Query($args);

if( $query->have_posts() ) :

	echo '<div id="featured-portfolio" class="featured-portfolio">';

	while( $query->have_posts() ) : $query->the_post();

		$exclude[] = $post->ID;

		get_template_part('content', 'portfolio-featured');

	endwhile;

	echo '</div>';

endif;

wp_reset_query();

// main portfolio
$args = array(
	'post_type' => 'portfolio',
	'orderby' => 'menu_order',
	'order' => 'ASC',
	'posts_per_page' => -1,
	'update_post_meta_cache' => false,
	'post__not_in' => $exclude
);
$query = new WP_Query($args);

if( $query->have_posts() ) :

	echo '<div id="portfolio-feed" class="portfolio-feed clearfix">';

	while( $query->have_posts() ) : $query->the_post();

		get_template_part('content', 'portfolio');

	endwhile;

	echo '</div>';

endif;

wp_reset_query();