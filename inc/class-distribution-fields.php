<?php

namespace Packaging_Preview;

use Packaging_Preview;

class Distribution_Fields {

	private static $instance;

	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new Distribution_Fields;
			self::$instance->setup_actions();
			self::$instance->setup_filters();
		}
		return self::$instance;
	}

	/**
	 * Set up field actions
	 */
	private function setup_actions() {
		add_action( 'init', array( $this, 'action_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
	}

	/**
	 * Set up field filters
	 */
	private function setup_filters() {
		add_filter( 'fm_element_markup_start',       array( $this, 'filter_fm_element_markup_start' ), 10, 2 );
	}

	/**
	 * Do whatever needed after post types have been registered
	 */
	public function action_init() {
		foreach ( Packaging_Preview::post_types() as $post_type ) {
			add_action( "fm_post_{$post_type}", array( $this, 'action_fm_post_content_post_types' ) );
		}
		foreach ( Packaging_Preview::taxonomies() as $taxonomy ) {
			// These fields are lower priority on term pages
			add_action( "fm_term_{$taxonomy}", array( $this, 'action_fm_term_content_taxonomies' ), 20 );
		}
	}

	/**
	 * Register Fieldmanager fields for post context
	 */
	public function action_fm_post_content_post_types() {
		$post_type = substr( current_filter(), 8 );
		$meta_group = $this->register_distribution_fields( 'post', $post_type );
		$meta_group->add_meta_box( esc_html__( 'Packaging', 'packaging-preview' ), $post_type, 'normal', 'high' );
	}

	public function action_fm_term_content_taxonomies() {
		$taxonomy = substr( current_filter(), 8 );
		$meta_group = $this->register_distribution_fields( 'term', $taxonomy );
		$meta_group->add_term_form( esc_html__( 'Packaging', 'packaging-preview' ),
			$taxonomy, false, true );
	}

	/**
	 * Register distribution meta box fields
	 *
	 * @param string $context Fieldmanager "context" value, i.e. "post", "term"
	 * @param string $subcontext Fieldmanager "subcontext", i.e. post type, taxonomy name
	 * @return object Fieldmanager Group
	 */
	private function register_distribution_fields( $context, $subcontext ) {

		/**
		 * Distribution settings
		 */
		$meta_group = new \Fieldmanager_Group( '', array(
			'name'               => 'fusion_distribution',
			'tabbed'             => true,
			) );

		// Can't use $fm_group->add_child(): https://github.com/alleyinteractive/wordpress-fieldmanager/pull/172
		$meta_group->children['facebook'] = new \Fieldmanager_Group( '<i class="icon-facebook-black"></i> ' . esc_html__( 'Facebook', 'packaging-preview' ), array(
			'name'                    => 'facebook',
			'escape'                  => array( 'label' => 'wp_kses_post' ),
			'children'                => array(
				'share_text' => new \Fieldmanager_TextArea( '<strong>' . esc_html__( 'Facebook Share Text', 'packaging-preview' ) . '</strong>', array(
					'escape'          => array( 'label' => 'wp_kses_post' ),
					'attributes'      => array(
						'style'           => 'width:100%',
						),
					'description'     => esc_html__( "Some suggestions for this text: a quote from the article, a second headline, a personal reaction.", 'packaging-preview' ),
					) ),
				'image'               => new \Fieldmanager_Media( '<strong>' . esc_html__( 'Image', 'packaging-preview' ) . '</strong>', array(
					'description'     => __( 'Recrop the Facebook open graph thumbnail if not optimized for sharing.', 'packaging-preview' ),
					'escape'          => array( 'label' => 'wp_kses_post', 'description' => 'wp_kses_post' ),
					'button_label'    => esc_html__( 'Change the social image', 'packaging-preview' ),
					'modal_button_label' => esc_html__( 'Select image', 'packaging-preview' ),
					'modal_title'     => esc_html__( 'Choose image', 'packaging-preview' ),
					) ),
				'title'               => new \Fieldmanager_TextField( '<strong>' . esc_html__( 'Facebook Headline', 'packaging-preview' ) . '</strong>', array(
					'escape'          => array( 'label' => 'wp_kses_post' ),
					'attributes'      => array(
						'style'           => 'width:100%',
						'data-fusion-enable-max-length-countdown' => '',
						'data-fusion-max-length-countdown-placeholder' => 1,
						'maxlength'       => 100,
						)
					) ),
				'description'         => new \Fieldmanager_TextArea( '<strong>' . esc_html__( 'Description', 'packaging-preview' ) . '</strong>', array(
					'description'     => esc_html__( 'Some suggestions for this text: something surprising about the article, a brief opinion, a message with a more pointed voice or spin than the headline.', 'packaging-preview' ),
					'escape'          => array( 'label' => 'wp_kses_post' ),
					'attributes'      => array(
						'style'           => 'width:100%',
						'rows'            => 4,
						'data-fusion-enable-max-length-countdown' => '',
						'data-fusion-max-length-countdown-placeholder' => 1,
						'maxlength'       => 160,
						)
					) )
				),
			) );
		$meta_group->children['twitter'] = new \Fieldmanager_Group( '<i class="icon-twitter-black"></i> ' . esc_html__( 'Twitter', 'packaging-preview' ), array(
			'name'                    => 'twitter',
			'escape'                  => array( 'label' => 'wp_kses_post' ),
			'children'                => array(
				'share_text'          => new \Fieldmanager_TextArea( '<strong>' . esc_html__( 'Twitter Headline', 'packaging-preview' ) . '</strong>', array(
					'description'     => esc_html__( 'This is the tweet when a reader shares this story (defaults to headline + shortlink + @fusion). If the post references an event or topic that has a hashtag, try to include it, and if it includes a person or brand that is on Twitter, use their handle when possible.', 'packaging-preview' ),
					'escape'          => array( 'label' => 'wp_kses_post' ),
					'attributes'      => array(
						'style'           => 'width:100%',
						'data-fusion-enable-max-length-countdown' => '',
						'data-fusion-max-length-countdown-placeholder' => 1,
						'maxlength'       => constant( 'FUSION_TWITTER_SHARE_TEXT_MAX_LENGTH' ),
						)
					) ),
				'image'               => new \Fieldmanager_Media( '<strong>' . esc_html__( 'Image', 'packaging-preview' ) . '</strong>', array(
					'description'     => __( 'Override the featured image if you have a special image or chart you want to share on Twitter that you didn\'t use as the featured image.', 'packaging-preview' ),
					'escape'          => array( 'label' => 'wp_kses_post', 'description' => 'wp_kses_post' ),
					'button_label'    => esc_html__( 'Change the social image', 'packaging-preview' ),
					'modal_button_label' => esc_html__( 'Select image', 'packaging-preview' ),
					'modal_title'     => esc_html__( 'Choose image', 'packaging-preview' ),
					) ),
				'title'               => new \Fieldmanager_TextField( '<strong>' . esc_html__( 'Twitter Card Headline', 'packaging-preview' ) . '</strong>', array(
					'escape'          => array( 'label' => 'wp_kses_post' ),
					'attributes'      => array(
						'style'           => 'width:100%',
						'data-fusion-enable-max-length-countdown' => '',
						'data-fusion-max-length-countdown-placeholder' => 1,
						'maxlength'       => 70,
						)
					) ),
				'description'         => new \Fieldmanager_TextArea( '<strong>' . esc_html__( 'Description', 'packaging-preview' ) . '</strong>', array(
					'description'     => __( 'Descriptions are limited to 200 characters.', 'packaging-preview' ),
					'escape'          => array( 'label' => 'wp_kses_post' ),
					'attributes'      => array(
						'style'           => 'width:100%',
						'maxlength'       => 200,
						'data-fusion-enable-max-length-countdown' => '',
						'data-fusion-max-length-countdown-placeholder' => 1,
						'rows'            => 3,
						)
					) )
				),
			) );
		$seo_group = new \Fieldmanager_Group( '<i class="icon-search"></i> ' . esc_html__( 'SEO', 'packaging-preview' ), array(
			'name'                    => 'seo',
			'escape'                  => array( 'label' => 'wp_kses_post' ),
			'children'                => array(
				'title'          => new \Fieldmanager_TextField( '<strong>' . esc_html__( 'SEO Headline / Title Tag', 'packaging-preview' ) . '</strong>', array(
					'description'     => esc_html__( 'What keywords or phrases would you use to search for this story? Are any of them missing from your headline? Are keywords front-loaded?', 'packaging-preview' ),
					'escape'          => array( 'label' => 'wp_kses_post' ),
					'attributes'      => array(
						'style'           => 'width:100%',
						'maxlength'       => 50,
						'data-fusion-enable-max-length-countdown' => '',
						'data-fusion-max-length-countdown-placeholder' => 1,
						)
					) ),
				'description'         => new \Fieldmanager_TextArea( '<strong>' . esc_html__( 'Description', 'packaging-preview' ) . '</strong>', array(
					'escape'          => array( 'label' => 'wp_kses_post' ),
					'attributes'      => array(
						'style'           => 'width:100%',
						'rows'            => 2,
						'maxlength'       => 160,
						'data-fusion-enable-max-length-countdown' => '',
						'data-fusion-max-length-countdown-placeholder' => 1,
						)
					) ),
			),
		) );

		// Google News fields are only applicable to Posts, not to Terms
		if ( 'post' === $context ) {
			$seo_group->children['keywords'] = new \Fieldmanager_TextField( '<strong>' . esc_html__( 'Google News Keywords', 'packaging-preview' ) . '</strong>', array(
				'name'            => 'keywords',
				'description'     => __( 'These keywords (up to 10, separated by commas) should answer the "who, what, and where" of the story, and include any potential misspellings of those keywords.', 'packaging-preview' ),
				'escape'          => array( 'label' => 'wp_kses_post', 'description' => 'wp_kses_post' ),
				'attributes'      => array(
					'style'           => 'width:100%',
					)
				) );
			$seo_group->children['standout'] = new \Fieldmanager_Checkbox( '<strong>' . esc_html__( 'Google News Standout', 'packaging-preview' ) . '</strong>', array(
				'name'            => 'standout',
				'escape'          => array( 'label' => 'wp_kses_post' ),
				) );
		}

		$meta_group->children['seo'] = $seo_group;

		/**
		 * Allow filtering of the fields here.
		 *
		 * Themes can use this filter to add additional settings fields and
		 * preview panels. Make sure to register them on the same
		 * fm_{context}_{subcontext} hooks that the form is registered on.
		 *
		 * @param Fieldmanager_Group All fields in theis group
		 * @param string Fieldmanager context, ie "post" or "term"
		 * @param string Fieldmanager subcontext, ie post type or taxonomy slug
		 */
		$meta_group = apply_filters( 'packaging_preview_settings_fields', $meta_group, $context, $subcontext );

		return $meta_group;

	}

	/**
	 * Filter markup to include placeholders specific to this post or term
	 */
	public function filter_fm_element_markup_start( $out, $fm ) {

		$screen = get_current_screen();
		if ( ! $screen ) {
			return $out;
		} else if ( 'post' === $screen->base ) {
			if ( ! in_array( $screen->post_type, Fusion()->get_post_types() ) ) {
				return $out;
			}
			$obj = \Fusion\Objects\Post::get_by_post_id( get_the_ID() );
			if ( ! $obj ) {
				return $out;
			}
		} else if ( 'edit-tags' == $screen->base && ! empty ( $screen->taxonomy ) && ! empty( $_GET['tag_ID'] ) ) {
			$queried_term = get_term( absint( $_GET['tag_ID'] ), $screen->taxonomy );
			if ( ! $queried_term ) {
				return $out;
			}
			$obj = \Fusion\Objects\Term::get_by_term( $queried_term );
		} else if ( 'term' == $screen->base && ! empty ( $screen->taxonomy ) && ! empty( $_GET['term_id'] ) ) {

			// for compat with WP core changeset https://core.trac.wordpress.org/changeset/36308
			$queried_term = get_term( absint( $_GET['term_id'] ), $screen->taxonomy );
			if ( ! $queried_term ) {
				return $out;
			}
			$obj = \Fusion\Objects\Term::get_by_term( $queried_term );
		} else {
			return $out;
		}

		$fm_tree = $fm->get_form_tree();
		array_pop( $fm_tree );
		$parent = array_pop( $fm_tree );

		if ( $parent ) {

			if ( 'facebook' === $parent->name ) {
				$placeholders = array(
					'title'        => $obj->get_default_facebook_open_graph_tag( 'title' ),
				);
			} else if ( 'twitter' === $parent->name ) {
				$placeholders = array(
					'share_text'   => $obj->get_twitter_share_text(),
					'title'        => $obj->get_default_twitter_card_tag( 'title' ),
					'description'  => $obj->get_default_twitter_card_tag( 'description' ),
				);
			} else if ( 'seo' === $parent->name ) {
				$placeholders = array(
					'title'        => $obj->get_default_seo_title(),
					'description'  => $obj->get_default_seo_description(),
					'keywords'     => $obj instanceof Post ? $obj->get_default_seo_keywords() : '',
					);

				if ( 'standout' === $fm->name ) {
					/* Values for standout checkbox */
					$standout_disabled = true;
					$current_standouts = Fusion()->content_model->get_last_week_standouts();
					if ( $obj->is_google_standout_enabled() ) {
						$standout_description = 'This post is already a standout. Hurrah!';
					} else if ( count( $current_standouts ) < 7 ) {
						$standout_description = __( 'It\'s time for another standout. Reserved for growth editor use only.', 'packaging-preview' );
						$standout_disabled = false;
					} else {
						$standout_description = __( 'Egads! There are already 7 standout posts in the last week. Give it a couple days.', 'packaging-preview' );
						$standout_disabled = true;
					}

					if ( ! current_user_can( 'edit_theme_options' ) ) {
						$fm->attributes['style'] = 'display:none';
						$fm->label = false;
					} else {
						$fm->description = esc_html( $standout_description );
						if ( $standout_disabled ) {
							$fm->attributes['disabled'] = 'disabled';
						}
					}

				}
			}

			if ( isset( $placeholders[ $fm->name ] ) ) {
				$fm->attributes['placeholder'] = $placeholders[ $fm->name ];
			}
		}

		return $out;
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function action_admin_enqueue_scripts() {
		$screen = get_current_screen();

		if ( ( $screen->base != 'term' ) && ( $screen->base != 'post' ) ) {
			return;
		}

		wp_enqueue_script( 'fusion-distribution-fields',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/src/admin-media-attached.js',
			array( 'media-editor', 'media-views' )
		);

	}

}
