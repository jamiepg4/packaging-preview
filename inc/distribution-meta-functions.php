<?php

namespace Packaging_Preview;

/**
 * Get the share link for the post
 *
 * @return string
 */
function get_share_link( $post_id ) {
	return wp_get_shortlink( $post_id );
}

function get_featured_image_url( $post_id ) {
	if ( $featured_image = get_post_thumbnail_id( $post_id ) ) {
		$attachment_image = wp_get_attachment_image_src( absint( $featured_image ) );

		return $attachment_image[0];
	}
}

/**
 * Get the SEO keywords for the post
 *
 * @return string
 */
function get_seo_keywords( $post_id ) {
	$seo_keywords = get_post_field( $post_id, 'fusion_distribution', 'seo', 'keywords' );

	if ( empty( $seo_keywords ) ) {
		$terms = array();
		foreach ( Packaging_Preview::$taxonomies as $taxonomy ) {
			$terms = $terms + wp_list_pluck( get_the_terms( $post_id, 'post_tag' ), 'name' );
		}

		$seo_keywords = implode( ', ', $terms );
	}

	return $seo_keywords;
}

/**
 * Set the standout status for the post
 *
 * @param boolean
 */
function set_google_standout_status( $post_id,  $status ) {
	$current_standouts = get_last_week_standouts();
	//if ( empty( $current_standouts ) || count( $current_standouts ) < 7 ) {
		//set_post_field( $post_id, 'fusion_distribution', 'seo', 'standout', $status );
		//Fusion()->content_model->add_post_to_standouts( get_id( $post_id ) );
	//}
}

/**
 * Get the standout status for the post
 *
 * @param string
 */
function is_google_standout_enabled( ) {
	return get_post_field( $post_id, 'fusion_distribution', 'seo', 'standout' );
}

/**
 * Get the SEO title for the post
 *
 * @return string
 */
function get_seo_title( $post_id ) {
	$seo_title = get_post_field( $post_id, 'fusion_distribution', 'seo', 'title' );

	if ( ! $seo_title ) {
		$seo_title = get_the_title( $post_id );
	}

	return decode_html_entities( $seo_title );
}

/**
 * Set the SEO title
 *
 * @param string
 */
function set_seo_title( $post_id, $title ) {
	return set_post_field( $post_id, 'fusion_distribution', 'seo', 'title', $title );
}

/**
 * Get the SEO description for the post
 *
 * @return string
 */
function get_seo_description( $post_id ) {
	$seo_description = get_post_field( $post_id, 'fusion_distribution', 'seo', 'description' );

	if ( empty( $seo_description ) ) {
		$seo_description = get_first_sentence_from_post_content( $post_id );
	}

	return decode_html_entities( $seo_description );
}

/**
 * Set the SEO description for the post
 *
 * @param string
 */
function set_seo_description( $post_id, $description ) {
	set_post_field( $post_id, 'fusion_distribution', 'seo', 'description', $description );
}


/**
 * Get a given Facebook open graph tag for this post
 *
 * @param string $tag_name
 * @return string
 */
function get_facebook_open_graph_tag( $post_id, $tag_name ) {

	switch ( $tag_name ) {

		case 'title':
			$val = get_post_field( $post_id, 'fusion_distribution', 'facebook', 'title' );
			if ( empty( $val ) ) {
				$val = get_seo_title( $post_id );
			}
			break;

		case 'description':
			$val = get_post_field( $post_id, 'fusion_distribution', 'facebook', 'description' );
			if ( empty( $val ) ) {
				$val = get_seo_description( $post_id );
			}
			break;

		case 'image':
			$image_id = get_post_field( $post_id, 'fusion_distribution', 'facebook', 'image' );

			if ( empty( $image_id ) ) {
				$image_id = get_post_thumbnail_id( $post_id );
			}

			if ( intval( $image_id ) > 0 ) {
				$val = wp_get_attachment_image_src( 'facebook-open-graph', array( 'width' => 1200, 'height' => 630 ) )[0];
			}
			break;

		default:
			break;
	}

	if ( in_array( $tag_name, array( 'title', 'description' ) ) ) {
		$val = decode_html_entities( $val );
	}
	return $val;
}

/**
 * Set a given Facebook open graph tag for this post
 *
 * @param string $tag_name
 * @param mixed $value
 */
function set_facebook_open_graph_tag( $post_id, $tag_name, $value ) {
	switch ( $tag_name ) {
		case 'title':
			set_post_field( $post_id, 'fusion_distribution', 'facebook', 'title', $value );
			break;
		case 'description':
			set_post_field( $post_id, 'fusion_distribution', 'facebook', 'description', $value );
			break;
		case 'image':
			set_post_field( $post_id, 'fusion_distribution', 'facebook', 'image', $value );
			break;
	}
}

/**
 * Get suggested text for Facebook promotion
 *
 * @return array
 */
function get_facebook_share_text_for_promotion( $post_id ) {
	return get_post_field( $post_id, 'fusion_distribution', 'facebook', 'share_text' );
}

/**
 * Get a given Twitter card tag for this post
 *
 * @param string $tag_name
 * @return string
 */
function get_twitter_card_tag( $post_id, $tag_name ) {

	switch ( $tag_name ) {

		case 'title':
			$title = get_post_field( $post_id, 'fusion_distribution', 'twitter', 'title' );

			if ( empty( $title ) ) {
				$title = get_facebook_open_graph_tag( $post_id, 'title' );
			}

			$title = decode_html_entities( $title );

			// Limited to 70 characters or less
			if ( strlen( $title ) > 70 ) {
				$parts = wordwrap( $title, 70, PHP_EOL );
				$parts = explode( PHP_EOL, $parts );
				$val = array_shift( $parts );
			} else {
				$val = $title;
			}
			break;

		case 'description':
			$description = get_post_field( $post_id, 'fusion_distribution', 'twitter', 'description' );

			if ( empty( $description ) ) {
				$description = get_facebook_open_graph_tag( $post_id, 'description' );
			}

			$description = decode_html_entities( $description );

			if ( strlen( $description ) > 200 ) {
				$parts = wordwrap( $description, 200, PHP_EOL );
				$parts = explode( PHP_EOL, $parts );
				$val = array_shift( $parts );
			} else {
				$val = $description;
			}
			break;

		case 'url':
			$val = get_permalink( $post_id );
			break;

		case 'image':
			$val = '';
			$image_id = get_twitter_card_image( $post_id );

			if ( $image_id ) {
				$val = wp_get_attachment_image_src( $image_id, 'twitter-card' );
			} else {
				$val = get_facebook_open_graph_tag( $post_id, 'image' );
			}
			break;

		default:
			$val = '';
			break;
	}

	if ( in_array( $tag_name, array( 'title', 'description' ) ) ) {
		$val = decode_html_entities( $val );
	}
	return $val;
}

/**
 * Set a given Twitter Card tag for this post
 *
 * @param string $tag_name
 * @param mixed $value
 */
function set_twitter_card_tag( $post_id, $tag_name, $value ) {
	switch ( $tag_name ) {
		case 'title':
			set_post_field( $post_id, 'fusion_distribution', 'twitter', 'title', $value );
			break;
		case 'description':
			set_post_field( $post_id, 'fusion_distribution', 'twitter', 'description', $value );
			break;
		case 'image':
			set_post_field( $post_id, 'fusion_distribution', 'twitter', 'image', $value );
			break;
	}
}

/**
 * Get the text to use when a user shares a link on Twitter
 *
 * @return string
 */
function get_twitter_share_text( $post_id ) {

	$share_text = get_post_field( $post_id, 'fusion_distribution', 'twitter', 'share_text' );

	if ( empty( $share_text ) ) {
		$share_text = get_the_title( $post_id );
	}

	if ( mb_strlen( $share_text ) > FUSION_TWITTER_SHARE_TEXT_MAX_LENGTH ) {
		$share_text = mb_substr( $share_text, 0, FUSION_TWITTER_SHARE_TEXT_MAX_LENGTH );
	}

	return decode_html_entities( $share_text );
}

/**
 * Set the Twitter share text (for tests)
 *
 * @param string
 */
function set_twitter_share_text( $post_id, $share_text ) {
	return set_post_field( $post_id, 'fusion_distribution', 'twitter', 'share_text', $share_text );
}

/**
 * Get the image used for the Twitter card
 *
 * @return string image URL
 */
function get_twitter_card_image( $post_id ) {
	$image_id = get_post_field( $post_id, 'fusion_distribution', 'twitter', 'image' );

	if ( $attachment_image = wp_get_attachment_image_src( absint( $image_id ) ) ) {
		return $attachment_image[0];
	}

	return false;
}

/**
 * Get suggested text for Twitter promotion
 *
 * @return array
 */
function get_twitter_share_text_for_promotion( $post_id ) {
	return get_post_field( $post_id, 'fusion_distribution', 'twitter', 'share_text' );
}


/**
 * Get a given Pinterest share field value for this post
 *
 * @param string $field_name
 * @return string
 */
function get_pinterest_share_field( $post_id, $field_name ) {

	switch ( $field_name ) {

		case 'description':
			$val = get_post_field( $post_id, 'fusion_distribution', 'pinterest', 'description' );
			break;

		case 'image':
			$image_id = get_post_field( $post_id, 'fusion_distribution', 'pinterest', 'image' );
			if ( $src = wp_get_attachment_image_src( $image_id, 'pinterest-pin-image' ) ) {
				$val = $src[0];
			} else {
				$val = '';
			}
			break;

		default:
			break;
	}

	if ( empty( $val ) ) {
		$val = get_default_pinterest_share_field( $post_id, $field_name );
	}

	if ( in_array( $field_name, array( 'description' ) ) ) {
		$val = strip_tags( $val );
	}
	return $val;
}


function get_pinterest_share_description( $post_id ) {

	$share_text = get_pinterest_share_field( $post_id, 'distribution' );

	if ( empty( $share_text ) ) {
		$share_text = get_the_title( $post_id );
	}

	return $share_text;
}

/**
 * Get a social image for display in newsletter.
 *
 * Uses either the Facebook image or the post's featured image.
 *
 * @return object Attachment
 */
function get_newsletter_distribution_image( $post_id ) {
	if ( get_post_field( $post_id, 'fusion_distribution', 'facebook', 'image' ) ) {
		$featured_image_id = get_post_field( $post_id, 'fusion_distribution', 'facebook', 'image' );
	} else {
		$featured_image_id = get_featured_image_id( $post_id );
	}

	return Attachment::get_by_post_id( $featured_image_id );
}

