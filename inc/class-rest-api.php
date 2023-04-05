<?php
/**
 * Rest Api Class
 *
 */
class Rest_Api {

	/**
	 * Helper class object
	 */
	private $helper;

	/**
	 * Constructor for the class.
	 */
	public function __construct() {

		$this->helper = new Helper();

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Gets an item
	 * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {

		$attachment_id = $request['id'];
		$data = [];
		$post_type     = get_post_type( $attachment_id );
		if ( 'attachment' === $post_type ) {
			$associated_objects  = $this->helper->get_associated_objects( $attachment_id );
			$posts               = $associated_objects['posts'];
			$terms               = $associated_objects['terms'];
			$content_based_posts = $associated_objects['content_based_posts'];
			$unique_posts        = array_unique( array_merge( $posts, $content_based_posts ) );
			$attachment_type     = get_post_mime_type( $attachment_id );
			$attachment_date     = get_the_date( 'Y-m-d', $attachment_id );
			$attachment_slug     = get_post_field( 'post_name', $attachment_id );
			$attachment_url      = wp_get_attachment_image_url( $attachment_id, 'full' );
			$attachment_alt      = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

			$data = array(
				'id'                 => $attachment_id,
				'date'               => $attachment_date,
				'slug'               => $attachment_slug,
				'type'               => $attachment_type,
				'link'               => $attachment_url,
				'alt'                => $attachment_alt,
				'associated_objects' => array(
					'posts' => $unique_posts,
					'terms' => $terms,
				),
			);
			$status = 200;
		} else {
			$data['message'] = 'Image does not exist for given id.';
			$status = 404;
		}

		$response = new WP_REST_Response( $data );
		$response->set_status($status);
		return $response;
	}

	/**
	 * Deletes an item
	 * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {

		$data = [];
		$attachment_id = $request['id'];
		$data['message'] = 'could not delete.';
		$status = 405;

		if ( wp_delete_attachment( $attachment_id ) ) {
			$data['message'] = 'deleted.';
			$status = 200;
		}
		
		$response = new WP_REST_Response( $data );
		$response->set_status($status);
		return $response;
	}

	/**
	 * Registers routes for assignment namespace
	 */
	public function register_routes() {

		register_rest_route(
			'/assignment/v1',
			'/image/(?P<id>\d+)',
			array(
				array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_item' ),
				),
				array(
					'methods'  => WP_REST_Server::DELETABLE,
					'callback'               => array( $this, 'delete_item' ),
				)
			)
		);
	}
}
