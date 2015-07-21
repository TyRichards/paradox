<?php
/**
 * Metabox Functions
 *
 * @package ZillaPortfolio
 * @subpackage Includes/Admin
 * @since 0.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create our meta boxes
 *
 * @since 0.1.0
 * @return void
 */
function tzp_add_portfolio_meta_box() {

	// Add General Portfolio Settings Metabox
	add_meta_box( 'tzp-portfolio-metabox-settings', __('Zilla Portfolio Settings', 'zilla-portfolio'), 'tzp_render_portfolio_settings_meta_box', 'portfolio', 'normal', 'high' );

	// Add Portfolio Gallery Metabox
	add_meta_box( 'tzp-portfolio-metabox-gallery', __('Zilla Portfolio Gallery', 'zilla-portfolio'), 'tzp_render_portfolio_gallery_meta_box', 'portfolio', 'normal', 'high' );

	// Add Portfolio Audio Metabox
	add_meta_box( 'tzp-portfolio-metabox-audio', __('Zilla Portfolio Audio', 'zilla-portfolio'), 'tzp_render_portfolio_audio_meta_box', 'portfolio', 'normal', 'high' );

	// Add Portfolio Video Metabox
	add_meta_box( 'tzp-portfolio-metabox-video', __('Zilla Portfolio Video', 'zilla-portfolio'), 'tzp_render_portfolio_video_meta_box', 'portfolio', 'normal', 'high' );

}
add_action( 'add_meta_boxes', 'tzp_add_portfolio_meta_box' );

/**
 * Save portfolio meta when the save_post action is called
 *
 * @since 0.1.0
 * @param int $post_id Portfolio ID
 * @global array $post All of the data of current post
 * @return void
 */
function tzp_portfolio_meta_box_save( $post_id ) {
	global $post;

	if ( !isset( $_POST['tzp_portfolio_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['tzp_portfolio_meta_box_nonce'], basename( __FILE__ ) ) )
		return $post_id;

	if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['buld_edit'] ) )
		return $post_id;

	if ( isset( $post->post_type ) && $post->post_type == 'revision' )
		return $post_id;

	if ( ! current_user_can( 'edit_post', $post_id ) )
		return $post_id;

	// Default fields that get saved
	$fields = apply_filters( 'tzp_metabox_fields_save', array(
		'_tzp_portfolio_url' 		=> 'url',
		'_tzp_portfolio_date'		=> 'html',
		'_tzp_portfolio_client'	=> 'html',
		'_tzp_display_gallery'	=> 'checkbox',
		'_tzp_display_audio'		=> 'checkbox',
		'_tzp_display_video'		=> 'checkbox',
		'_tzp_gallery_images'		=> 'images',
		'_tzp_audio_poster_url'	=> 'url',
		'_tzp_audio_file_mp3'		=> 'url',
		'_tzp_audio_file_ogg'		=> 'url',
		'_tzp_video_poster_url'	=> 'url',
		'_tzp_video_file_m4v'		=> 'url',
		'_tzp_video_file_ogv'		=> 'url',
		'_tzp_video_file_mp4'		=> 'url',
		'_tzp_video_embed'			=> 'html'
		)
	);

	foreach( $fields as $key => $type ) {
		if ( ! empty( $_POST[ $key ] ) ) {
			// sanitize fields with apply_filters
			$new = apply_filters( 'tzp_metabox_save_' . $type, $_POST[ $key ]);
			update_post_meta( $post_id, $key, $new );
		} else {
			delete_post_meta( $post_id, $key );
		}
	}
}
add_action( 'save_post', 'tzp_portfolio_meta_box_save' );

function tzp_save_gallery_images() {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;
	
	if ( !isset($_POST['ids']) || !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'tzp_ajax' ) )
		return;
	
	if ( !current_user_can( 'edit_posts' ) ) 
		return;
 
	$ids = tzp_metabox_sanitize_images( $_POST['ids'] );
	update_post_meta($_POST['post_id'], '_tzp_gallery_images_ids', $ids);

	// update thumbs
	$thumbs = explode(',', $ids);
	$thumbs_output = '';
	foreach( $thumbs as $thumb ) {
		$thumbs_output .= '<li>' . wp_get_attachment_image( $thumb, array(32,32) ) . '</li>';
	}

	echo $thumbs_output;

	die();
}
add_action('wp_ajax_tzp_save_gallery_images', 'tzp_save_gallery_images');

/**
 * Sanitize html fields before saving
 * 
 * @param string $field The field being sanitized
 * @return string $field Sanitized html field
 */
function tzp_metabox_sanitize_html( $field ) {
	return wp_kses( 
		$field, 
		array(
			'a' => array( 
				'href' => array(), 
				'title' => array(),
				'id' => array(),
				'class' => array()
			), 
			'br' => array(), 
			'em' => array(), 
			'strong' => array(), 
			'iframe' => array(
				'width' => array(),
				'height' => array(),
				'src' => array(),
				'frameborder' => array(),
				'wmode' => array(),
				'allowfullscreen' => array()
			),
			'img' => array(
				'src' => array(),
				'alt' => array(),
				'class' => array()
			), 
			'div' => array(
				'id' => array(),
				'class' => array()
			)
		) 
	);
}
add_filter( 'tzp_metabox_save_html', 'tzp_metabox_sanitize_html' );

/**
 * Sanitize URL fields before saving
 * @param  string $field The url to be sanitized
 * @return string $field Sanitized URL string
 */
function tzp_metabox_sanitizie_url( $field ) {
	return esc_url_raw( $field );
}
add_filter( 'tzp_metabox_save_url', 'tzp_metabox_sanitizie_url' );

/**
 * Sanitize checkbox fields before saving
 * 
 * @param string $field Checked checkbox will post 'on'
 * @return bool Return true if checked
 */
function tzp_metabox_sanitize_checkbox( $field ) {
	return true;
}
add_filter( 'tzp_metabox_save_checkbox', 'tzp_metabox_sanitize_checkbox' );

/**
 * Santize the string of ids
 * @param  string $field A comma separated string of ids
 * @return string        Sanitized string
 */
function tzp_metabox_sanitize_images( $field ) {
	return sanitize_text_field( rtrim( $field, ',' ) );
}
add_filter( 'tzp_metabox_save_images', 'tzp_metabox_sanitize_images' );

/**
 * Portfolio General Settings Metabox
 *
 * Extensions and themes can add items to the general portfolio settings
 * metabox via the 'tzp_portfolio_settings_meta_box_fields' action
 *
 * @since 0.1.0
 * @return void
 */
function tzp_render_portfolio_settings_meta_box() {
	global $post;

	echo '<div class="tzp-metabox">';
	printf( '<p class="tzp-intro">%1$s</p>', __('Configure your portfolio post in this section. For any information that you do not wish to be displayed for this portfolio project, please leave blank.', 'zilla-portfolio') );

	do_action( 'tzp_portfolio_settings_meta_box_fields', $post->ID );
	wp_nonce_field( basename(__FILE__), 'tzp_portfolio_meta_box_nonce' );
	echo '</div>';
}

/**
 * Portfolio Gallery Metabox
 *
 * Extensions and themes can add items to the gallery metabox via the
 * 'tzp_portfolio_gallery_meta_box_fields' action
 *
 * @since 0.1.0
 * @return void
 */
function tzp_render_portfolio_gallery_meta_box() {
	global $post;

	echo '<div class="tzp-metabox">';
	printf( '<p class="tzp-intro">%1$s</p>', __('Set up your gallery for display in your portfolio.', 'zilla-portfolio') );

	do_action( 'tzp_portfolio_gallery_meta_box_fields', $post->ID );
	echo '</div>';
}

/**
 * Portfolio Audio Metabox
 *
 * Extensions and themes can add items to the audio metabox via the
 * 'tzp_portfolio_audio_meta_box_fields' action
 *
 * @since 0.1.0
 * @return void
 */
function tzp_render_portfolio_audio_meta_box() {
	global $post;

	echo '<div class="tzp-metabox">';
	printf( '<p class="tzp-intro">%1$s</p>', __('Set up your audio media for display in your portfolio. Only 1 file type is required for audio to work in all browsers, but the providing additional file types will ensure that users view an HTML5 audio element if available.', 'zilla') );

	do_action( 'tzp_portfolio_audio_meta_box_fields', $post->ID );
	echo '</div>';
}

/**
 * Portfolio Video Metabox
 *
 * Extensions and themes can add items to the video metabox via the 
 * 'tzp_portfolio_video_meta_box_fields' action
 *
 * @since 0.1.0
 * @return void
 */
function tzp_render_portfolio_video_meta_box() {
	global $post;

	echo '<div class="tzp-metabox">';
	printf( '<p class="tzp-intro">%1$s</p>', __('Set up your video media for display in your portfolio. Adding to the video embed field will override the self hosted options. For self hosted video, the more file types you provide the more likely a user will view an HTML5 video element. However, only one file type is required.', 'zilla-portfolio') );

	do_action( 'tzp_portfolio_video_meta_box_fields', $post->ID );
	echo '</div>';
}

/**
 * General Portfolio Settings Fields
 *
 * Used to output the general portfolio settings fields.
 *
 * @since 0.1.0
 * @param int $post_id The ID of the portfolio post
 */
function tzp_render_portfolio_settings_fields( $post_id ) {	
?>
	<div class="tzp-field">
		<div class="tzp-left">
			<label for='_tzp_portfolio_url'><?php _e('Portfolio Project URL:', 'zilla-portfolio'); ?></label>
		</div>
		<div class="tzp-right">
			<input type="text" name="_tzp_portfolio_url" id="_tzp_portfolio_url" value="<?php echo esc_attr( get_post_meta( $post_id, '_tzp_portfolio_url', true) ); ?>" />
			<p class='tzp-desc howto'><?php _e('The live project URL (e.g., http://www.nike.com).', 'zilla-portfolio'); ?></p>
		</div>	
	</div>
	
	<div class="tzp-field">
		<div class="tzp-left">
			<label for='_tzp_portfolio_date'><?php _e('Portfolio Project Date:', 'zilla-portfolio'); ?></label>
		</div>
		<div class="tzp-right">
			<input type="text" name="_tzp_portfolio_date" id="_tzp_portfolio_date" value="<?php echo esc_attr( get_post_meta( $post_id, '_tzp_portfolio_date', true) ); ?>" />
			<p class='tzp-desc howto'><?php _e('When this project was completed (e.g., June 2013).', 'zilla-portfolio'); ?></p>
		</div>	
	</div>

	<div class="tzp-field">
		<div class="tzp-left">
			<label for='_tzp_portfolio_client'><?php _e('Portfolio Project Client:', 'zilla-portfolio'); ?></label>
		</div>
		<div class="tzp-right">
			<input type="text" name="_tzp_portfolio_client" id="_tzp_portfolio_client" value="<?php echo esc_attr( get_post_meta( $post_id, '_tzp_portfolio_client', true) ); ?>" />
			<p class='tzp-desc howto'><?php _e('The project client (e.g., Nike).', 'zilla-portfolio'); ?></p>
		</div>	
	</div>
	
	<div class="tzp-field">
		<div class="tzp-left">
			<p><?php _e('Portfolio Project Media:', 'zilla-portfolio'); ?></p>
		</div>
		<div class="tzp-right">
<?php 
			$display_gallery = get_post_meta( $post_id, '_tzp_display_gallery', true );
			$display_audio = get_post_meta( $post_id, '_tzp_display_audio', true );
			$display_video = get_post_meta( $post_id, '_tzp_display_video', true );
?>
			<ul class="tzp-inline-checkboxes">
				<li>
					<input type="checkbox" name="_tzp_display_gallery" id="_tzp_display_gallery"<?php checked( 1, $display_gallery ); ?> data-related-metabox-id="tzp-portfolio-metabox-gallery" />
					<label for="_tzp_display_gallery"><?php _e('Display Gallery', 'zilla-portfolio'); ?></label>					
				</li>
				<li>
					<input type="checkbox" name="_tzp_display_audio" id="_tzp_display_audio"<?php checked( 1, $display_audio ); ?> data-related-metabox-id="tzp-portfolio-metabox-audio" />
					<label for="_tzp_display_audio"><?php _e('Display Audio', 'zilla-portfolio'); ?></label>					
				</li>
				<li>
					<input type="checkbox" name="_tzp_display_video" id="_tzp_display_video"<?php checked( 1, $display_video ); ?> data-related-metabox-id="tzp-portfolio-metabox-video" />
					<label for="_tzp_display_video"><?php _e('Display Video', 'zilla-portfolio'); ?></label>					
				</li>
			</ul>
			<p class='tzp-desc howto'><?php _e('Select the media formats that should be displayed.', 'zilla-portfolio'); ?></p>
		</div>	
	</div>	
<?php
}
add_action( 'tzp_portfolio_settings_meta_box_fields', 'tzp_render_portfolio_settings_fields', 10 );

/**
 * Portfolio Gallery Fields
 *
 * Used to output the portfolio gallery fields.
 *
 * @since 0.1.0
 * @param int $post_id The ID of the portfolio post
 */
function tzp_render_portfolio_gallery_fields( $post_id ) {
	$images_ids = get_post_meta( $post_id, '_tzp_gallery_images_ids', true );
?>
	<div class="tzp-field">
		<div class="tzp-left">
			<label for='_tzp_gallery_images_ids'><?php _e('Gallery Images:', 'zilla-portfolio'); ?></label>
		</div>
		<div class="tzp-right">
			<input type="hidden" name="_tzp_gallery_images_ids" id="_tzp_gallery_images_ids" value="<?php echo esc_attr( $images_ids ); ?>" />
			<input type="button" class="_tzp_gallery_upload_button button" name="_tzp_gallery_upload_button" id="_tzp_gallery_upload_button" value="<?php if( $images_ids ) { _e( 'Edit Gallery', 'zilla-portfolio' ); } else { _e( 'Upload Images', 'zilla-portfolio' ); } ?>" />
			<p class='tzp-desc howto tzp-gallery-upload-desc'><?php _e('Edit the gallery by clicking to upload or edit the gallery.', 'zilla-portfolio'); ?></p>
			<ul id="_tzp_gallery_images" class="tzp-gallery-thumbs">
				<?php if( $images_ids ) {
					$thumbs = explode(',', $images_ids);
					$thumbs_output = '';
					foreach( $thumbs as $thumb ) {
						$thumbs_output .= '<li>' . wp_get_attachment_image( $thumb, array(32,32) ) . '</li>';
					}
					echo html_entity_decode( esc_html( $thumbs_output ) );
				} ?>
			</ul>
		</div>	
	</div>
<?php
}
add_action( 'tzp_portfolio_gallery_meta_box_fields', 'tzp_render_portfolio_gallery_fields', 10 );

/**
 * Portfolio Audio Fields
 *
 * Used to output the portfolio audio fields
 *
 * @since 0.1.0
 * @param int $post_id The ID of the portfolio post
 */
function tzp_render_portfolio_audio_fields( $post_id ) {
?>
	<div class="tzp-field">
		<div class="tzp-left">
			<label for='_tzp_audio_poster_url'><?php _e('Poster Image:', 'zilla-portfolio'); ?></label>
		</div>
		<div class="tzp-right">
			<input type="text" name="_tzp_audio_poster_url" id="_tzp_audio_poster_url" value="<?php echo esc_attr( get_post_meta( $post_id, '_tzp_audio_poster_url', true ) ); ?>" class="file" />
			<input type="button" class="tzp-upload-file-button button" name="_tzp_audio_poster_url_button" data-post-id="<?php echo $post_id; ?>" id="_tzp_audio_poster_url_button" value="<?php esc_attr_e( 'Browse', 'zilla-portfolio' ); ?>" />
			<p class='tzp-desc howto'><?php _e('Add a poster image to your audio player (optional).', 'zilla-portfolio'); ?></p>
		</div>
	</div>

	<div class="tzp-field">
		<div class="tzp-left">
			<label for='_tzp_audio_file_mp3'><?php _e('.mp3 File:', 'zilla-portfolio'); ?></label>
		</div>
		<div class="tzp-right">
			<input type="text" name="_tzp_audio_file_mp3" id="_tzp_audio_file_mp3" value="<?php echo esc_attr( get_post_meta( $post_id, '_tzp_audio_file_mp3', true ) ); ?>" class="file" />
			<input type="button" class="tzp-upload-file-button button" name="_tzp_audio_file_mp3_button" data-post-id="<?php echo $post_id; ?>" id="_tzp_audio_file_mp3_button" value="<?php esc_attr_e( 'Browse', 'zilla-portfolio' ); ?>" />
			<p class='tzp-desc howto'><?php _e('Insert an .mp3 file, if desired.', 'zilla-portfolio'); ?></p>
		</div>
	</div>

	<div class="tzp-field">
		<div class="tzp-left">
			<label for='_tzp_audio_file_ogg'><?php _e('.ogg File:', 'zilla-portfolio'); ?></label>
		</div>
		<div class="tzp-right">
			<input type="text" name="_tzp_audio_file_ogg" id="_tzp_audio_file_ogg" value="<?php echo esc_attr( get_post_meta( $post_id, '_tzp_audio_file_ogg', true ) ); ?>" class="file" />
			<input type="button" class="tzp-upload-file-button button" name="_tzp_audio_file_ogg_button" data-post-id="<?php echo $post_id; ?>" id="_tzp_audio_file_ogg_button" value="<?php esc_attr_e( 'Browse', 'zilla-portfolio' ); ?>" />
			<p class='tzp-desc howto'><?php _e('Insert an .ogg file, if desired.', 'zilla-portfolio'); ?></p>
		</div>
	</div>
<?php
}
add_action( 'tzp_portfolio_audio_meta_box_fields', 'tzp_render_portfolio_audio_fields', 10 );

/**
 * Portfolio Video Fields
 *
 * Used to output the portfolio video fields
 *
 * @since 0.1.0
 * @param int $post_id The ID of the portfolio post
 */
function tzp_render_portfolio_video_fields( $post_id ) {
?>
	<div class="tzp-field">
		<div class="tzp-left">
			<label for='_tzp_video_poster_url'><?php _e('Poster Image:', 'zilla-portfolio'); ?></label>
		</div>
		<div class="tzp-right">
			<input type="text" name="_tzp_video_poster_url" id="_tzp_video_poster_url" value="<?php echo esc_attr( get_post_meta( $post_id, '_tzp_video_poster_url', true ) ); ?>" class="file" />
			<input type="button" class="tzp-upload-file-button button" name="_tzp_video_poster_url_button" data-post-id="<?php echo $post_id; ?>" id="_tzp_video_poster_url_button" value="<?php esc_attr_e( 'Browse', 'zilla-portfolio' ); ?>" />
			<p class='tzp-desc howto'><?php _e('Add a poster image for your video player (optional).', 'zilla-portfolio'); ?></p>
		</div>
	</div>

	<div class="tzp-field">
		<div class="tzp-left">
			<label for='_tzp_video_file_m4v'><?php _e('.m4v File:', 'zilla-portfolio'); ?></label>
		</div>
		<div class="tzp-right">
			<input type="text" name="_tzp_video_file_m4v" id="_tzp_video_file_m4v" value="<?php echo esc_attr( get_post_meta( $post_id, '_tzp_video_file_m4v', true ) ); ?>" class="file" />
			<input type="button" class="tzp-upload-file-button button" name="_tzp_video_file_m4v_button" data-post-id="<?php echo $post_id; ?>" id="_tzp_video_file_m4v_button" value="<?php esc_attr_e( 'Browse', 'zilla-portfolio' ); ?>" />
			<p class='tzp-desc howto'><?php _e('Insert an .m4v file, if desired.', 'zilla-portfolio'); ?></p>
		</div>
	</div>

	<div class="tzp-field">
		<div class="tzp-left">
			<label for='_tzp_video_file_ogv'><?php _e('.ogv File:', 'zilla-portfolio'); ?></label>
		</div>
		<div class="tzp-right">
			<input type="text" name="_tzp_video_file_ogv" id="_tzp_video_file_ogv" value="<?php echo esc_attr( get_post_meta( $post_id, '_tzp_video_file_ogv', true ) ); ?>" class="file" />
			<input type="button" class="tzp-upload-file-button button" name="_tzp_video_file_ogv_button" data-post-id="<?php echo $post_id; ?>" id="_tzp_video_file_ogv_button" value="<?php esc_attr_e( 'Browse', 'zilla-portfolio' ); ?>" />
			<p class='tzp-desc howto'><?php _e('Insert an .ogv file, if desired.', 'zilla-portfolio'); ?></p>
		</div>
	</div>

	<div class="tzp-field">
		<div class="tzp-left">
			<label for='_tzp_video_file_mp4'><?php _e('.mp4 File:', 'zilla-portfolio'); ?></label>
		</div>
		<div class="tzp-right">
			<input type="text" name="_tzp_video_file_mp4" id="_tzp_video_file_mp4" value="<?php echo esc_attr( get_post_meta( $post_id, '_tzp_video_file_mp4', true ) ); ?>" class="file" />
			<input type="button" class="tzp-upload-file-button button" name="_tzp_video_file_mp4_button" data-post-id="<?php echo $post_id; ?>" id="_tzp_video_file_mp4_button" value="<?php esc_attr_e( 'Browse', 'zilla-portfolio' ); ?>" />
			<p class='tzp-desc howto'><?php _e('Insert an .mp4 file, if desired.', 'zilla-portfolio'); ?></p>
		</div>
	</div>

	<div class="tzp-field">
		<div class="tzp-left">
			<label for='_tzp_video_embed'><?php _e('Video Embed:', 'zilla-portfolio'); ?></label>
		</div>
		<div class="tzp-right">
			<textarea name="_tzp_video_embed" id="_tzp_video_embed" rows="8" cols="5"><?php echo esc_textarea( get_post_meta( $post_id , '_tzp_video_embed', true ) ); ?></textarea>
			<p class='tzp-desc howto'><?php printf( '%1$s <br /><strong>%2$s</strong>.', __('Embed iframe code from YouTube, Vimeo or other trusted source. HTML tags are limited to iframe, div, img, a, em, strong and br.', 'zilla-portfolio'), __('This field overrides the previous fields.', 'zilla-portfolio') ); ?></p>
		</div>
	</div>
<?php
}
add_action( 'tzp_portfolio_video_meta_box_fields', 'tzp_render_portfolio_video_fields', 10 );