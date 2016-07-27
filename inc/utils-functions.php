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

	$filter = implode( '_', $path );
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

	/**
	 * Filter the value specified for a field on a post.
	 *
	 * Can be used to set defaults if a field is empty.
	 *
	 * The filter is the path arguments passed to the function, concatenated with underscores. If this
	 * function was called to check the fusion_distribution[seo][keywords] field, the filter called here
	 * would be `fusion_distribution_seo_keywords`.
	 *
	 * @param mixed field value returned
	 * @param int post ID
	 */
	return apply_filters( $filter, $field, $post_id );
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
