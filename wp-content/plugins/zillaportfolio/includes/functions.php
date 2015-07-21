<?php
/**
 * Functions, filters, and actions for Zilla Portfolio
 *
 * @package  ZillaPortfolio
 * @subpackage Includes
 * @since  0.1.0
 */

/**
 * This will add portfolio media content to the start of the content
 * Override this functionality in a theme by removing the filter.
 * 
 * @param  string $content The content
 * @return string          The updated content
 */
function tzp_add_portfolio_post_media( $content ) {
	global $post;

	if( $post->post_type == 'portfolio' ) {
		$display_gallery = get_post_meta( $post->ID, '_tzp_display_gallery', true );
		$display_audio = get_post_meta( $post->ID, '_tzp_display_audio', true );
		$display_video = get_post_meta( $post->ID, '_tzp_display_video', true );

		if( $display_gallery || $display_audio || $display_video ) {
			$output = '<div class="portfolio-media">';

				if( $display_gallery ) {
					$output .= tzp_portfolio_gallery( $post->ID );
				}

				if( $display_audio ) {
					$poster = get_post_meta( $post->ID, '_tzp_audio_poster_url', true );
					if( $poster ) {
						$output .= sprintf( '<img src="%1$s" alt="" />', esc_url( $poster ) );
					}

					$mp3 = get_post_meta( $post->ID, '_tzp_audio_file_mp3', true );
					$ogg = get_post_meta( $post->ID, '_tzp_audio_file_ogg', true );
					$attr = array(
						'mp3' => $mp3,
						'ogg' => $ogg
					);
					$output .= wp_audio_shortcode($attr);
				}

				if( $display_video ) {
					$embed = get_post_meta( $post->ID, '_tzp_video_embed', true );
					if( $embed ) {
						$output .= html_entity_decode( esc_html( $embed ) );
					} else {
						$poster = get_post_meta( $post->ID, '_tzp_video_poster_url', true );
						$m4v = get_post_meta( $post->ID, '_tzp_video_file_m4v', true );
						$ogv = get_post_meta( $post->ID, '_tzp_video_file_ogv', true );
						$mp4 = get_post_meta( $post->ID, '_tzp_video_file_mp4', true );
						$attr = array(
							'poster' => $poster,
							'm4v' => $m4v,
							'ogv' => $ogv,
							'mp4' => $mp4
						);
						$output .= wp_video_shortcode( $attr );
					}
				}

			$output .= '</div>';

			return $output . $content;
		}

		return $content;
	} else {
		return $content;
	}
}
add_filter('the_content', 'tzp_add_portfolio_post_media');

/**
 * This will add portfolio meta information to the output of the content.
 * Override this functionality in a theme by removing the filter.
 * 
 * @param  string $content The content
 * @return string          The updated content
 */
function tzp_add_portfolio_post_meta( $content ) {
	global $post;
	$output = '';

	if( $post->post_type == 'portfolio' ) {
		$url = get_post_meta( $post->ID, '_tzp_portfolio_url', true);
		$date = get_post_meta( $post->ID, '_tzp_portfolio_date', true);
		$client = get_post_meta( $post->ID, '_tzp_portfolio_client', true);

		if( $url || $date || $client ) {
			$output .= '<div class="portfolio-entry-meta"><ul>';
				if( $date ) {
					$output .= sprintf( '<li><strong>%1$s</strong> <span class="portfolio-project-date">%2$s</span></li>', __('Portfolio Date: ', 'zilla-portfolio'), esc_html( $date ) );
				}
				if( $url ) {
					$output .= sprintf( '<li><strong>%1$s</strong><a class="portfolio-project-url" href="%2$s">%3$s</a></li>', __('Portfolio URL: ', 'zilla-portfolio'), esc_url( $url ), esc_url($url) );
				}
				if( $client ) {
					$output .= sprintf( '<li><strong>%1$s</strong><span class="portfolio-project-client">%2$s</span></li>', __('Portfolio Client: ', 'zilla-portfolio'), esc_html( $client ) );
				}
			$output .= '</ul></div>';
		}

		return $content . $output;
	} else {
		return $content;
	}
}
add_filter('the_content', 'tzp_add_portfolio_post_meta');

/**
 * Print the HTML for galleries
 *
 * @since 0.1.0
 *
 * @param int $postid ID of the post
 * @param string $imagesize Optional size of image
 * @return string The HTML output for galleries
 */
function tzp_portfolio_gallery( $postid ) {

	$image_ids_raw = get_post_meta($postid, '_tzp_gallery_images_ids', true);
	$image_size = 'full';
	$output = '';
	if( $image_ids_raw != '' ) {
		// custom gallery created
		$image_ids = explode(',', $image_ids_raw);
		$orderby = 'post__in';

		// get the gallery images
		$args = array(
			'include' => $image_ids,
			'numberposts' => -1,
			'orderby' => $orderby,
			'order' => 'ASC',
			'post_type' => 'attachment',
			'post_parent' => null,
			'post_mime_type' => 'image',
			'post_status' => 'null'
		);
		$attachments = get_posts($args);

		if( !empty($attachments) ) {
			$output .= "<!--BEGIN #tzp-portfolio-gallery-$postid -->\n";
			$output .= "<ul id='tzp-portfolio-gallery-$postid' class='" . apply_filters( 'tzp_gallery_classes', 'tzp-portfolio-gallery') . "'>";

			foreach( $attachments as $attachment ) {
				$src = wp_get_attachment_image_src( $attachment->ID, apply_filters( 'tzp_set_gallery_image_size', $image_size ) );
				$caption = $attachment->post_excerpt;
				$caption = ($caption) ? "<p class='img-caption'>$caption</p>" : '';
				$alt = ( !empty($attachment->post_content) ) ? $attachment->post_content : $attachment->post_title;
					$output .= "<li><img height='$src[2]' width='$src[1]' src='$src[0]' alt='$alt' />$caption</li>";
			}

			$output .= "</ul>";
		}
	}

	return $output;
}

/**
 * Add basic styles to the head
 * 
 * @return void
 */
function tzp_add_custom_css() { 
	if( defined('TZP_DISABLE_CSS') && TZP_DISABLE_CSS )
		return;
	?>
	<style type="text/css" id="tzp-custom-css">
		.portfolio-media > div {
			margin-bottom: 2em;
		}
		.portfolio-media img {
			height: auto;
			max-width: 100%;
			vertical-align: bottom;
			width: auto;
		}
		.portfolio-media .img-caption {
			padding: 4px 0;
			text-align: center;
		}
		.tzp-portfolio-gallery,
		.tzp-portfolio-gallery li {
			list-style-type: none;
			margin-bottom: 2em;
			margin-left: 0;
		}
	</style>
<?php }
add_action( 'wp_head', 'tzp_add_custom_css' );

/**
 * Set the default sort order for portfolios to be by menu order
 * @param  object $query The original query object
 * @return void
 */
function tzp_portfolios_display_order( $query ) {
	if( !is_admin() && $query->is_main_query() && $query->is_post_type_archive('portfolio') ) {
		$order = defined( 'TZP_PORTFOLIO_ORDER' ) ? TZP_PORTFOLIO_ORDER : 'ASC';
		$orderby = defined( 'TZP_PORTFOLIO_ORDERBY' ) ? TZP_PORTFOLIO_ORDERBY : 'menu_order';
		$query->set('order', $order);
		$query->set('orderby', $orderby);
	}
}
add_action( 'pre_get_posts', 'tzp_portfolios_display_order' );