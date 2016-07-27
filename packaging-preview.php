<?php
/*
Plugin Name: Packaging Preview
Description: Set SEO and social distribution fields for posts and terms, and see a live preview of the appearance of search results or social share cards.
Author: Fusion Engineering
Author URI: http://fusion.net/section/tech-product
Version: 0.1.0
License: GPL v3
*/

use Packaging_Preview\Distribution_Fields;
use Packaging_Preview\Distribution_Metadata;
use Packaging_Preview\SEO_Preview;

class Packaging_Preview {

	private static $instance;

	public static $post_types;
	public static $taxonomies;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Packaging_Preview;

			self::$instance->load();
		}
		return self::$instance;
	}

	private function load() {
		spl_autoload_register( array( $this, 'spl_autoload' ) );

		static::$post_types = apply_filters( 'packaging_preview_post_types', array( 'post' ) );
		static::$taxonomies = apply_filters( 'packaging_preview_taxonomies', array( 'category', 'post_tag' ) );

		require_once 'inc/distribution-meta-functions.php';

		$this->distribution_fields = Distribution_Fields::get_instance();
		$this->distribution_metadata = Distribution_Metadata::get_instance();
		$this->seo_preview = SEO_Preview::get_instance( $this->post_types, $this->taxonomies );
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

