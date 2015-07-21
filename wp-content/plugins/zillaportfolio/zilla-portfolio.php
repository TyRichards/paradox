<?php
/**
 * Plugin Name: Zilla Portfolio
 * Plugin URI: http://themezilla.com/plugins/zilla-portfolio
 * Description: A portfolio manager for creative folks
 * Author: Mark Southard for ThemeZilla
 * Contributors: mbsatunc
 * Version: 1.0
 * Tags: themezilla, theme zilla, portfolio, custom post type, custom taxonomy, portfolio type, images, gallery, video, audio, custom fields
 * Text Domain: zilla-portfolio
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * Zilla Portfolio is free software: you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published by 
 * the Free Software Foundation, either version 2 of the License, or 
 * any later version.
 *
 * Zilla Portfolio is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * A copy of the GNU General Public License has been included with
 * Zilla Portfolio.
 *
 * @package  ZillaPortfolio
 * @version  1.0
 * @copyright  Copyright (c) 2013, ThemeZilla
 * @link  http://themezilla.com/plugins/zilla-portfolio
 * @license  http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Zilla_Portfolio' ) ) :

/**
 * Main Zilla_Portfolio Class
 *
 * @since  0.1.0
 */
final class Zilla_Portfolio {
	// Singleton Class

	/**
	 * @var Zilla_Portfolio The one Zilla_Portfolio
	 * @since 0.1.0
	 */
	private static $instance;

	/**
	 * Main Zilla_Portfolio Instance
	 *
	 * Ensures that only one instance of Zilla_Portfolio exists in memory at any one
	 * time.
	 *
	 * @since  0.1.0
	 * @static
	 * @uses  Zilla_Portfolio::setup_constants() Setup the constants
	 * @uses  Zilla_Portfolio::includes() Include the required files
	 * @uses  Zilla_Portfolio::load_textdomain() Load out text domain
	 * @see  TZP()
	 * @return  The true Zilla_Portfolio
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Zilla_Portfolio ) ) {
			self::$instance = new Zilla_Portfolio;
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->load_textdomain();
		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone
	 *
	 * Singleton design pattern. Only one object, so no clones for you
	 *
	 * @since  0.1.0
	 * @return  void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'zilla-portfolio' ), '0.1.0' );
	}

	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @since 0.1.0
	 * @return void
	 */
	private function setup_constants() {
		// Plugin Version
		if ( ! defined( 'TZP_VERSION' ) )
			define( 'TZP_VERSION', '0.1.0' );

		// Plugin directory path
		if ( ! defined( 'TZP_PLUGIN_DIR' ) )
			define( 'TZP_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );

		// Plugin directory URI
		if ( ! defined( 'TZP_PLUGIN_URI' ) )
			define( 'TZP_PLUGIN_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );

		// Plugin root file
		if ( ! defined( 'TZP_PLUGIN_FILE' ) )
			define( 'TZP_PLUGIN_FILE', __FILE__ );
	}

	/**
	 * Include the required files
	 *
	 * @access  private
	 * @since  0.1.0
	 * @return  void
	 */
	private function includes() {
		require_once TZP_PLUGIN_DIR . 'includes/portfolio-post-type.php';
		require_once TZP_PLUGIN_DIR . 'includes/portfolio-type-taxonomy.php';
		require_once TZP_PLUGIN_DIR . 'includes/scripts.php';
		require_once TZP_PLUGIN_DIR . 'includes/functions.php';

		if( is_admin() ) {
			require_once TZP_PLUGIN_DIR . 'includes/admin/admin.php';
			require_once TZP_PLUGIN_DIR . 'includes/admin/metaboxes.php';
		}

		require_once TZP_PLUGIN_DIR . 'includes/install.php';
	}

	/**
	 * Load the translation files
	 *
	 * @access public
	 * @since 0.1.0
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'zilla-portfolio', false, 'zilla-portfolio/languages' );
	}

}
endif; // End if class_exists check

/**
 * The main function to return one Zilla_Portfolio Instance
 *
 * Use like you would a global variable, except without needing to 
 * declare the global
 *
 * Example: <?php $portfolio = TZP(); ?>
 *
 * @since  0.1.0
 * @return object The one Zilla_Portfolio instance
 */
function TZP() {
	return Zilla_Portfolio::instance();
}

// Away we go!
TZP();