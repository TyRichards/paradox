=== Zilla Portfolio ===
Contributors: mbsatunc
Tags: themezilla, theme zilla, portfolio, custom post type, custom taxonomy, portfolio type, images, gallery, video, audio, custom fields
Stable Tag: 1.0
Tested up to: 4.1
Requires at least: 3.5
License: GPLv2 or later

A complete portfolio plugin for creative folks

== Description ==

This plugin adds the portfolio custom post type to your WordPress blog. By default, the plugin will append portfolio meta information (client, date, and project URL) to the portfolio post. It will prepend media elements (gallery, audio and video media) to portfolio posts.

== Installation ==

Just install and activate

== Creating Themes for this Plugin ==

There are several handy bits that you can use within your theme:
1. By default, the portfolio posts are displayed in the portfolio archive. However, you may want to create a custom page template that will display your portfolio posts. As such, you'll need to disable the archives. In your theme's function file, use the following code: `<?php if( !defined('TZP_DISABLE_ARCHIVE') ) define('TZP_DISABLE_ARCHIVE', TRUE); ?>`
This will enable child themes to enable the portfolio archives if desired.
2. Set custom slugs for 'portfolio' and 'portfolio-type' by defining constants for: `TZP_SLUG` and `TZP_TAX_SLUG`. After defining the constants, save the Permalink Settings. Also, define the constants as above to allow a child theme to customize the slugs.
3. There are several actions and filters available for adding additional custom fields to the existings metaboxes. Have a look through metaboxes.php to see how these all play together.
4. To prevent the media and meta from being added to the_content(), remove these filters: `tzp_add_portfolio_post_media` and `tzp_add_portfolio_post_meta`
5. To update the image size used for galleries add a filter to 'tzp_set_gallery_image_size'. Pass the string name or an array of the image size to be used.

== Complete List of Constants and Actions/Filters ==

_Constants_

* `TZP_DISABLE_CSS` set to true to prevent plugin from loading basic CSS
* `TZP_PORTFOLIO_ORDER` default is 'ASC'
* `TZP_PORTFOLIO_ORDERBY` default is 'menu_order'
* `TZP_DISABLE_ARCHIVE` default is false
* `TZP_SLUG` default is 'portfolio'
* `TZP_DISABLE_REWRITE`
* `TZP_TAX_SLUG`
* `TZP_DISABLE_MEDIAELEMENT_STYLE` prevent the plugin from loading the default mediaelement stylesheet

_Actions_

* `tzp_portfolio_settings_meta_box_fields` add meta fields to the settings section
* `tzp_portfolio_gallery_meta_box_fields` add meta fields to the gallery section
* `tzp_portfolio_audio_meta_box_fields` add meta fields to the audio section
* `tzp_portfolio_video_meta_box_fields` add meta fields to the video section

_Actions added in plugin that you may want to remove_

* `tzp_add_custom_css`
* `tzp_portfolios_display_order`

_Filters_

* `tzp_metabox_fields_save` add fields to be saved (use url, html, checkbox, or images for sanitization)
* `tzp_gallery_classes` class added to gallery; default is tzp-portfolio-gallery
* `tzp_set_gallery_image_size` default image size is 'full'
* `tzp_portfolio_labels`
* `tzp_portfolio_supports`
* `tzp_portfolio_post_type_args`
* `tzp_portfolio_type_labels`
* `tzp_portfolio_type_args`

_Filters applied in plugin that you may want to remove_

* `tzp_add_portfolio_post_media`
* `tzp_add_portfolio_post_meta`

== Changelog ==

= 1.0 =
* Initial release