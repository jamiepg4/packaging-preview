<?php

namespace Packaging_Preview;

use Packaging_Preview;

class Distribution_Metadata {

	private static $instance;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Distribution_Metadata;
			self::$instance->setup_actions();
		}
		return self::$instance;
	}

	/**
	 * Set up distribution metadata actions
	 */
	private function setup_actions() {
		add_action( 'wp_head', array( $this, 'action_wp_head_social_meta_tags' ) );
	}

	/**
	 * "Canonicalize" the current request URL
	 *
	 * return string URL of the current request, without any query args
	 */
	private function get_request_uri() {
		global $wp;
		return home_url( $wp->request );
	}

	/**
	 * Add meta tags to the head of our site
	 */
	public function action_wp_head_social_meta_tags() {

		if ( is_singular( Packaging_Preview::post_types() ) ) {
			$context = 'post';
			$object_id = get_queried_object_id();
		} else if ( is_tax( Packaging_Preview::taxonomies() ) ) {
			$context = 'term';
			$object_id = get_queried_object_id();
		} else if ( is_home() ) {
			$context = 'home';
		} else {
			return;
		}

		echo $this->get_social_meta_tags( $object_id, $context );
	}

	/**
	 * Get all the social meta tags for a request
	 *
	 * @param int Queried object ID
	 * @param string "post"|"term"
	 * @return string HTML representing all social distribution meta tags
	 */
	public function get_social_meta_tags( $object_id = null, $context = null ) {

		$meta_tags = '';

		$meta_tags .= '<meta name="description" content="' . esc_attr( $this->get_current_meta_description( $object_id ) ) . '" />' . PHP_EOL;

		if ( $object_id && 'post' === $context
				&& in_array( get_post_type( $object_id ), Packaging_Preview::post_types() ) ) {
			$meta_tags .= '<meta name="news_keywords" content="' . esc_attr( get_seo_keywords( $object_id ) ) . '" />' . PHP_EOL;

			if ( is_google_standout_enabled( $object_id ) ) {
				$meta_tags .= '<link rel="standout" href="' . esc_attr( get_permalink( $object_id ) ) . '" />' . PHP_EOL;
			}
		}

		$facebook_tags = $this->get_facebook_open_graph_meta_tags( $object_id, $context );
		$twitter_tags = $this->get_twitter_card_meta_tags( $object_id, $context );

		$tags = array_merge( $facebook_tags, $twitter_tags );

		foreach ( array_filter( $tags ) as $name => $value ) {

			// An array of values is perfectly legitimate for some OG properties. eg `article:author`
			if ( is_array( $value ) ) {
				foreach ( $value as $individual_value ) {
					$meta_tags .= '<meta property="' . esc_attr( $name ) . '" content="' . esc_url( $individual_value ) . '" />' . PHP_EOL;
				}
			} else {

				// Encoded ampersands in URLs seem to cause Facebook some anguish trying to parse
				if ( in_array( $name, array( 'og:image', 'og:url', 'twitter:image', 'twitter:url' ) ) ) {
					$meta_tags .= '<meta property="' . esc_attr( $name ) . '" content="' . esc_url( $value ) . '" />' . PHP_EOL;
				} else {
					$meta_tags .= '<meta property="' . esc_attr( $name ) . '" content="' . esc_attr( $value ) . '" />' . PHP_EOL;
				}
			}
		}


		return $meta_tags;
	}


	/**
	 * Get meta description for current page
	 *
	 * @return string
	 */
	public function get_current_meta_description( $object_id = null, $context = null )  {

		$meta_description = get_bloginfo( 'description' );

		if ( $object_id && 'post' === $context ) {
			$meta_description = get_seo_description( $object_id );
		} else if ( $object_id && 'term' === $context ) {
			$meta_description = get_seo_description( $object_id );
		} else if ( is_author() && get_queried_object()->description ) {
			$meta_description = get_queried_object()->description;
		}
		return $meta_description;
	}

	/**
	 * Get the Facebook Open Graph meta tags for this page
	 *
	 * @return array Array of meta name to content value
	 */
	public function get_facebook_open_graph_meta_tags( $object_id = false, $context = false ) {
		global $wp;

		// Defaults
		$tags = array(
			'og:site_name'   => get_bloginfo( 'name' ),
			'og:type'        => 'website',
			'og:title'       => get_bloginfo( 'name' ),
			'og:description' => $this->get_current_meta_description( $object_id ),
			'og:url'         => home_url( $wp->request ),
			'og:publisher'   => get_settings_field( 'packaging_preview', 'facebook', 'publisher' ),
			'fb:app_id'      => get_settings_field( 'packaging_preview', 'facebook', 'app_id' ),
		);

		if ( ( $fb_default_image = get_settings_field( 'packaging_preview', 'facebook', 'default_image' ) )
				&& ( $attachment_src = wp_get_attachment_image_src( $fb_default_image, 'full' ) )
				) {
			$tags['og:image'] = $attachment_src[0];
		}

		if ( $fb_publisher = get_settings_field( 'packaging_preview', 'facebook', 'publisher' ) ) {
			$tags['og:publisher'] = $fb_publisher;
		}

		// Single posts
		if ( $object_id && 'post' === $context ) {

			$tags['og:title'] = get_facebook_open_graph_tag( $object_id, 'title' );
			$tags['og:type'] = 'article';
			$tags['og:description'] = get_facebook_open_graph_tag( $object_id, 'description' );
			$tags['og:url'] = get_facebook_open_graph_tag( $object_id, 'url' );

			if ( $image = get_facebook_open_graph_tag( $object_id, 'image' ) ) {
				$tags['og:image'] = $image[0];
				$tags['og:image:width'] = $image[1];
				$tags['og:image:height'] = $image[2];
			}
		}

		// Term pages
		if ( $object_id && 'term' === $context ) {

			$tags['og:title'] = get_facebook_open_graph_tag( $object_id, 'title' );
			$tags['og:description'] = get_facebook_open_graph_tag( $object_id, 'description' );
			$tags['og:url'] = get_facebook_open_graph_tag( $object_id, 'url' );

			if ( $image = get_facebook_open_graph_tag( $object_id, 'image' ) ) {
				$tags['og:image'] = $image[0];
				$tags['og:image:width'] = $image[1];
				$tags['og:image:height'] = $image[2];
			}
		}

		return $tags;
	}

	/**
	 * Get the Twitter card meta tags for this page
	 *
	 * @param int Queried Object ID
	 * @param string "post"|"term"
	 * @return array Array of meta name to content value
	 */
	public function get_twitter_card_meta_tags( $object_id = null, $context = null ) {
		global $wp;

		// Defaults
		$tags = array(
			'twitter:card'        => 'summary',
			'twitter:title'       => get_bloginfo( 'name' ),
			'twitter:description' => $this->get_current_meta_description( $object_id ),
			'twitter:url'         => esc_url( $this->get_request_uri() ),
			);

		if ( $twitter_site = get_settings_field( 'packaging_preview', 'twitter', 'site' ) ) {
			$tags['twitter:site'] = '@' . $twitter_site;
		}

		// Single posts
		if ( $object_id && 'post' === $context ) {

			$tags['twitter:title'] = get_twitter_card_tag( $object_id, 'title' );
			$tags['twitter:description'] = get_twitter_card_tag( $object_id, 'description' );
			$tags['twitter:url'] = get_twitter_card_tag( $object_id, 'url' );

			if ( $image = get_twitter_card_tag( $object_id, 'image' ) ) {
				$tags['twitter:card'] = 'summary_large_image';
				$tags['twitter:image'] = $image;
			}
		}

		// Term pages
		if ( $object_id && 'term' === $context ) {

			$tags['twitter:title'] = get_twitter_card_tag( $object_id, 'title' );
			$twitter_description = get_twitter_card_tag( $object_id, 'description' );
			if ( ! empty( $twitter_description ) ) {
				$tags['twitter:description'] = $twitter_description;
			}
			$tags['twitter:url'] = get_twitter_card_tag( $object_id, 'url' );

			if ( $image = get_twitter_card_tag( $object_id, 'image' ) ) {
				$tags['twitter:card'] = 'summary_large_image';
				$tags['twitter:image'] = $image;
			}
		}

		return $tags;

	}
}
