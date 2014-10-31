<?php

namespace Fusion\Admin;

use Fusion\Config;
use Fusion\Frontend\Distribution_Metadata;
use Fusion\Objects\Post;
use Fusion\Objects\Term;
use Fusion\Utils;

class SEO_Preview {

	private $ver = '1.0';
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
				&& in_array( $current_screen->taxonomy, Fusion()->get_content_taxonomies() )
				&& ! empty( $_GET['tag_ID'] ) ) {
			$queried_term = get_term( absint( $_GET['tag_ID'] ), $current_screen->taxonomy );
			if ( ! $queried_term ) {
				return;
			}
			$context = 'term';
			$obj = Term::get_by_term( $queried_term );
		} else if ( 'term' === $current_screen->base && isset( $current_screen->taxonomy )
				&& in_array( $current_screen->taxonomy, Fusion()->get_content_taxonomies() )
				&& ! empty( $_GET['term_id'] ) ) {
			$queried_term = get_term( absint( $_GET['term_id'] ), $current_screen->taxonomy );
			if ( ! $queried_term ) {
				return;
			}
			$context = 'term';
			$obj = Term::get_by_term( $queried_term );
		} else if ( in_array( $current_screen->base, array( 'post', 'post-new' ) )
				&& in_array( $current_screen->post_type, Fusion()->get_content_post_types() ) ) {
			$context = 'post';
			$obj = Post::get_by_post_id( get_the_ID() );
		}

		if ( empty( $obj ) ) {
			return;
		}

		wp_enqueue_script( 'seo-preview', get_template_directory_uri() . '/assets/js/build/seo-preview.js',
			array( 'jquery', 'backbone', 'media-views', 'utils' ), $this->ver, true
		);

		if ( $obj instanceof Post ) {
			$twitter_username = Distribution_Metadata::get_twitter_username_via( $obj );
		}
		if ( empty( $twitter_username ) ) {
			$twitter_username = Config::get( 'TWITTER_USERNAME' );
		}

		$facebook_share_text = $obj->get_facebook_share_text_for_promotion();
		$facebook_share_text = ! empty( $facebook_share_text ) ? array_shift( $facebook_share_text ) : '';

		$model = array(
			'title'                       => $obj->get_seo_title(),
			'url'                         => $obj->get_permalink(),
			'shortlink'                   => $obj->get_share_link(),
			'desc'                        => $obj->get_seo_description(),
			'image'                       => $obj->get_featured_image_url( 'full' ),
			'twitter_card_title'          => $obj->get_twitter_card_tag( 'title' ),
			'twitter_card_desc'           => Utils::decode_html_entities($obj->get_twitter_card_tag( 'description' )),
			'twitter_card_image'          => $obj->get_twitter_card_tag( 'image' ),
			'twitter_share_text'          => $obj->get_twitter_share_text(),
			'twitter_site_name'           => Distribution_Metadata::get_instance()->get_facebook_open_graph_meta_tags()['og:site_name'],
			'twitter_user_name'           => '@' . $twitter_username,
			'twitter_char_limit'          => FUSION_TWITTER_SHARE_TEXT_MAX_LENGTH,
			'twitter_avatar'              => get_template_directory_uri() . '/assets/images/twitter-avatar.png',
			'twitter_avatar_default'      => get_template_directory_uri() . '/assets/images/twitter-avatar-default.png',
			'facebook_share_text'         => $facebook_share_text,
			'open_graph_title'            => $obj->get_facebook_open_graph_tag( 'title' ),
			'open_graph_desc'             => Utils::decode_html_entities($obj->get_facebook_open_graph_tag( 'description' )),
			'open_graph_image'            => $obj->get_facebook_open_graph_tag( 'image' )[0],
			'open_graph_site_name'        => Distribution_Metadata::get_instance()->get_facebook_open_graph_meta_tags()['og:site_name'],
			'seo_title'                   => $obj->get_seo_title(),
			'seo_desc'                    => Utils::decode_html_entities($obj->get_seo_description()),
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
		echo Fusion()->get_template_part( 'admin/seo-preview/main' );
		echo Fusion()->get_template_part( 'admin/seo-preview/google' );
		echo Fusion()->get_template_part( 'admin/seo-preview/twitter' );
		echo Fusion()->get_template_part( 'admin/seo-preview/facebook' );
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
