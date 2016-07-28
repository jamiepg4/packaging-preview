<?php

namespace Packaging_Preview;

use Packaging_Preview;

class SEO_Preview {

	private static $instance;

	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new SEO_Preview;
			self::$instance->load();
		}
		return self::$instance;
	}

	private function load() {

		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
		add_action( 'admin_footer', array( $this, 'action_admin_footer' ) );
		add_action( 'wp_ajax_seo_preview_get_image_src', array( $this, 'ajax_callback_image_src' ) );

	}

	public function action_admin_enqueue_scripts( $hook ) {

		$current_screen = get_current_screen();

		if ( 'edit-tags' === $current_screen->base && isset( $current_screen->taxonomy )
				&& in_array( $current_screen->taxonomy, Packaging_Preview::$taxonomies )
				&& ! empty( $_GET['tag_ID'] ) ) {
			$queried_term = get_term( absint( $_GET['tag_ID'] ), $current_screen->taxonomy );
			if ( ! $queried_term ) {
				return;
			}
			$context = 'term';
			$object_id = $queried_term->term_id;
		} else if ( 'term' === $current_screen->base && isset( $current_screen->taxonomy )
				&& in_array( $current_screen->taxonomy, Packaging_Preview::$taxonomies )
				&& ! empty( $_GET['term_id'] ) ) {
			$queried_term = get_term( absint( $_GET['term_id'] ), $current_screen->taxonomy );
			if ( ! $queried_term ) {
				return;
			}
			$context = 'term';
			$object_id = $queried_term->term_id;
		} else if ( in_array( $current_screen->base, array( 'post', 'post-new' ) )
				&& in_array( $current_screen->post_type, Packaging_Preview::$post_types ) ) {
			$context = 'post';
			$object_id = get_the_ID();
		}

		if ( empty( $context ) || empty( $object_id ) ) {
			return;
		}

		wp_enqueue_script( 'seo-preview', plugin_dir_url( dirname( __FILE__ ) ) . '/assets/js/build/seo-preview.js',
			array( 'jquery', 'backbone', 'media-views', 'utils' ), $this->ver, true
		);

		wp_enqueue_style( 'seo-preview', plugin_dir_url( dirname( __FILE__ ) ) . '/assets/css/seo-preview.css' );

		//if ( $obj instanceof Post ) {
			//$twitter_username = Distribution_Metadata::get_twitter_username_via( $obj );
		//}
		//if ( empty( $twitter_username ) ) {
			//$twitter_username = Config::get( 'TWITTER_USERNAME' );
		//}

		$model = array(
			'title'                       => Packaging_Preview\get_seo_title( $object_id ),
			'url'                         => get_permalink( $object_id ),
			'shortlink'                   => Packaging_Preview\get_share_link( $object_id ),
			'desc'                        => Packaging_Preview\get_seo_description( $object_id ),
			'image'                       => Packaging_Preview\get_featured_image_url( $object_id, 'full' ),
			'twitter_card_title'          => Packaging_Preview\get_twitter_card_tag( $object_id, 'title' ),
			'twitter_card_desc'           => Packaging_Preview\get_twitter_card_tag( $object_id, 'description' ),
			'twitter_card_image'          => Packaging_Preview\get_twitter_card_tag( $object_id, 'image' ),
			'twitter_share_text'          => Packaging_Preview\get_twitter_share_text( $object_id ),
			'twitter_site_name'           => Distribution_Metadata::get_instance()->get_facebook_open_graph_meta_tags()['og:site_name'],
			'twitter_user_name'           => '@' . $twitter_username,
			'twitter_char_limit'          => FUSION_TWITTER_SHARE_TEXT_MAX_LENGTH,
			'twitter_avatar'              => get_template_directory_uri() . '/assets/images/twitter-avatar.png',
			'twitter_avatar_default'      => get_template_directory_uri() . '/assets/images/twitter-avatar-default.png',
			'facebook_share_text'         => Packaging_Preview\get_facebook_share_text_for_promotion( $object_id ),
			'open_graph_title'            => Packaging_Preview\get_facebook_open_graph_tag( $object_id, 'title' ),
			'open_graph_desc'             => Packaging_Preview\get_facebook_open_graph_tag( $object_id, 'description' ),
			'open_graph_image'            => Packaging_Preview\get_facebook_open_graph_tag( $object_id, 'image' ),
			'open_graph_site_name'        => Distribution_Metadata::get_instance()->get_facebook_open_graph_meta_tags()['og:site_name'],
			'seo_title'                   => Packaging_Preview\get_seo_title( $object_id ),
			'seo_desc'                    => Packaging_Preview\get_seo_description( $object_id ),
		);

		/**
		 * Some attributes can default to the value
		 * of another if nothing is provided.
		 */
		$map = array(
			'twitter_card_title'          => 'title',
			'twitter_card_desc'           => 'open_graph_desc',
			'twitter_card_image'          => 'open_graph_image',
			'twitter_share_text'          => 'title',
			'open_graph_title'            => 'title',
			'open_graph_desc'             => '',
			'open_graph_image'            => 'image',
			'seo_title'                   => 'title',
			'seo_desc'                    => 'open_graph_desc',
		);

		// If value == default, unset.
		foreach( $map as $original => $default ) {
			if ( ! empty( $default ) && $model[ $original ] === $model[ $default ] ) {
				$model[$original] = null;
			}
		}

		wp_localize_script( 'seo-preview', 'fusionSeoPreviewData', array(
			'context' => $context,
			'model' => $model,
			'imagePreviewNonce' => wp_create_nonce( 'fusion-seo-preview-image' ),
			'publishWarning' => __( 'Please edit the Twitter share text to fewer than 92 characters. ' . "\n\n" .
							'Otherwise the share text will appear as ' . "\n\n" ),
		) );

	}

	public function action_admin_footer() {
		require_once dirname( dirname( __FILE__ ) ) . '/templates/main.php';
		require_once dirname( dirname( __FILE__ ) ) . '/templates/google.php';
		require_once dirname( dirname( __FILE__ ) ) . '/templates/twitter.php';
		require_once dirname( dirname( __FILE__ ) ) . '/templates/facebook.php';
	}

	public function ajax_callback_image_src() {
		if ( ! current_user_can( 'edit_posts' ) || ! wp_verify_nonce( $_POST['nonce'], 'fusion-seo-preview-image' ) ) {
			exit;
		}

		if ( isset( $_POST['image_id'] ) ) {
			$image = wp_get_attachment_image_src( absint( $_POST['image_id'] ), 'full' );
			if ( ! empty( $image ) && ! is_wp_error( $image ) ) {
				wp_send_json_success( array(
					'image_src' => reset( $image )
				) );
			}
			die;
		}
		die;
	}

}
