<?php
/**
 * Helper Class
 *
 */
class Helper {

	/**
	 * Gets posts by featured image id
	 * @param integer $attachment_id attachment id
	 */
	public function get_posts_by_featured_image( $attachment_id ) {

		$posts_ids = get_transient( 'linked_posts_by_featured_area_' . $attachment_id );

		if ( ! empty( $posts_ids ) ) {
			return $posts_ids;
		}

		$posts_ids = array();

		$args = array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'meta_key'       => '_thumbnail_id',
			'meta_value'     => $attachment_id,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'cache_results'  => false,
			'posts_per_page' => -1,
		);

		$query_posts = new WP_Query( $args );

		if ( $query_posts->have_posts() ) {
			$posts_ids = $query_posts->posts;
		}

		set_transient( 'linked_posts_by_featured_area_' . $attachment_id, $posts_ids, 0 );

		return $posts_ids;
	}

	/**
	 * Gets Terms by featured image id.
	 * @param integer $attachment_id attachment id
	 */
	public function get_terms_by_featured_image( $attachment_id ) {

		$terms_ids = get_transient( 'linked_terms_by_feature_area_' . $attachment_id );
		if ( ! empty( $terms_ids ) ) {
			return $terms_ids;
		}
		$terms_ids = array();
		$args      = array(
			'taxonomy'   => array( 'category', 'post_tag' ),
			'hide_empty' => false,
			'fields'     => 'ids',
			'meta_query' => array(
				array(
					'key'     => 'term_featured_image_id',
					'value'   => $attachment_id,
					'compare' => '=',
				),
			),
		);

		$terms_ids = get_terms( $args );

		set_transient( 'linked_terms_by_feature_area_' . $attachment_id, $terms_ids, 0 );

		return $terms_ids;
	}

	/**
	 * Gets posts by searching attachment in content
	 * @param integer $attachment_id attachment id
	 */
	public function get_posts_by_content_media( $attachment_id ) {

		$posts_ids = get_transient( 'linked_posts_by_content_media_' . $attachment_id );

		if ( ! empty( $posts_ids ) ) {
			return $posts_ids;
		}

		$attachment_meta = wp_get_attachment_metadata( $attachment_id );
		$posts_ids       = array();

		foreach ( $attachment_meta['sizes'] as $image_size ) {
			$image_search_query = new WP_Query(
				array(
					's'              => $image_size['file'],
					'post_type'      => 'any',
					'fields'         => 'ids',
					'no_found_rows'  => false,
					'posts_per_page' => -1,
					'cache_results'  => false,
				)
			);

			$posts_ids = array_merge( $image_search_query->posts, $posts_ids );
		}

		$posts_ids = array_unique( $posts_ids );
		set_transient( 'linked_posts_by_content_media_' . $attachment_id, $posts_ids, 0 );
		return $posts_ids;
	}

}
