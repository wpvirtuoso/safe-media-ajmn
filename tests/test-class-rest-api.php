<?php
/**
 * Class Rest APi Test
 *
 * @package Safe_Media_Ajmn
 */

/**
 * Rest Api class test cases.
 */
class TestRestApi extends WP_UnitTestCase {

	/**
	 * Test function for get_item.
	 */
	public function test_get_item() {

		$attachment_id = $this->factory->attachment->create();

		$request  = new WP_REST_Request( WP_REST_Server::READABLE, '/assignment/v1/image/' . $attachment_id );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'id', $data );

		wp_delete_attachment( $attachment_id );
	}

	/**
	 * Test function for delete_item.
	 */
	public function test_delete_item() {

		$user_id = self::factory()->user->create(
			array(
				'role' => 'administrator',
			)
		);

		wp_set_current_user( $user_id );

		$attachment_id = $this->factory->attachment->create();
		$request       = new WP_REST_Request( WP_REST_Server::DELETABLE, '/assignment/v1/image/' . $attachment_id );
		$response      = rest_get_server()->dispatch( $request );
		$data          = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'message', $data );

		wp_delete_attachment( $attachment_id );
	}
}
