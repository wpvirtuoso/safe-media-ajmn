<?php
/**
 * Class Media Test
 *
 * @package Safe_Media_Ajmn
 */

const UNIT_TEST = 'UNIT_TESTING';

/**
 * Media class test cases.
 */
class TestMedia extends WP_UnitTestCase {

	/**
	 * Media class object
	 */
	private $media;

	/**
	 * Constructor for class.
	 */
	public function __construct() {

		parent::__construct();
		$this->media = new Media();
	}

	/**
	 * Test function for prevent_attachment_deletion
	 */
	public function test_prevent_attachment_deletion() {

		$attachment_id = $this->factory->attachment->create();
		$post_id       = $this->factory->post->create();
		add_post_meta( $post_id, '_thumbnail_id', $attachment_id );

		$prevent_deletion = $this->media->prevent_attachment_deletion( null, get_post( $attachment_id ) );

		$this->assertFalse( $prevent_deletion );

		wp_delete_post( $post_id );
		wp_delete_attachment( $attachment_id );
	}

	/**
	 * Test function for delete_post_attachment_transients.
	 */
	public function test_delete_post_attachment_transients() {

		$post_id       = $this->factory->post->create();
		$attachment_id = $this->factory->attachment->create();
		add_post_meta( $post_id, '_thumbnail_id', $attachment_id );
		$transient_featured_image = 'linked_posts_by_featured_area_' . $attachment_id;
		wp_delete_attachment( $attachment_id );

		$this->media->delete_post_attachment_transients( $post_id, get_post( $post_id ) );

		$this->assertFalse( get_transient( $transient_featured_image ) );

		wp_delete_post( $post_id );
		wp_delete_attachment( $attachment_id );
	}

	/**
	 * Test function for delete_term_attachment_transients.
	 */
	public function test_delete_term_attachment_transients() {

		$taxonomy      = 'category';
		$term          = wp_insert_term( 'test', $taxonomy );
		$attachment_id = $this->factory->attachment->create();

		add_term_meta( $term['term_id'], 'term_featured_image_id', $attachment_id );
		$transient = 'linked_terms_by_feature_area_' . $attachment_id;
		wp_delete_attachment( $attachment_id );

		$this->media->delete_term_attachment_transients( $term['term_id'], $taxonomy );

		$this->assertFalse( get_transient( $transient ) );

		wp_delete_term( $term['term_id'], $taxonomy );
		wp_delete_attachment( $attachment_id );
	}
}
