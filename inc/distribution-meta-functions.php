<?php

namespace Packaging_Preview;

/**
 * Gets a value from a nested array field.
 * Takes any number of arguments as a path, returns the meta value at that page.
 *
 * @param int $post_id
 * @param string... First value is the meta_key, subsequent values are nested array keys inside it.
 * @return mixed Value of the (possible nested) meta field
 */
function get_post_field( int $post_id, string $meta_key /*, $path... */ ) {
	$path = func_get_args();
	$post_id = array_shift( $path );
	$meta_key = array_shift( $path );

	if ( $field = get_post_meta( $post_id, $meta_key, true ) ) {
		while ( count( $path ) ) {
			$path_part = array_shift( $path );
			if ( ! array_key_exists( $path_part, $field ) ) {
				return false;
			}
			$field = $field[ $path_part ];
		}
	}

	return $field;
}

/**
 * Sets a value in a nested array field.
 * Takes any number of arguments as a path, with the value to set as the last argument.
 *
 * @param int $post_id
 * @param string... First value is the meta_key, subsequent values are nested array keys inside it.
 * @param mixed $value value to set
 * @return bool|int Return value of "update_post_meta"
 */
function set_post_field( int $post_id, string $meta_key /*, $path... */, $value ) {
	$path = func_get_args();
	$post_id = array_shift( $path );
	$meta_key = array_shift( $path );
	$value = array_pop( $path );

	if ( ! $meta_value = get_post_meta( $post_id, $meta_key, true ) ) {
		$meta_value = array();
	}

	$field = &$meta_value;

	while ( count( $path ) ) {
		$path_part = array_shift( $path );
		if ( ! array_key_exists( $path_part, $field ) ) {
			$field[ $path_part ] = array();
		}
		$field = &$field[ $path_part ];
	}

	$field = $value;

	return update_post_meta( $post_id, $meta_key, $meta_value );
}

/**
 * Decode HTML entities in a string
 *
 * @param string
 * @return string
 */
function decode_html_entities( $string ) {
	return htmlspecialchars_decode( html_entity_decode( $string ), ENT_QUOTES );
}

/**
 * Get the SEO keywords for the post
 *
 * @return string
 */
function get_seo_keywords( $post_id ) {
	if ( $keywords = get_post_field( $post_id, 'fusion_distribution', 'seo', 'keywords' ) ) {
		return $keywords;
	} else {
		return get_default_seo_keywords( $post_id );
	}
}

/**
 * Get the default SEO keywords for the post
 *
 * @return string
 */
function get_default_seo_keywords( $post_id ) {
	$keywords = array();
	//if ( method_exists( $this, 'get_terms' ) ) {
		//foreach( get_terms( $post_id ) as $term ) {
			//if ( in_array( $term->taxonomy, array( 'fusion_section' ) ) ) {
				//continue;
			//}
			//$keywords[] = $term->name;
		//}
	//}
	return implode( ',', $keywords );
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
	if ( empty( $seo_title ) ) {
		$seo_title = get_default_seo_title( $post_id );
	}
	return strip_tags( $seo_title );
}

/**
 * Set the SEO title
 *
 * @param string
 */
function set_seo_title( $title ) {
	return set_post_field( $post_id, 'fusion_distribution', 'seo', 'title', $title );
}

/**
 * Get the default SEO title for the post
 *
 * Also used as fallback for the page's <title> attribute.
 *
 * @return string
 */
function get_default_seo_title( $post_id ) {

	/*
	 * Because this function is used to generate the <title> element of the
	 * page, we're applying the filters from `the_title` in order to pick up
	 * title overrides from Experiments. We don't need the `widont` filter,
	 * though, so that filter is being disabled and reenabled afterwards.
	 */
	remove_filter( 'the_title', 'widont' );
	$seo_title = apply_filters( 'the_title', get_the_title( $post_id ), $post_id );
	add_filter( 'the_title', 'widont' );

	return $seo_title;
}

/**
 * Get the SEO description for the post
 *
 * @return string
 */
function get_seo_description( $post_id ) {
	$seo_description = get_post_field( $post_id, 'fusion_distribution', 'seo', 'description' );
	if ( empty( $seo_description ) ) {
		$seo_description = get_default_seo_description( $post_id );
	}
	return strip_tags( $seo_description );
}

/**
 * Set the SEO description for the post
 *
 * @param string
 */
function set_seo_description( $description ) {
	set_post_field( $post_id, 'fusion_distribution', 'seo', 'description', $description );
}

/**
 * Get the default SEO description for the post
 *
 * @return string
 */
function get_default_seo_description( $post_id ) {
	return 'Amet est iaculis egestas. Ut at magna. Etiam dui nisi, blandit quis, fermentum vitae, auctor vel, sem. Cras et leo.' ;
}

/**
 * Get a given Facebook open graph tag for this post
 *
 * @param string $tag_name
 * @return string
 */
function get_facebook_open_graph_tag( $tag_name ) {

	switch ( $tag_name ) {

		case 'title':
			$val = get_post_field( $post_id, 'fusion_distribution', 'facebook', 'title' );
			break;

		case 'description':
			$val = get_post_field( $post_id, 'fusion_distribution', 'facebook', 'description' );
			break;

		case 'image':
			$image_id = get_post_field( $post_id, 'fusion_distribution', 'facebook', 'image' );
			$val = array();

			if ( intval( $image_id ) > 0 ) {
				$image = \Fusion\Objects\Post::get_by_post_id( $image_id );
				if ( $image instanceof \Fusion\Objects\Attachment ) {
					return $image->get_src( 'facebook-open-graph', array( 'width' => 1200, 'height' => 630 ) );
				}
			}
			break;

		default:
			break;
	}

	if ( empty( $val ) ) {
		$val = get_default_facebook_open_graph_tag( $post_id, $tag_name );
	}

	if ( in_array( $tag_name, array( 'title', 'description' ) ) ) {
		$val = strip_tags( $val );
	}
	return $val;
}

/**
 * Set a given Facebook open graph tag for this post
 *
 * @param string $tag_name
 * @param mixed $value
 */
function set_facebook_open_graph_tag( $tag_name, $value ) {
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
 * Get the default Facebook Open Graph tag for this post
 *
 * @param string $tag_name
 * @return string
 */
function get_default_facebook_open_graph_tag( $tag_name ) {

	switch ( $tag_name ) {

		case 'title':
			$val = Utils::titlecase( get_the_title( $post_id ) );
			break;

		case 'description':
			$val = '';
			break;

		case 'url':
			$val = get_permalink( $post_id );
			break;

		case 'image':
			if ( $image = get_featured_image( $post_id, 'facebook-open-graph' ) ) {
				$val = $image->get_src( 'facebook-open-graph', array() );
			} else {
				$val = false;
			}
			break;

		default:
			$val = '';
			break;
	}

	return $val;

}

/**
 * Get suggested text for Facebook promotion
 *
 * @return array
 */
function get_facebook_share_text_for_promotion( $post_id ) {
	if ( $share_text = get_post_field( $post_id, 'fusion_distribution', 'facebook', 'share_text' ) ) {
		return array( $share_text );
	// Legacy data structure https://github.com/fusioneng/fusion-theme/issues/3673
	} else if ( $share_text = get_post_field( $post_id, 'fusion_distribution', 'promotion', 'facebook_share_text' ) ) {
		return $share_text;
	} else {
		return array();
	}
}

/**
 * Get a given Twitter card tag for this post
 *
 * @param string $tag_name
 * @return string
 */
function get_twitter_card_tag( $tag_name ) {

	switch ( $tag_name ) {

		case 'title':
			$title = strip_tags( get_post_field( $post_id, 'fusion_distribution', 'twitter', 'title' ) );
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
			$description = strip_tags( get_post_field( $post_id, 'fusion_distribution', 'twitter', 'description' ) );
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
			$image = get_twitter_card_image( $post_id );
			if ( $image ) {
				$val = $image->get_url( 'twitter-card' );
			} else {
				$image = get_featured_image( $post_id );
				if ( $image && 'attachment' == $image->get_type() ) {
					$val = $image->get_url( 'twitter-card' );
				}
			}
			break;

		default:
			$val = '';
			break;
	}

	if ( empty( $val ) ) {
		$val = get_default_twitter_card_tag( $post_id, $tag_name );
	}

	if ( in_array( $tag_name, array( 'title', 'description' ) ) ) {
		$val = strip_tags( $val );
	}
	return $val;
}

/**
 * Set a given Twitter Card tag for this post
 *
 * @param string $tag_name
 * @param mixed $value
 */
function set_twitter_card_tag( $tag_name, $value ) {
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
 * Get the default Twitter card tag for this post
 *
 * @param string $tag_name
 * @return string
 */
function get_default_twitter_card_tag( $tag_name ) {

	switch ( $tag_name ) {

		case 'title':
			$title = strip_tags( get_the_title( $post_id ) );
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
			$excerpt = get_description( $post_id );
			// Limited to 200 characters or less
			if ( strlen( $excerpt ) > 200 ) {
				$parts = wordwrap( $excerpt, 200, PHP_EOL );
				$parts = explode( PHP_EOL, $parts );
				$val = array_shift( $parts );
			} else {
				$val = $excerpt;
			}
			break;

		case 'url':
			$val = get_permalink( $post_id );
			break;

		case 'image':
			$val = get_featured_image_url( $post_id, 'twitter-card' );
			break;

		default:
			$val = '';
			break;
	}

	return $val;

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

	return $share_text;
}

/**
 * Set the Twitter share text (for tests)
 *
 * @param string
 */
function set_twitter_share_text( $share_text ) {
	return set_post_field( $post_id, 'fusion_distribution', 'twitter', 'share_text', $share_text );
}

/**
 * Get the image used for the Twitter card
 *
 * @return Attachment|false
 */
function get_twitter_card_image( $post_id ) {
	$image_id = get_post_field( $post_id, 'fusion_distribution', 'twitter', 'image' );
	$image = Attachment::get_by_post_id( $image_id );
	if ( $image && 'attachment' === $image->get_type() ) {
		return $image;
	} else {
		return false;
	}
}

/**
 * Get suggested text for Twitter promotion
 *
 * @return array
 */
function get_twitter_share_text_for_promotion( $post_id ) {
	if ( $share_text = get_post_field( $post_id, 'fusion_distribution', 'twitter', 'share_text' ) ) {
		return array( $share_text );
	// Legacy data structure https://github.com/fusioneng/fusion-theme/issues/3673
	} else if ( $share_text = get_post_field( $post_id, 'fusion_distribution', 'promotion', 'twitter_share_text' ) ) {
		return $share_text;
	} else {
		return array();
	}
}


/**
 * Get a given Pinterest share field value for this post
 *
 * @param string $field_name
 * @return string
 */
function get_pinterest_share_field( $field_name ) {

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


/**
 * Get the default Pinterest share field, if one isn't specified for this post
 *
 * @param string $field_name
 * @return string
 */
function get_default_pinterest_share_field( $field_name ) {

	switch ( $field_name ) {

		case 'description':
			$val = get_first_sentence_from_post_content( $post_id );
			break;

		case 'image':
			$val = get_featured_image_url( $post_id, 'pinterest-pin-image' );
			break;

		default:
			$val = '';
			break;
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

/**
 * Get the first sentence from the post
 *
 * Used as default description for Facebook or Pinterest sharing.
 *
 * @return string
 */
function get_first_sentence_from_post_content( $post_id ) {

	// Stolen from wp_trim_excerpt()
	$text = strip_shortcodes( get_field( $post_id, 'post_content' ) );
	$text = strip_tags( $text );

	$sentences = preg_split( '#(?<=[.?!](\s|"))[\n\r\t\s]{0,}(?=[A-Z\b"])#',$text);

	if ( is_array( $sentences ) ) {
		return trim( Utils::decode_html_entities( wptexturize( $sentences[0] ) ) );
	}

	return '';
}

