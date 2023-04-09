<?php
/**
 * Class Helper Test
 *
 * @package Safe_Media_Ajmn
 */
/**
 * Helper class test cases.
 */
class TestHelper extends WP_UnitTestCase {

	/**
	 * Helper class object
	 */
	private $helper;

	/**
	 * Constructor for class
	 */
	public function __construct() {

		parent::__construct();
		$this->helper = new Helper();
	}

	/**
	 * Test function for get_posts_by_featured_image.
	 */
	public function test_get_posts_by_featured_image() {

		$post_id       = $this->factory->post->create();
		$attachment_id = $this->factory->attachment->create();

		add_post_meta( $post_id, '_thumbnail_id', $attachment_id );

		$posts = $this->helper->get_posts_by_featured_image( $attachment_id );

		$this->assertIsArray( $posts );
		$this->assertContains( $post_id, $posts );

		wp_delete_post( $post_id );
		wp_delete_attachment( $attachment_id );
	}

	/**
	 * Test function for get_terms_by_featured_image.
	 */
	public function test_get_terms_by_featured_image() {

		$attachment_id = $this->factory->attachment->create();
		$term          = wp_insert_term( 'test', 'category' );

		add_term_meta( $term['term_id'], 'term_featured_image_id', $attachment_id );

		$terms = $this->helper->get_terms_by_featured_image( $attachment_id );

		$this->assertIsArray( $terms );
		$this->assertContains( $term['term_id'], $terms );

		wp_delete_term( $term['term_id'], 'category' );
		wp_delete_attachment( $attachment_id );
	}

	/**
	* Test function for get_associated_objects.
	 */
	public function test_get_associated_objects() {

		$attachment_id = $this->factory->attachment->create();
		$post_id       = $this->factory->post->create();
		$term          = wp_insert_term( 'test', 'category' );

		add_term_meta( $term['term_id'], 'term_featured_image_id', $attachment_id );
		add_post_meta( $post_id, '_thumbnail_id', $attachment_id );

		$expected_result = array(
			'posts' => array( $post_id ),
			'terms' => array( $term['term_id'] ),
		);

		$associated_objects = $this->helper->get_associated_objects( $attachment_id );

		$this->assertEquals( $expected_result, $associated_objects );

		wp_delete_post( $post_id );
		wp_delete_term( $term['term_id'], 'category' );
		wp_delete_attachment( $attachment_id );
	}
}
