<?php
/**
 * Custom template tags for Base
 *
 * @package Base
 * @since 1.0
 */

if ( ! function_exists('base_post_thumbnail') ) :
/**
 * Display the post thumbnail
 *
 * @return void
 */
function base_post_thumbnail($postid) {
	if ( post_password_required() || ! has_post_thumbnail() ) {
		return;
	}

	$format = get_post_format();
	if( $format == 'link' ) {
		$link_to = get_post_meta($postid, '_zilla_link_url', true);
	} else {
		$link_to = get_the_permalink($postid);
	}
	?>

	<div class="entry-thumbnail">
	<?php if ( is_singular() ) {
		the_post_thumbnail('full');
	} else { ?>
		<a href="<?php echo esc_url($link_to); ?>">
			<?php the_post_thumbnail('blog-thumb'); ?>
		</a>
	<?php } ?>
	</div>

<?php }
endif;

if ( ! function_exists( 'base_get_image_description' ) ) :
/**
 * A simple function to grab the post_content for an image
 *
 * @param  int $p the attachment id
 * @return mixed
 */
function base_get_image_description($p) {
	$image = get_post( $p );
	if( $image->post_content ) {
		return '<p class="portfolio-image-description">' . $image->post_content . '</p>';
	}
	return false;
}
endif;


if ( ! function_exists( 'base_portfolio_media_feature' ) ) :
/**
 * Add a cool featured image/video/audio to the top of a portfolio post
 *
 * @param  int $p the post id
 * @param  array $meta the custom meta fields
 * @return string HTML string
 */
function base_portfolio_media_feature($p, $meta) {
	$output = '<div class="portfolio-media-feature">';

		if( $meta['display_video'] ) {
			$output .= base_print_video_html($p);
		} elseif( $meta['display_audio'] ) {
			$output .= base_print_audio_html($p);
		} elseif( has_post_thumbnail($p) ) {
			$output .= get_the_post_thumbnail($p, 'full');
			$output .= base_get_image_description( get_post_thumbnail_id( $p ) );
		}

	$output .= '</div>';

	echo $output;
}
endif;

if ( ! function_exists( 'base_portfolio_media' ) ) :
/**
 * Return the portfolio media portion of content
 * @param  int $p    the post id
 * @param  array $meta the meta content
 * @return string       HTML for output
 */
function base_portfolio_media($p, $meta) {
	$output = '<div class="portfolio-media-main">';

		if( $meta['display_video'] ) {
			// if showing a video, it gets featured spot
			if( $meta['display_gallery'] && $meta['display_audio'] ) {
				// if showing audio and gallery, add audio to gallery code
				add_filter('base_filter_gallery_content', 'base_add_audio_to_gallery', 10, 2);
				// add gallery to output
				$output .= base_post_gallery($p, 'post-thumbnail', 'stacked');
			} elseif( $meta['display_audio'] ) {
				// showing video and audio; video gets preemo spot, add audio here
				$output .= base_print_audio_html($p);
			} elseif( $meta['display_gallery'] ) {
				// showing video and gallery; video gets preemo spot, add gallery here
				$output .= base_post_gallery($p, 'post-thumbnail', 'stacked');
			}
		} elseif( $meta['display_audio'] ) {
			// if showing audio it gets featured spot
			if( $meta['display_gallery'] ) {
				// showing audio and gallery; audio gets preemo spot, add gallery here
				$output .= base_post_gallery($p, 'full', 'stacked');
			}
		} elseif( $meta['display_gallery'] ) {
			// displaying only a gallery; featured image in preemo spot, add gallery here
			$output .= base_post_gallery($p, 'post-thumbnail', 'stacked');
		}

	$output .= '</div>';

	echo $output;
}
endif;

function base_add_audio_to_gallery($html, $postid) {
	return '<li>' . base_print_audio_html($postid) . '</li>' . $html;
}

if ( !function_exists( 'base_post_gallery' ) ) :
/**
 * Print the HTML for galleries
 *
 * @since 1.0
 *
 * @param int $id ID of the post
 * @param string $imagesize Optional size of image
 * @param string $layout Optional layout format
 * @param int/string $imagesize the image size
 * @return void
 */
function base_post_gallery( $postid, $imagesize = '', $layout = 'slideshow' ) {

	if ( get_post_type($postid) == 'portfolio' ) {
		$image_ids_raw = get_post_meta($postid, '_tzp_gallery_images_ids', true);
	} else {
		$image_ids_raw = get_post_meta($postid, '_zilla_image_ids', true);
	}

	if( $image_ids_raw != '' ) {
		// custom gallery created
		$image_ids = explode(',', $image_ids_raw);
		$orderby = 'post__in';
		$post_parent = null;
	} else {
		// pull all images attached to post
		$image_ids = '';
		$orderby = 'menu_order';
		$post_parent = $postid;
	}

	// get the gallery images
	$args = array(
		'include' => $image_ids,
		'numberposts' => -1,
		'orderby' => $orderby,
		'order' => 'ASC',
		'post_type' => 'attachment',
		'post_parent' => $post_parent,
		'post_mime_type' => 'image',
		'post_status' => 'null'
	);
	$attachments = get_posts($args);

	$output = '';

	if( !empty($attachments) ) {
		$output .= "<div class='zilla-gallery-container'>";
		$output .= "<!--BEGIN #zilla-gallery-$postid -->\n<ul id='zilla-gallery-" . esc_attr($postid) . "' class='zilla-gallery " . esc_attr($layout) . "'>";

		// create a fragment so that we can add a filter to be hooked into
		$fragment = '';
		foreach( $attachments as $attachment ) {
			$src = wp_get_attachment_image_src( $attachment->ID, $imagesize );
			$caption = $attachment->post_excerpt;
			$caption = ($caption && !is_home() && !is_archive()) ? "<div class='wp-caption'>$caption</div>" : '';
			$alt = ( !empty($attachment->post_content) ) ? $attachment->post_content : $attachment->post_title;
            $fragment .= "<li><img height='" . esc_attr($src[2]) . "' width='" . esc_attr($src[1]) . "' src='" . esc_url($src[0]) . "' alt='" . esc_attr($alt) . "' />$caption</li>";
		}
		$fragment = apply_filters('base_filter_gallery_content', $fragment, $postid);
		$output .= $fragment;

		$output .= '</ul>';

		if( $layout != 'stacked' ) {
			$output .= '<div class="zilla-slider-nav">';
				$output .= '<a href="#" id="zilla-slide-prev-'. esc_attr($postid) .'" class="zilla-slide-prev">&larr;' . __('Previous', 'zilla') . '</a>';
				$output .= '<a href="#" id="zilla-slide-next-'. esc_attr($postid) .'" class="zilla-slide-next">' . __('Next', 'zilla') . '&rarr;</a>';
			$output .= '</div>';
		}
		$output .= '</div>';
	}

	return $output;
}
endif;

if ( !function_exists('base_print_video_html') ) :
/**
 * Prints the WP Vidio Shortcode to output the HTML for video
 * @param  int $postid The post ID
 * @return string         The "html" for printing video elements
 */
function base_print_video_html($postid) {
	$output = '';

	$posttype = get_post_type($postid);

	$keys = array(
		'post' => array(
			'embed' => '_zilla_video_embed_code',
			'poster' => '_zilla_video_poster_url',
			'm4v' => '_zilla_video_m4v',
			'ogv' => '_zilla_video_ogv',
			'mp4' => 'a_field'
		),
		'portfolio' => array(
			'embed' => '_tzp_video_embed',
			'poster' => '_tzp_video_poster_url',
			'm4v' => '_tzp_video_file_m4v',
			'ogv' => '_tzp_video_file_ogv',
			'mp4' => '_tzp_video_file_mp4'
		)
	);

	$embed = get_post_meta( $postid, $keys[$posttype]['embed'], true);
	if( $embed ) {
		// Output the embed code if provided
		$output .= html_entity_decode( esc_html( $embed ) );
	} else {
		// Build the video "shortcode"
		$poster = get_post_meta( $postid, $keys[$posttype]['poster'], true );
		$m4v = get_post_meta( $postid, $keys[$posttype]['m4v'], true );
		$ogv = get_post_meta( $postid, $keys[$posttype]['ogv'], true );
		$mp4 = get_post_meta( $postid, $keys[$posttype]['mp4'], true );

		$attr = array('width' => '2000');
		if( $poster ) $attr['poster'] = $poster;
		if( $m4v ) $attr['m4v'] = $m4v;
		if( $ogv ) $attr['ogv'] = $ogv;
		if( $mp4 ) $attr['mp4'] = $mp4;

		$output .= wp_video_shortcode( $attr );
	}

	return $output;
}
endif;


if ( !function_exists('base_print_audio_html') ) :
/**
 * Prints the WP Audio Shortcode to output the HTML for audio
 * @param  int $postid The post ID
 * @return string         The "hmtl" for printing audio elements
 */
function base_print_audio_html($postid) {
	$output = '';

	$posttype = get_post_type($postid);

	$keys = array(
		'post' => array(
			'mp3' => '_zilla_audio_mp3',
			'ogg' => '_zilla_audio_ogg'
		),
		'portfolio' => array(
			'mp3' => '_tzp_audio_file_mp3',
			'ogg' => '_tzp_audio_file_ogg'
		)
	);

	// Print an image if needed
	if( $posttype == 'portfolio' ) {
		$img = get_post_meta( $postid, '_tzp_audio_poster_url', true );
		if( $img ) {
			$output .= '<img src="' . esc_url_raw($img) . '" alt="' . esc_attr( get_the_title($postid) ) . '" />';
		}
	} elseif( has_post_thumbnail($postid) ) {
		$size = 'post-thumbnail';
		if( is_singular() ) {
			$size = 'full';
		}
		$output .= get_the_post_thumbnail($postid, $size);
	}

	// Build the "shortcode"
	$mp3 = get_post_meta( $postid, $keys[$posttype]['mp3'], true );
	$ogg = get_post_meta( $postid, $keys[$posttype]['ogg'], true );
	$attr = array();
	if( $mp3 ) $attr['mp3'] = $mp3;
	if( $ogg) $attr['ogg'] = $ogg;

	$output .= wp_audio_shortcode($attr);

	return $output;
}
endif;


if ( ! function_exists('base_mod_video_shortcode') ) :
/**
 * Videos were not responsive, this filter fixed that
 * @param  string $html   The html output
 * @param  array $atts    The attributes of the shortcode
 * @param  int $post_id 	The post id
 * @param  string $library	JS library
 * @return string         Modified output
 */
function base_mod_video_shortcode($html, $atts, $video, $post_id, $library) {

	if( get_post_format($post_id) == 'video' || get_post_type($post_id) == 'portfolio' ) {
		$html = str_replace('width=', 'style="width:100%;height:100%;" width=', $html);
	}
	return $html;
}
endif;
add_filter('wp_video_shortcode', 'base_mod_video_shortcode', 10, 5);


if ( ! function_exists('base_post_title') ) :
/**
 * Display the post title
 *
 * @return void
 */
function base_post_title() {
	if ( is_single() ) {
		the_title( '<h1 class="entry-title">', '</h1>');
	} else {
		the_title( '<h1 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h1>');
	}
}
endif;


if( !function_exists('base_post_meta_header') ) :
/**
 * Print HTML meta information for current post
 *
 * @return void
 */
function base_post_meta_header() {
?>
	<!--BEGIN .entry-meta-->
	<div class="entry-meta">
	<?php
		printf( ' <span class="published"><a href="%1$s" title="%2$s" rel="bookmark">%3$s</a></span>',
			esc_url( get_permalink() ),
			esc_attr( get_the_time() ),
			esc_html( get_the_time( get_option('date_format') ) )
		);

		if ( is_singular() ) {
			printf( ' <span class="author">%1$s <a href="%2$s" title="%3$s" rel="author">%4$s</a></span>',
				__('by', 'zilla'),
				esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
				esc_attr( sprintf( __('View all posts by %s', 'zilla' ), get_the_author() ) ),
				get_the_author()
			);
		}
	?>
	<!--END .entry-meta -->
	</div>
<?php
}
endif;


if ( ! function_exists('base_the_content') ) :
/**
 * Display the content
 *
 * @return  voide
 */
function base_the_content() { ?>
	<!--BEGIN .entry-content -->
	<div class="entry-content">
		<?php the_content(); ?>
		<?php wp_link_pages(array('before' => '<p><strong>'.__('Pages: ', 'zilla').'</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
	<!--END .entry-content -->
	</div>
<?php }
endif;


if ( ! function_exists('base_post_footer') ) :
/**
 * Display the post footer
 *
 * @return  void
 */
function base_post_footer() { ?>
	<footer class="entry-footer">
		<div class="entry-tags"><?php the_tags('', ' ', ''); ?></div>
	</footer>
<?php }
endif;


if ( ! function_exists('base_page_header' ) ) :
/**
 * Display page header
 *
 * @return void
 */
function base_page_header() { ?>
	<header class="entry-header">
		<h1 class="entry-title"><?php the_title(); ?></h1>
	</header>
<?php }
endif;


if ( ! function_exists( 'base_paging_nav' ) ) :
function base_paging_nav() {
	// Don't print empty markup
	if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
		return;
	} ?>

	<!--BEGIN .navigation .page-navigation-->
	<nav class="navigation page-navigation">
	<?php if ( get_next_posts_link() ) { ?>
		<div class="nav-next"><?php next_posts_link( __('<span>&larr;</span> Older Entries', 'zilla') ); ?></div>
	<?php } ?>
	<?php if ( get_previous_posts_link() ) { ?>
		<div class="nav-previous"><?php previous_posts_link( __('Newer Entries <span>&rarr;</span>', 'zilla') ); ?></div>
	<?php } ?>
	</nav>
	<?php
}
endif;


if ( ! function_exists( 'base_post_nav' ) ) :
/**
 * Display nav to prev/next post as needed
 *
 * @return  void
 */
function base_post_nav() {
	// Do not print nav links on attachment page
	if ( is_attachment() )
		return;

	// Do not print markup if no prev/next posts to link to
	if ( ! get_adjacent_post( false, '', false ) && ! get_adjacent_post( false, '', true ) )
		return;
	?>

	<nav class="navigation single-post-navigation">
		<div class="nav-previous"><?php previous_post_link('%link', '&larr; %title'); ?></div>
		<div class="nav-next"><?php next_post_link('%link', '%title &rarr;'); ?></div>
	</nav>
	<?php
}
endif;


if ( ! function_exists( 'base_portfolio_nav' ) ) :
/**
 * Print the portfolio navigation
 *
 * @return void
 */
function base_portfolio_nav() { ?>
	<nav class="navigation single-post-navigation">
		<div class="nav-previous"><?php base_previous_portfolio_link(); ?></div>
		<div class="nav-next"><?php base_next_portfolio_link(); ?></div>
	</nav>
<?php }
endif;


if ( ! function_exists( 'base_portfolio_meta' ) ) :
/**
 * Build and echo the portfolio meta information
 *
 * @param  int $postid The post id
 * @since  1.0
 * @return void
 */
function base_portfolio_meta($postid) {
	$output = '';

	$url = get_post_meta( $postid, '_tzp_portfolio_url', true);
	$date = get_post_meta( $postid, '_tzp_portfolio_date', true);
	$client = get_post_meta( $postid, '_tzp_portfolio_client', true);
	$role = get_post_meta( $postid, '_zilla_broadcast_portfolio_role', true);

	if( $url || $date || $client || $role ) {
		$output .= '<div class="portfolio-entry-meta"><ul>';
			if( $date ) {
				$output .= sprintf( '<li><strong>%1$s</strong> <span class="portfolio-project-date">%2$s</span></li>', __('Date: ', 'zilla'), esc_html( $date ) );
			}
			if( $client ) {
				$output .= sprintf( '<li><strong>%1$s</strong><span class="portfolio-project-client">%2$s</span></li>', __('Client: ', 'zilla'), esc_html( $client ) );
			}
			if( $role ) {
				$output .= sprintf( '<li><strong>%1$s</strong><span class="portfolio-project-role">%2$s</span></li>', __('Role: ', 'zilla'), esc_html( $role ) );
			}
			if( $url ) {
				$output .= sprintf( '<li><strong>%1$s</strong><a class="portfolio-project-url" href="%2$s">%3$s</a></li>', __('URL: ', 'zilla'), esc_url( $url ), esc_url($url) );
			}

		$output .= '</ul></div>';
	}

	echo $output;
}
endif;


/**
 * Get the adjacent portfolio based upon menu order instead of date
 *
 * @param  boolean $previous Get the previous or next portfolio
 * @since  1.0
 * @return obj            The portfolio post object for linking
 */
function base_get_adjacent_portfolio($previous = true) {
	global $wpdb;

	if ( ( ! $post = get_post() ) )
		return null;

	$current_menu_order = $post->menu_order;

	$adjacent = $previous ? 'previous' : 'next';
	$op = $previous ? '>' : '<';
	$order = $previous ? 'ASC' : 'DESC';

	$where = $wpdb->prepare( "WHERE p.menu_order $op %s AND p.post_type = %s AND p.post_status = 'publish'", $current_menu_order, $post->post_type);

	$sort  = "ORDER BY p.menu_order $order LIMIT 1";

	$query = "SELECT p.ID FROM $wpdb->posts AS p $where $sort";
	$query_key = 'adjacent_post_' . md5( $query );
	$result = wp_cache_get( $query_key, 'counts' );
	if ( false !== $result ) {
		if ( $result )
			$result = get_post( $result );
		return $result;
	}

	$result = $wpdb->get_var( $query );
	if ( null === $result )
		$result = '';

	wp_cache_set( $query_key, $result, 'counts' );

	if ( $result )
		$result = get_post( $result );

	return $result;
}


/**
 * Print the previous portfolio link
 *
 * @uses  base_get_adjacent_portfolio
 * @since  1.0
 * @return  void
 */
function base_previous_portfolio_link() {
	$post = base_get_adjacent_portfolio();

	if ( ! $post ) {
		$link = '';
	} else {
		$link = '<a href="' . esc_url( get_permalink( $post ) ) . '" rel="prev">&larr; ';
		$link .= $post->post_title;
		$link .= '</a>';
	}

	echo $link;
}


/**
 * Print the next portfolio link
 *
 * @uses  base_get_adjacent_portfolio
 * @since  1.0
 * @return void
 */
function base_next_portfolio_link() {
	$post = base_get_adjacent_portfolio(false);

	if ( ! $post ) {
		$link = '';
	} else {
		$link = '<a href="' . esc_url( get_permalink( $post ) ) . '" rel="next">';
		$link .= $post->post_title;
		$link .= ' &rarr;</a>';
	}

	echo $link;
}