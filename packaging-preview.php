<?php
/*
Plugin Name: Packaging Preview
Description: Set SEO and social distribution fields for posts and terms, and see a live preview of the appearance of search results or social share cards.
Author: Fusion Engineering
Author URI: http://fusion.net/section/tech-product
Version: 0.1.0
License: GPL v3
*/

use Packaging_Preview\Distribution_Settings;
use Packaging_Preview\Distribution_Fields;
use Packaging_Preview\Distribution_Metadata;
use Packaging_Preview\SEO_Preview;

define( 'FUSION_TWITTER_SHARE_TEXT_MAX_LENGTH', 92 );

class Packaging_Preview {

	private static $instance;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Packaging_Preview;

			self::$instance->load();
		}
		return self::$instance;
	}

	private function load() {

		// If Fieldmanager hasn't been initialized yet, bail to prevent fatals
		if ( ! function_exists( 'fm_register_submenu_page' ) ) {
			return;
		}

		spl_autoload_register( array( $this, 'spl_autoload' ) );

		require_once 'inc/utils-functions.php';
		require_once 'inc/distribution-meta-functions.php';

		$this->distribution_settings = Distribution_Settings::get_instance();
		$this->distribution_fields = Distribution_Fields::get_instance();
		$this->distribution_metadata = Distribution_Metadata::get_instance();
		$this->seo_preview = SEO_Preview::get_instance();
	}

	/*
	 * Filter the post types Packaging Preview is available for
	 *
	 * @param array Array of post types
	 */
	public static function post_types() {
		return apply_filters( 'packaging_preview_post_types', array( 'post' ) );
	}

	/*
	 * Filter the taxonomies Packaging Preview is available for
	 *
	 * @param array Array of taxonomies
	 */
	public static function taxonomies() {
		return apply_filters( 'packaging_preview_taxonomies', array( 'category', 'post_tag' ) );
	}

	/**
	 * Autoloader function for any class in the plugin's namespace.
	 *
	 */
	function spl_autoload( $class ) {

		// project-specific namespace
		$prefix = 'Packaging_Preview';

		$parts = explode( '\\', $class );

		if ( $parts[0] !== $prefix ) {
			return;
		}

		array_shift( $parts );

		$last = array_pop( $parts ); // File should be 'class-[...].php'
		$last = 'class-' . $last . '.php';

		$parts[] = $last;
		$file = dirname( __FILE__ ) . '/inc/' . str_replace( '_', '-', strtolower( implode( $parts, '/' ) ) );

		//If the file exists....
		if ( file_exists( $file ) ) {
			//Require the file
			require( $file );
		}
	}
}

Packaging_Preview::get_instance();

