<?php

namespace Packaging_Preview;

class Distribution_Metadata {

	private static $instance;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Distribution_Metadata;
			self::$instance->setup_actions();
			self::$instance->setup_filters();
		}
		return self::$instance;
	}

	/**
	 * Set up distribution metadata actions
	 */
	private function setup_actions() {
		add_action( 'wp_head', array( $this, 'action_wp_head_social_meta_tags' ) );
		add_action( 'wp_head', array( $this, 'action_wp_head_feed_meta_tags' ) );
		add_action( 'wp_head', array( $this, 'action_wp_head_rel_canonical' ), 9 );
		add_action( 'wp_head', array( $this, 'action_wp_head_no_index_no_follow' ) );
		add_action( 'query_vars', array( $this, 'action_query_vars_add_element' ) );
	}

	/**
	 * Set up distribution metadata filters
	 */
	private function setup_filters() {
		// `wp_title` filter; used for WP up through 4.3.1. Can be removed once VIP is no longer running this version
		add_filter( 'wp_title', array( $this, 'filter_wp_title' ) );

		// These filters are used for WP >= 4.4. Used for local and staging environments and in testing for now.
		add_filter( 'document_title_separator', array( $this, 'filter_document_title_separator' ) );
		add_filter( 'document_title_parts',     array( $this, 'filter_document_title_parts'     ) );
	}

	/**
	 * Add meta tags to the head of our site
	 */
	public function action_wp_head_social_meta_tags() {
		$queried_obj = $shared_element = false;

		if ( is_single() || is_page() || is_singular() ) {
			$queried_obj = $this->get_current_post();
			$shared_element = $this->get_current_object();
		} else if ( is_tax( Fusion()->get_content_taxonomies() ) ) {
			$queried_obj = $this->get_current_term();
		}

		echo $this->get_social_meta_tags( $queried_obj, $shared_element );
		echo Fusion()->get_template_part( 'header/favicons' );
	}

	/**
	 * Get all the social meta tags for a request
	 *
	 * @param Post|Term|null $queried_obj
	 * @param Post|null $shared_element Set from element share links
	 * @return string HTML representing all social distribution meta tags
	 */
	public function get_social_meta_tags( $queried_obj = null, $shared_element = null ) {

		$meta_tags = '';

		$meta_tags .= '<meta name="description" content="' . esc_attr( $this->get_current_meta_description( $queried_obj ) ) . '" />' . PHP_EOL;

		if ( $queried_obj && $queried_obj instanceof Post
				&& in_array( $queried_obj->get_type(), Fusion()->get_content_post_types() )) {
			$meta_tags .= '<meta name="news_keywords" content="' . esc_attr( $queried_obj->get_seo_keywords() ) . '" />' . PHP_EOL;

			if ( $queried_obj->is_google_standout_enabled() ) {
				$meta_tags .= '<link rel="standout" href="' . esc_attr( $queried_obj->get_permalink() ) . '" />' . PHP_EOL;
			}
		}

		$facebook_tags = $this->get_facebook_open_graph_meta_tags( $queried_obj, $shared_element );
		$twitter_tags = $this->get_twitter_card_meta_tags( $queried_obj, $shared_element );

		$tags = array_merge( array( 'fb:app_id' => Config::get( 'FACEBOOK_APPID' ) ), $facebook_tags, $twitter_tags );

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

	public function action_wp_head_feed_meta_tags() {

		$main_rss_url = get_feed_link();
		$main_feed_topic = "Fusion.net";

		echo '<link href="' . esc_url( $main_rss_url ) . '" rel="alternate" type="application/rss+xml" title="' . esc_attr( sprintf( __( 'Feed for %s' ), $main_feed_topic ) ) . '" />' . PHP_EOL;

		if ( is_author() || is_tax() ) {
			$queried_object = get_queried_object();

			if ( is_author() ) {
				$author = Author::get_by_coauthor( $queried_object );
				$url = $author->get_feed_link();
				$title = $author->get_display_name();
			} elseif ( is_tax() ) {
				$term = Term::get_by_term( $queried_object );
				$url = $term->get_feed_link();
				$title = $term->get_name();
			}

			echo '<link href="' . esc_url( $url ) . '" rel="alternate" type="application/rss+xml" title="' . esc_attr( sprintf( __( 'Feed for %s' ), $title ) ) . '" />'  . PHP_EOL;
		}

	}

	/**
	 * Add rel=canonical to archive views, as WP doesn't do this by default
	 */
	public function action_wp_head_rel_canonical() {

		$rel_canonical = false;
		if ( is_tax() ) {
			$term = Term::get_by_term( get_queried_object() );
			$rel_canonical = $term->get_permalink();
		} else if ( is_author() ) {
			$author = Author::get_by_coauthor( get_queried_object() );
			$rel_canonical = $author->get_permalink();
		} else if ( is_post_type_archive() ) {
			$rel_canonical = get_post_type_archive_link( get_query_var( 'post_type' ) );
		} else if ( get_query_var( 'fusion-static-page' ) ) {
			$rel_canonical = home_url( trailingslashit( get_query_var( 'fusion-static-page' ) ) );
		} else if ( is_home() ) {
			$rel_canonical = home_url( '/' );
		} else if ( is_singular( 'fusion_video' ) ) {
			// If a video is linked to at least one post,
			// set the first post linked as the canonical URL
			$video = Video::get_by_post_id( get_queried_object_id() );
			$linked_posts = $video->get_linked_posts();
			if( count( $linked_posts ) ) {
				$top_post = array_shift( $linked_posts );
				$rel_canonical = $top_post->get_permalink();
			}
		}

		if ( ! empty( $rel_canonical ) ) {
			// If we're setting our own canonical tag, make sure WP doesn't set it's own
			remove_action( 'wp_head', 'rel_canonical' );
			echo '<link rel="canonical" href="' . esc_url( $rel_canonical ) . '" />' . PHP_EOL;
		}

	}

	/**
	 * Add noindex,nofollow to date archives, content type archives, and other blocked views
	 */
	public function action_wp_head_no_index_no_follow() {
		if ( is_date()
			|| is_post_type_archive()
			|| is_search()
			|| ( is_singular() && get_queried_object()->post_status == 'unindexed' )
			) {
			echo '<meta name="robots" content="noindex,follow">';
		} else if ( is_singular( 'fusion_sponsored' ) || is_tax( 'fusion_client' ) ) {
			echo '<meta name="robots" content="noindex,nofollow">';
		}
	}

	/**
	 * Filter the title on single posts
	 */
	public function filter_wp_title( $wp_title ) {

		if ( is_singular( 'fusion_show' ) ) {
			$show_obj = new Show( get_queried_object() );
			$wp_title = sprintf( '%s | Fusion', $show_obj->get_seo_title() );
		} else if ( is_singular( 'fusion_sponsored' ) ) {
			$post_obj = Post::get_by_post_id( get_queried_object_id() );
			$wp_title = sprintf( '%s | Fusion', $post_obj->get_seo_title() );
		}else if ( is_singular( Fusion()->get_content_post_types() ) ) {
			$post_obj = Post::get_by_post_id( get_queried_object_id() );
			$wp_title = sprintf( '%s | Fusion', $post_obj->get_seo_title() );
		} else if ( is_page() ) {
			$page_obj = Page::get_by_post_id( get_queried_object_id() );
			$wp_title = sprintf( '%s | Fusion', $page_obj->get_seo_title() );
		} else if ( 'schedule' === get_query_var( 'fusion-static-page' ) ) {
			$wp_title = 'Schedule | News. Pop Culture. Satire. | Fusion';
		} else if ( 'standout' === get_query_var( 'fusion-static-page' ) ) {
			$wp_title = 'Standout Stories | News. Pop Culture. Satire. | Fusion';
		} else if ( 'authors' === get_query_var( 'fusion-static-page' ) ) {
			$wp_title = 'Authors | Fusion';
		} else if ( is_tax() ) {
			$wp_title = sprintf( '%s | News. Pop Culture. Satire. | Fusion', get_queried_object()->name );
		} else if ( is_search() ) {
			$wp_title = sprintf( 'Search – %s | News. Pop Culture. Satire. | Fusion', get_search_query() );
		} else if ( is_author() ) {
			$wp_title = sprintf( '%s | Fusion', get_queried_object()->display_name );
		} else {
			$wp_title = 'Fusion | Pop culture. Satire. News.';
		}

		return apply_filters( 'fusion_wp_title', strip_tags( $wp_title ) );
	}

	/**
	 * Filter the separator used in the document title tag.
	 *
	 * Default is an en dash. We're using a vertical pipe for consistency.
	 */
	public function filter_document_title_separator( $sep ) {
		$sep = '|';
		return $sep;
	}

	/**
	 * Filter the document title parts on single posts
	 *
	 * @param array $title_parts Document title parts: $title, $page, $tagline, $site
	 */
	public function filter_document_title_parts( $title_parts ) {

		// Use the SEO title property where it's available for single content
		if ( is_singular( array_merge( Fusion()->get_content_post_types(), array( 'page', 'fusion_show', 'fusion_sponsored' ) )) ) {
			$post_obj = Post::get_by_post_id( get_queried_object_id() );
			$title_parts['title'] = $post_obj->get_seo_title();
		} else if ( is_page() ) {
			$page_obj = Page::get_by_post_id( get_queried_object_id() );
			$title_parts['title'] = $page_obj->get_seo_title();
		} else if ( 'schedule' === get_query_var( 'fusion-static-page' ) ) {
			$title_parts['title'] = 'Schedule';
		} else if ( 'standout' === get_query_var( 'fusion-static-page' ) ) {
			$title_parts['title'] = 'Standout Stories';
		} else if ( 'authors' === get_query_var( 'fusion-static-page' ) ) {
			$title_parts['title'] = 'Authors';
		} else if ( is_search() ) {
			$title_parts['title'] = sprintf( 'Search - %s', get_search_query() );
		}

		// Add a tagline for archive views where there should be one
		if ( get_query_var( 'fusion-static-page' ) || is_tax() || is_search() ) {
			$title_parts['tagline'] = 'News. Pop Culture. Satire.';
		} else if ( is_home() ) {
			$title_parts['tagline'] = 'Pop culture. Satire. News.';
		}

		// For all but homepage, we want the tagline before the title
		if ( ! is_home() ) {
			$title_parts_order = array( 'title', 'page', 'tagline', 'site' );

			uksort( $title_parts, function( $a, $b ) use ( $title_parts_order ) {
				$first_position = array_search( $a, $title_parts_order );
				$second_position = array_search( $b, $title_parts_order );

				// PHP 7 spaceship operator couldn't come soon enough for this
				if ( false === $first_position ) return 1;
				if ( false === $second_position ) return -1;
				return ( $first_position < $second_position ) ? -1 :
					( ( $first_position > $second_position ) ? 1 : 0 );
			} );
		}

		return apply_filters( 'fusion_document_title_parts', array_map( 'strip_tags', $title_parts ) );
	}

	/**
	 * Get meta description for current page
	 *
	 * @param Post|Term|null Currently-queried object.
	 * @return string
	 */
	public function get_current_meta_description( $obj = null )  {

		$meta_description = get_bloginfo( 'description' );
		if ( $obj instanceof Post || $obj instanceof Term ) {
			$meta_description = $obj->get_seo_description();
		} else if ( is_author() && get_queried_object()->description ) {
			$meta_description = get_queried_object()->description;
		} else if ( 'schedule' === get_query_var( 'fusion-static-page' ) ) {
			$meta_description = 'Fusion is a news, pop culture, and satire TV and digital network. We have a complete 4 hours of live shows everyday that engages and champions a young, diverse, and inclusive America. The shows consist of a unique mix of smart and irreverent original reporting, lifestyle, and comedic content. Some of our favorite hosts include: Alicia Menendez, Jorge Ramos, Nando Villa, Simon Carr, Mariana Atencio, and Pedro Andrade.';
		}  else if ( 'standout' === get_query_var( 'fusion-static-page' ) ) {
			$meta_description = 'Fusion is a news, pop culture, and satire TV and digital network. See some of our standout content.';
		} else if ( 'authors' === get_query_var( 'fusion-static-page' ) ) {
			$meta_description = 'Fusion is a news, pop culture, and satire TV and digital network.';
		}
		return $meta_description;
	}

	/**
	 * Get the Facebook Open Graph meta tags for this page
	 *
	 * @param Post|Term|null $queried_obj
	 * @param Post|null $shared_element Set from element share links
	 * @return array Array of meta name to content value
	 */
	public function get_facebook_open_graph_meta_tags( $queried_obj = false, $shared_element = false ) {
		global $wp;

		// Defaults
		$tags = array(
			'og:site_name'   => get_bloginfo( 'name' ),
			'og:type'        => 'website',
			'og:title'       => get_bloginfo( 'name' ),
			'og:description' => $this->get_current_meta_description( $queried_obj ),
			'og:url'         => home_url( $wp->request ),
			'og:image'       => get_template_directory_uri() . '/assets/images/fusion_logo.png',
		);

		// Single posts
		if ( $queried_obj && $queried_obj instanceof Post ) {

			$tags['og:title'] = $queried_obj->get_facebook_open_graph_tag( 'title' );
			$tags['og:type'] = 'article';
			$tags['og:description'] = $queried_obj->get_facebook_open_graph_tag( 'description' );

			// Override some share values if an element on the page is being shared
			if ( $shared_element && $shared_element->get_id() !== $queried_obj->get_id() ) {
				$tags['og:url'] = add_query_arg( 'element', $shared_element->get_id(),
					$queried_obj->get_facebook_open_graph_tag( 'url' ) );
			} else {
				$tags['og:url'] = $queried_obj->get_facebook_open_graph_tag( 'url' );
				$shared_element = $queried_obj;
			}

			if ( $image = $shared_element->get_facebook_open_graph_tag( 'image' ) ) {
				$tags['og:image'] = $image[0];
				$tags['og:image:width'] = $image[1];
				$tags['og:image:height'] = $image[2];
			}

			if ( $article_author = $shared_element->get_authors_facebook_urls() ) {
				$tags['article:author'] = $article_author;
			}
		}

		// Term pages
		if ( $queried_obj && $queried_obj instanceof Term ) {

			$tags['og:title'] = $queried_obj->get_facebook_open_graph_tag( 'title' );
			$og_description = $queried_obj->get_facebook_open_graph_tag( 'description' );
			if ( ! empty( $og_description ) ) {
				$tags['og:description'] = $og_description;
			}
			$tags['og:url'] = $queried_obj->get_facebook_open_graph_tag( 'url' );
			if ( $image = $queried_obj->get_facebook_open_graph_tag( 'image' ) ) {
				$tags['og:image'] = $image[0];
				$tags['og:image:width'] = $image[1];
				$tags['og:image:height'] = $image[2];
			}
		}

		if ( 'newsletter-subscribe' === get_query_var( 'fusion-static-page' ) ) {
			$overrides = get_option( 'fusion_newsletter_fields' );
			if ( ! empty( $overrides['facebook']['title'] ) ) {
				$tags['og:title'] = $overrides['facebook']['title'];
			}
			if ( ! empty( $overrides['facebook']['description'] ) ) {
				$tags['og:description'] = $overrides['facebook']['description'];
			}
			if ( ! empty( $overrides['facebook']['image'] ) ) {
				$attachment = Attachment::get_by_post_id( (int) $overrides['facebook']['image'] );
				if ( $attachment && 'attachment' == $attachment->get_type() ) {
					$tags['og:image'] = $attachment->get_url( 'facebook-open-graph' );
				}
			}
		}

		return $tags;
	}

	/**
	 * Get the Twitter card meta tags for this page
	 *
	 * @param Post|Term|null $queried_obj
	 * @param Post|null $shared_element Set from element share links
	 * @return array Array of meta name to content value
	 */
	public function get_twitter_card_meta_tags( $queried_obj = null, $shared_element = null ) {

		// Defaults
		$tags = array(
			'twitter:card'        => 'summary',
			'twitter:site'        => '@' . Config::get( 'TWITTER_USERNAME' ),
			'twitter:title'       => get_bloginfo( 'name' ),
			'twitter:description' => $this->get_current_meta_description( $queried_obj ),
			'twitter:url'         => esc_url( home_url( Fusion()->get_request_uri() ) ),
			);

		// Single posts
		if ( $queried_obj && $queried_obj instanceof Post ) {

			$tags['twitter:title'] = $queried_obj->get_twitter_card_tag( 'title' );
			$tags['twitter:description'] = $queried_obj->get_twitter_card_tag( 'description' );

			// Override some share values if an element on the page is being shared
			if ( $shared_element && $shared_element->get_id() !== $queried_obj->get_id() ) {
				$tags['twitter:url'] = add_query_arg( 'element', $shared_element->get_id(),
					$queried_obj->get_twitter_card_tag( 'url' ) );
			} else {
				$tags['twitter:url'] = $queried_obj->get_twitter_card_tag( 'url' );
				$shared_element = $queried_obj;
			}

			if ( $image = $shared_element->get_twitter_card_tag( 'image' ) ) {
				$tags['twitter:card'] = 'summary_large_image';
				$tags['twitter:image'] = $image;
			}
		}

		// Term pages
		if ( $queried_obj && $queried_obj instanceof Term ) {

			$tags['twitter:title'] = $queried_obj->get_twitter_card_tag( 'title' );
			$twitter_description = $queried_obj->get_twitter_card_tag( 'description' );
			if ( ! empty( $twitter_description ) ) {
				$tags['twitter:description'] = $twitter_description;
			}
			$tags['twitter:url'] = $queried_obj->get_twitter_card_tag( 'url' );

			if ( $image = $queried_obj->get_twitter_card_tag( 'image' ) ) {
				$tags['twitter:card'] = 'summary_large_image';
				$tags['twitter:image'] = $image;
			}
		}

		return $tags;

	}


	/**
	 * Get the currently queried post.
	 *
	 * @return Fusion\Objects\Post
	 */
	public function get_current_post() {

		if ( ! is_singular() ) {
			return null;
		}

		return Post::get_by_post_id( get_queried_object_id() );
	}


	/**
	 * Get the currently queried term.
	 *
	 * @return Fusion\Objects\Term
	 */
	public function get_current_term() {
		if ( ! is_tax( Fusion()->get_taxonomies() ) ) {
			return;
		}

		return Term::get_by_term( get_queried_object() );
	}

	/**
	 * Get the currently active post element (attachment, etc.).
	 *
	 * If a valid post ID is passed as the "element" query param (as in an
	 * atomic share link of an image from a gallery or comic), that element
	 * will be returned. Otherwise, returns the current queried post.
	 *
	 * @return Fusion\Objects\Post
	 */
	public function get_current_object() {
		if ( ! is_singular() ) {
			return null;
		}

		$obj = null;

		if ( $element_id = get_query_var( 'element' ) ) {
			$obj = Post::get_by_post_id( absint( $element_id ) );
		}

		// If no object or custom object is not published - default to current queried object.
		if ( ! $obj || ! in_array( $obj->get_status(), array( 'publish', 'inherit' ) ) ) {
			$obj = $this->get_current_post();
		}

		return $obj;
	}

	/**
	 * Filter query vars to whitelist 'element'
	 *
	 * @param  array $query_vars [description]
	 * @return  array $query_vars [description]
	 */
	function action_query_vars_add_element( $query_vars ){
		$query_vars[] = 'element';
		return $query_vars;
	}

	/**
	 * Get the Twitter username to display in "via @username"
	 *
	 * @uses Post::get_primary_vertical()
	 * @uses Vertical::get_social_accounts()
	 * @param object $post Fusion Post object
	 * @return string
	 */
	public static function get_twitter_username_via( $obj ) {
		if ( ! $obj ) {
			return '';
		}

		$primary_vertical = $obj->get_primary_vertical();

		if ( $primary_vertical ) {
			$social_accounts = $primary_vertical->get_social_accounts();
			if ( ! empty( $social_accounts['twitter'] ) ) {
				return $social_accounts['twitter'];
			}
		}

		return Config::get( 'TWITTER_USERNAME' );
	}

}
