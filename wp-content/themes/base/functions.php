<?php
/**
 * Base functions
 *
 * Sets up the theme and provides some helper functions.
 *
 * When using a child theme, you can override certain functions by
 * defining the function within the child theme's functions.php file.
 * It is better to override theme functions via a child theme than to
 * edit directly in this functions.php file. When things go wrong in this
 * file, they go really wrong
 *
 * @package Base
 * @since 1.0
 */


/**
 * Set Max Content Width
 *
 * @since 1.0
 */
if ( ! isset( $content_width ) )
	$content_width = 960;


if ( !function_exists( 'zilla_theme_setup' ) ) :
/**
 * Sets up theme defaults and registers various features supported
 * by Base
 *
 * @uses load_theme_textdoman() For translation support
 * @uses register_nav_menu() To add support for navigation menu
 * @uses add_theme_support() To add support for post-thumbnails and post-formats
 * @uses set_post_thumbnail_size() To set a custom post thumbnail size
 * @uses add_image_size() To add additional image sizes
 *
 * @since 1.0
 *
 * @return void
 */
function zilla_theme_setup() {

	/* Load translation domain --------------------------------------------------*/
	load_theme_textdomain( 'zilla', get_template_directory() . '/languages' );

	/* Register WP 3.0+ Menus ---------------------------------------------------*/
	register_nav_menu( 'primary-menu', __('Primary Menu', 'zilla') );

	/* Configure WP 2.9+ Thumbnails ---------------------------------------------*/
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 960, 9999, true ); // Normal post thumbnails
	add_image_size('portfolio-thumb', 360, 296, true);
	add_image_size('blog-thumb', 360, 270, true);

	// Add post formats support
	add_theme_support( 'post-formats', array( 'audio', 'gallery', 'image', 'link', 'quote', 'video' ) );

	// Add RSS feed links to the <head> for posts and comments
	add_theme_support( 'automatic-feed-links' );

	// Enable forms to use HTML5 markup
	add_theme_support( 'html5', array(
	    'search-form', 'comment-form'
	) );

}
endif;
add_action( 'after_setup_theme', 'zilla_theme_setup' );


if ( !function_exists( 'zilla_sidebars_init' ) ) :
/**
 * Register the sidebars for the theme
 *
 * @since 1.0
 *
 * @uses register_sidebar() To add sidebar areas
 * @return void
 */
function zilla_sidebars_init() {
	register_sidebars(3, array(
		'name' => __('Footer Column %d', 'zilla'),
		'id' => 'footer-column',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>'
	));
}
endif;
add_action( 'widgets_init', 'zilla_sidebars_init' );


if ( !function_exists( 'zilla_excerpt_length' ) ) :
/**
 * Sets a custom excerpt length for portfolios
 *
 * @since 1.0
 *
 * @param int $length Excerpt length
 * @return int New excerpt length
 */
function zilla_excerpt_length($length) {
	return 55;
}
endif;
add_filter('excerpt_length', 'zilla_excerpt_length');


if ( !function_exists( 'zilla_excerpt_more' ) ) :
/**
 * Replaces [...] in excerpt string
 *
 * @since 1.0
 *
 * @param string $excerpt Existing excerpt
 * @return string
 */
function zilla_excerpt_more($excerpt) {
	return '&#8230;';
}
endif;
add_filter('exceprt_more', 'zilla_excerpt_more');


if ( !function_exists( 'zilla_wp_title' ) ) :
/**
 * Creates formatted and more specific title element for output based
 * on current view
 *
 * @since 1.0
 *
 * @param string $title Default title text
 * @param string $sep Optional separator
 * @return string Formatted title
 */
function zilla_wp_title( $title, $sep ) {
	if( !zilla_is_third_party_seo() ){
		global $paged, $page;

		if( is_feed() )
			return $title;

		$title .= get_bloginfo( 'name' );

		$site_description = get_bloginfo( 'description', 'display' );
		if( $site_description && ( is_home() || is_front_page() ) )
			$title = "$title $sep $site_description";

		if( $paged >= 2 || $page >= 2 )
			$title = "$title $sep " . sprintf( __('Page %s', 'zilla'), max( $paged, $page ) );
	}
	return $title;
}
endif;
add_filter('wp_title', 'zilla_wp_title', 10, 2);


if ( !function_exists( 'zilla_enqueue_scripts' ) ) :
/**
 * Enqueues scripts and styles for front end
 *
 * @since 1.0
 *
 * @return void
 */
function zilla_enqueue_scripts() {
	/* Register our scripts --- */
	wp_register_script('modernizr', get_template_directory_uri() . '/js/modernizr-2.6.3.min.js', '', '2.6.3', false);
	wp_register_script('validation', 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js', 'jquery', '1.9', true);
	wp_register_script('superfish', get_template_directory_uri() . '/js/superfish.js', 'jquery', '1.7.4', true);
	wp_register_script('zillaMobileMenu', get_template_directory_uri() . '/js/jquery.zillamobilemenu.min.js', 'jquery', '0.1', true);
	wp_register_script('fitVids', get_template_directory_uri() . '/js/jquery.fitvids.js', 'jquery', '1.0', true);
	wp_register_script('jPlayer', get_template_directory_uri() . '/js/jquery.jplayer.min.js', 'jquery', '2.4', true);
	wp_register_script('cycle2', get_template_directory_uri() . '/js/jquery.cycle2.min.js', 'jquery', '2.1.5', true);
	wp_register_script('cycle2swipe', get_template_directory_uri() . '/js/jquery.cycle2.swipe.min.js', 'jquery', true);
	wp_register_script('zilla-custom', get_template_directory_uri() . '/js/jquery.custom.js', array('jquery', /*'masonry',*/ 'superfish', 'zillaMobileMenu', 'fitVids', 'cycle2', 'cycle2swipe'), '', true);

	/* Enqueue our scripts --- */
	wp_enqueue_script('modernizr');
	// jQuery and Masonry are loaded as zilla-custom depends on them
	wp_enqueue_script('zilla-custom');

	/* loads the javascript required for threaded comments --- */
	if( is_singular() && comments_open() && get_option( 'thread_comments') )
		wp_enqueue_script( 'comment-reply' );

	if( is_page_template('template-contact.php') )
		wp_enqueue_script('validation');

	/* Load our stylesheet --- */
	$zilla_options = get_option('zilla_framework_options');
	wp_enqueue_style( $zilla_options['theme_name'], get_stylesheet_uri() );
	wp_enqueue_style('google_lato', 'http://fonts.googleapis.com/css?family=Lato:400,700,400italic');

	/* Conditionally load IE stylesheet --- */
    wp_enqueue_style( 'ie8-style', get_template_directory_uri() . '/css/ie.css' );
	global $wp_styles;
	$wp_styles->add_data( 'ie8-style', 'conditional', 'IE' );
}
endif;
add_action('wp_enqueue_scripts', 'zilla_enqueue_scripts');


if ( !function_exists( 'zilla_enqueue_admin_scripts' ) ) :
/**
 * Enqueues scripts for back end
 *
 * @since 1.0
 * @return void
 */
function zilla_enqueue_admin_scripts() {
	wp_register_script( 'zilla-admin', get_template_directory_uri() . '/includes/js/jquery.custom.admin.js', 'jquery' );
	wp_enqueue_script( 'zilla-admin' );
}
endif;
add_action( 'admin_enqueue_scripts', 'zilla_enqueue_admin_scripts' );


if( ! function_exists( 'zilla_hash_the_tags' ) ) :
/**
 * Add a hash to the start of the tag term
 *
 * @param  array $els the tags
 * @return array      modded tags
 */
function zilla_hash_the_tags($els) {
	$modded_els = array();
	foreach( $els as $el ) {
		$modded_els[] = preg_replace('/>/', '>#', $el, 1);
	}
	return $modded_els;
}
endif;
add_filter('term_links-post_tag', 'zilla_hash_the_tags');


if( ! function_exists( 'zilla_author_bio' ) ) :
/**
 * Display the author bio on the author archive page
 *
 * @package Base
 * @since 1.0
 *
 * @return void
 */
function zilla_author_bio() {
?>
	<!--BEGIN .author-bio-->
	<div class="author-bio">
		<?php if( is_archive() ) { ?>
			<div class="author-avatar">
				<?php echo get_avatar( get_the_author_meta( 'user_email' ), 74 ); ?>
			</div><!-- .author-avatar -->
		<?php } ?>
		<div class="author-title"><?php _e('About the author', 'zilla') ?></div>
		<div class="author-description"><?php the_author_meta("description"); ?></div>
	<!--END .author-bio-->
	</div>
<?php
}
endif;


if ( !function_exists( 'zilla_remove_inline_recent_comment_styles' ) ) :
function zilla_remove_inline_recent_comment_styles() {
	global $wp_widget_factory;
	remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'  ) );
}
endif;
add_action( 'widgets_init', 'zilla_remove_inline_recent_comment_styles' );

if ( !function_exists( 'zilla_comment' ) ) :
/**
 * Custom comment HTML output
 *
 * @since 1.0
 *
 * @param $comment
 * @param $args
 * @param $depth
 * @return void
 */
function zilla_comment($comment, $args, $depth) {

	$isByAuthor = false;

	if($comment->comment_author_email == get_the_author_meta('email')) {
		$isByAuthor = true;
	}

	$GLOBALS['comment'] = $comment; ?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">

		<div id="comment-<?php comment_ID(); ?>" class="clearfix">

			<?php echo get_avatar($comment,$size='64'); ?>

			<header class="comment-header">
				<div class="comment-author vcard">
				<?php printf(__('<cite class="fn">%s</cite> ', 'zilla'), get_comment_author_link()) ?> <?php if($isByAuthor) { ?><span class="author-tag"><?php _e('(Author)', 'zilla') ?></span><?php } ?>
				</div>
				<div class="comment-meta commentmetadata"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>"><?php printf(__('%1$s at %2$s', 'zilla'), get_comment_date(),  get_comment_time()) ?></a><?php edit_comment_link(__('(Edit)', 'zilla'),'  ','') ?>  <?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?></div>
			</header>

			<div class="comment-content">
				<?php comment_text() ?>
			</div>

			<?php if ($comment->comment_approved == '0') : ?>
				<em class="moderation"><?php _e('Your comment is awaiting moderation.', 'zilla') ?></em><br />
			<?php endif; ?>

		</div>
<?php
}
endif;


if ( !function_exists( 'zilla_list_pings' ) ) :
/**
 * Separate pings from comments
 *
 * @since 1.0
 *
 * @param $comment
 * @param $args
 * @param $depth
 * @return void
 */
function zilla_list_pings($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment; ?>
	<li id="comment-<?php comment_ID(); ?>"><?php comment_author_link(); ?>
	<?php
}
endif;


if ( ! function_exists('zilla_add_html_to_thumbnail_html') ) :
/**
 * Add html markup to the entry-thumbnail for specific post types
 * @param  string $html   the existing html
 * @param  int $postid 	  the post id of the current post
 * @return string         the modified html
 */
function zilla_add_html_to_thumbnail_html($html, $postid) {
	$format = get_post_format($postid);
	if( $format == 'quote' ) {
		$quote = get_post_meta($postid, '_zilla_quote_quote', true);
		$html .= "\n<div class='overlay'>\n";
		$html .= "\t<blockquote>" . esc_html($quote) . "\n\t\t<cite>" . esc_html(get_the_title($postid)) . "</cite>\n\t</blockquote>\n";
		$html .= "</div>\n";
	} elseif( $format == 'link' ) {
		$url = get_post_meta($postid, '_zilla_link_url', true);
		$html .= "\n<div class='overlay'>\n";
		$html .= "<h2>" . esc_url($url) . "</h2>\n";
		$html .= "</div>";
	}
	return $html;
}
endif;
add_filter('post_thumbnail_html', 'zilla_add_html_to_thumbnail_html', 10, 2);


// Prevent archives for the portfolio plugin; will use a custom page template
if( !defined('TZP_DISABLE_ARCHIVE') ) define('TZP_DISABLE_ARCHIVE', TRUE);
// Prevent Zilla Portfolio CSS from loading
if( !defined('TZP_DISABLE_CSS') ) define('TZP_DISABLE_CSS', TRUE);

// Remove filters on the content that adds portfolio content to the_content output
remove_filter('the_content', 'tzp_add_portfolio_post_meta');
remove_filter('the_content', 'tzp_add_portfolio_post_media');

/**
 * Add meta field to general portfolio settings fields
 *
 * @param  int $post_id the post id
 * @return void
 */
function zilla_render_portfolio_settings_fields( $post_id ) { ?>
	<div class="tzp-field">
		<div class="tzp-left">
			<p><?php _e('Featured Portfolio:', 'zilla'); ?></p>
		</div>
		<div class="tzp-right">
			<ul class="tzp-inline-checkboxes">
				<li>
					<input type="checkbox" name="_zilla_base_featured_portfolio" id="_zilla_base_featured_portfolio"<?php checked( 1, get_post_meta( $post_id, '_zilla_base_featured_portfolio', true) ); ?> />
					<label for='_zilla_base_featured_portfolio'><?php _e('Feature Portfolio', 'zilla'); ?></label>
				</li>
			</ul>
			<p class='tzp-desc howto'><?php _e('Should this portfolio be featured at the top of your portfolio?', 'zilla'); ?></p>
		</div>
	</div>
<?php }
add_action( 'tzp_portfolio_settings_meta_box_fields', 'zilla_render_portfolio_settings_fields', 9 );


/**
 * Add the new meta fields to the array of values to be saved
 *
 * @param  array $array Array of the fields to be sanitized and saved
 * @return array        The updated array
 */
function zilla_save_added_portfolio_post_meta( $array ) {
	$array['_zilla_base_featured_portfolio'] = 'checkbox';
	return $array;
}
add_filter( 'tzp_metabox_fields_save', 'zilla_save_added_portfolio_post_meta' );


if ( !function_exists( 'zilla_add_portfolio_to_rss' ) ) :
/**
 * Adds portfolios to RSS feed
 *
 * @since 1.0
 *
 * @param obj $request
 * @return obj Updated request
 */
function zilla_add_portfolio_to_rss( $request ) {
	if (isset($request['feed']) && !isset($request['post_type']))
		$request['post_type'] = array('post', 'portfolio');

	return $request;
}
endif;
add_filter('request', 'zilla_add_portfolio_to_rss');


if( ! function_exists( 'zilla_set_archive_order' ) ) :
/**
 * Set the order for portfolio type taxonomy archives
 *
 * @param  obj $query the query object
 * @return void
 */
function zilla_set_archive_order($query) {
	if ( $query->is_tax( 'portfolio-type' ) && $query->is_main_query() ) {
        $query->set( 'orderby', 'menu_order' );
        $query->set( 'order', 'ASC' );
    }
}
endif;
add_action('pre_get_posts', 'zilla_set_archive_order');

if( ! function_exists( 'zilla_add_hash_to_portfolio_type' ) ) :
/**
 * Add a hashtag before the term name
 * @param  obj $terms    the terms
 * @param  int $postId   post id
 * @param  string $taxonomy the taxonomy
 * @return obj           the modified terms list
 */
function zilla_add_hash_to_portfolio_type($terms, $postId, $taxonomy) {
	if( $taxonomy == 'portfolio-type' ) {
		foreach( $terms as $term ) {
			$term->name = '#' . $term->name;
		}
	}

	return $terms;
}
endif;
add_filter( 'get_the_terms', 'zilla_add_hash_to_portfolio_type', 10, 3);

// apply_filters( 'get_the_terms', $terms, $post->ID, $taxonomy );

/**
 * Include the framework and template tags
 */
$tempdir = get_template_directory();

// Include framwork bits
require_once $tempdir .'/framework/init.php';
require_once $tempdir .'/includes/init.php';

// Custom template tags for theme
require $tempdir . '/includes/template-tags.php';