<?php
/**
 * Contains methods for customizing the theme customization screen.
 *
 * @link http://codex.wordpress.org/Theme_Customization_API
 * @since Broadcast 1.0
 */

add_action('customize_register', 'zilla_customize_register');
function zilla_customize_register($wp_customize) {

  class Zilla_Customize_Textarea_Control extends WP_Customize_Control {
    public $type = 'textarea';

    public function render_content() {
      ?>
      <label>
        <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
        <textarea style="width:100%" rows="8" <?php $this->link(); ?>><<?php echo esc_textarea( $this->value() ); ?></textarea>
      </label>
      <?php
    }
  }

  /* General Options --- */
  $wp_customize->add_section(
    'zilla_general_options',
     array(
        'title' => __( 'General Options', 'zilla' ),
        'priority' => 15,
        'capability' => 'edit_theme_options',
        'description' => __('Control and configure the general setup of your theme. Upload your preferred logo, setup your feeds and insert your analytics tracking code.', 'zilla')
     )
  );

  $wp_customize->add_setting(
    'zilla_theme_options[general_text_logo]',
    array( 'default' => '0' )
  );

  $wp_customize->add_control( 'zilla_general_text_logo', array(
    'label' => __( 'Plain Text Logo', 'zilla' ),
    'section' => 'zilla_general_options',
    'settings' => 'zilla_theme_options[general_text_logo]',
    'type' => 'checkbox'
  ));

  $wp_customize->add_setting(
    'zilla_theme_options[general_custom_logo]',
    array(
      'default' => get_template_directory_uri() . '/images/logo.png',
      'transport' => 'postMessage'
    )
  );

  $wp_customize->add_control( new WP_Customize_Image_Control(
    $wp_customize,
    'zilla_general_custom_logo',
    array(
      'label' => __( 'Logo Upload', 'zilla' ),
      'section' => 'zilla_general_options',
      'settings' => 'zilla_theme_options[general_custom_logo]'
    )
  ));

  $wp_customize->add_setting(
    'zilla_theme_options[general_custom_favicon]',
     array( 'default' => '' )
  );

  $wp_customize->add_control( new WP_Customize_Image_Control(
    $wp_customize,
    'zilla_general_custom_favicon',
    array(
      'label' => __( 'Favicon Upload (16x16 image file)', 'zilla' ),
      'section' => 'zilla_general_options',
      'settings' => 'zilla_theme_options[general_custom_favicon]'
    )
  ));

  $wp_customize->add_setting(
    'zilla_theme_options[general_contact_email]',
    array( 'type' => 'option' )
  );

  $wp_customize->add_control( new WP_Customize_Control(
    $wp_customize,
    'zilla_general_contact_email',
    array(
      'label' => __( 'Contact Form Email Address', 'zilla' ),
      'section' => 'zilla_general_options',
      'settings' => 'zilla_theme_options[general_contact_email]'
    )
  ));

  $wp_customize->add_setting(
    'zilla_theme_options[general_portfolio_columns]',
    array( 'default' => '2' )
  );

  $wp_customize->add_control(
    'zilla_general_portfolio_columns',
    array(
      'label'    => __( 'Portfolio Columns', 'zilla' ),
      'section'  => 'zilla_general_options',
      'settings' => 'zilla_theme_options[general_portfolio_columns]',
      'type'     => 'radio',
      'choices'  => array(
        '2'  => '2',
        '3' => '3',
      ),
    )
  );

  /* Style Options --- */
  $wp_customize->add_section(
    'zilla_style_options',
    array(
      'title' => __( 'Style Options', 'zilla' ),
      'priority' => 16,
      'capability' => 'edit_theme_options',
      'description' => __('Give your site a custom coat of paint by updating the style options.', 'zilla')
    )
  );

  $wp_customize->add_setting(
    'zilla_theme_options[style_accent_color]',
    array(
      'default' => '#4ac2be',
      'transport' => 'postMessage'
    )
  );

  $wp_customize->add_control( new WP_Customize_Color_Control(
    $wp_customize,
    'zilla_style_accent_color',
    array(
      'label' => __( 'Accent Color', 'zilla' ),
      'section' => 'zilla_style_options',
      'settings' => 'zilla_theme_options[style_accent_color]'
    )
  ));

  $wp_customize->add_setting( 'zilla_theme_options[style_custom_css]', array('default' => ''));

  $wp_customize->add_control( new Zilla_Customize_Textarea_Control(
    $wp_customize,
    'zilla_style_custom_css',
    array(
      'label' => __( 'Custom CSS', 'zilla' ),
      'section' => 'zilla_style_options',
      'settings' => 'zilla_theme_options[style_custom_css]',
      )
    ));

  if( $wp_customize->is_preview() && ! is_admin() )
    add_action('wp_footer', 'zilla_live_preview', 21);
}

/**
* This outputs the javascript needed to automate the live settings preview.
*
*/
function zilla_live_preview() {
  ?>
    <script type="text/javascript">
    ( function( $ ) {

      wp.customize( 'zilla_theme_options[general_custom_logo]', function( value ) {
        value.bind( function( newval ) {
          console.log(newval);
          $('#logo img').attr('src', newval);
        });
      });

      wp.customize( 'zilla_theme_options[style_accent_color]', function( value ) {
        value.bind( function( newval ) {
          $('#content a').css('color', newval );
        } );
      } );

    } )( jQuery );
  </script>
  <?php
}

/**
 * Add custom class if portfolio should be 3 columns
 * @param  array $classes the classes array
 * @return array          modified classes array
 */
function zilla_add_portfolio_column_class($classes) {
  $theme_options = get_theme_mod('zilla_theme_options');

  if( $theme_options && array_key_exists( 'general_portfolio_columns', $theme_options ) && $theme_options['general_portfolio_columns'] == 3 ) {
    $classes[] = 'portfolio-three-columns';
  }

  return $classes;
}
add_filter('body_class', 'zilla_add_portfolio_column_class');

/**
* This will output the custom WordPress settings to the live theme's WP head.
*
*/
function header_output() {

  $theme_options = get_theme_mod('zilla_theme_options');

  // No mods; no output
  if( empty($theme_options) )
    return;

  /* Output the favicon */
  if( array_key_exists( 'general_custom_favicon', $theme_options ) && $theme_options['general_custom_favicon'] != '' ) {
    echo '<link rel="shortcut icon" href="'. $theme_options['general_custom_favicon'] .'" />' . "\n";
  }

  if( array_key_exists( 'style_custom_css', $theme_options ) && $theme_options['style_custom_css'] != '' ) {
    echo '<!-- Custom CSS-->' . "\n" . '<style type="text/css">' . "\n\t" . $theme_options['style_custom_css'] . "\n" . '</style>' . "\n<!--/Custom CSS-->\n";
  }

  if( array_key_exists( 'style_accent_color', $theme_options ) && $theme_options['style_accent_color'] != '' && $theme_options['style_accent_color'] != '#4ac2be' ) {
    $bg_color = array('button:hover','html input[type="button"]:hover','input[type="reset"]:hover','input[type="submit"]:hover','button:focus','html input[type="button"]:focus','input[type="reset"]:focus','input[type="submit"]:focus','button:active','html input[type="button"]:active','input[type="reset"]:active','input[type="submit"]:active','.zilla-slide-prev:hover','.zilla-slide-next:hover', '.zilla-dribbble-shots li a', '.flickr_badge_image a');
    $bg_color_opacity = array('.format-quote .overlay','.format-link .overlay');
    $bdr_color = array('button:hover','html input[type="button"]:hover','input[type="reset"]:hover','input[type="submit"]:hover','button:focus','html input[type="button"]:focus','input[type="reset"]:focus','input[type="submit"]:focus','button:active','html input[type="button"]:active','input[type="reset"]:active','input[type="submit"]:active','.primary-menu a:hover','.entry-title a:hover','.primary-menu .sfHover > a');
    $color = array('a','.site-logo a:hover','.primary-menu a:hover','.primary-menu .sfHover > a','.page-navigation a:hover','.single-post-navigation a:hover','.entry-title a:hover','.portfolio-types a:hover','.portfolio-entry-meta a:hover');
    echo "<!-- Custom Accent Color -->\n<style type='text/css'>\n";
      foreach($bg_color as $bgc) {
        generate_css($bgc, 'background-color', 'style_accent_color');
      }
      foreach( $bg_color_opacity as $bgo ) {
        echo "\n$bgo { background: rgba(" . hex2rgb($theme_options['style_accent_color']) . ",0.7); }\n";
      }
      foreach($bdr_color as $bdrc) {
        generate_css($bdrc, 'border-color', 'style_accent_color');
      }
      foreach($color as $c) {
        generate_css($c, 'color', 'style_accent_color');
      }
      echo "\n.primary-menu a { color: #25292c; }\n";
      echo "\n.entry-meta a { color: #878788; }\n";
      echo "\n.page-navigation a { color: #25292c; }\n";
      echo "\n.primary-menu .sub-menu a,\n.entry-meta a:hover { border-color: transparent; }\n";
    echo "</style>\n<!-- /Custom Accent Color -->\n";
  }
}

/**
 * This will generate a line of CSS for use in header output. If the setting
 * ($mod_name) has no defined value, the CSS will not be output.
 *
 * @uses get_theme_mod()
 * @param string $selector CSS selector
 * @param string $style The name of the CSS *property* to modify
 * @param string $mod_name The name of the 'theme_mod' option to fetch
 * @param string $prefix Optional. Anything that needs to be output before the CSS property
 * @param string $postfix Optional. Anything that needs to be output after the CSS property
 * @param bool $echo Optional. Whether to print directly to the page (default: true).
 * @return string Returns a single line of CSS with selectors and a property.
 * @since MyTheme 1.0
 */
function generate_css( $selector, $style, $mod_name, $prefix='', $postfix='', $echo=true ) {
  $return = '';
  $mods = get_theme_mod('zilla_theme_options');
  $mod = $mods[$mod_name];
  if ( ! empty( $mod ) ) {
     $return = sprintf('%s { %s:%s; }',
        $selector,
        $style,
        $prefix.$mod.$postfix
     );
     if ( $echo ) {
        echo $return;
     }
  }
  return $return;
}
// Output custom CSS to live site
add_action( 'wp_head' , 'header_output' );

/**
 * Convert a hexcode value to rgb
 * @param  string $hex hexcode to be convert
 * @return string      rgb values comma separated
 */
function hex2rgb($hex) {
   $hex = str_replace("#", "", $hex);

   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   $rgb = array($r, $g, $b);

   return implode(",", $rgb); // returns the rgb values separated by commas
}