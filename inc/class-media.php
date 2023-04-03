<?php
/**
 * Media Class
 *
 */
class Media {

	/**
	 * Helper class object
	 */
	private $helper;

	/**
	 * Constructor for the class.
	 */
	public function __construct() {

		$this->helper = new Helper();

		add_action( 'cmb2_admin_init', array( $this, 'create_term_image_field' ) );
		add_filter( 'pre_delete_attachment', array( $this, 'prevent_attachment_deletion' ), 10, 2 );
	}

	/**
	 * Creates image field for the term interfaces.
	 */
	public function create_term_image_field() {

		$term_media = new_cmb2_box(
			array(
				'id'           => 'term_image',
				'taxonomies'   => array( 'category', 'post_tag' ),
				'object_types' => array( 'term' ),
			)
		);

		$term_media->add_field(
			array(
				'name'         => 'Featured Image',
				'id'           => 'term_featured_image',
				'type'         => 'file',
				'options'      => array(
					'url' => false,
				),
				'text'         => array(
					'add_upload_file_text' => 'Upload Image',
				),
				'query_args'   => array(
					'type' => array(
						'image/jpeg',
						'image/png',
					),
				),
				'preview_size' => 'medium',
				'desc'         => 'Upload a JPEG or PNG image.',
				'attributes'   => array(
					'accept' => 'image/jpeg,image/png',
				),
			)
		);
	}

	/**
	 * Prevents deletion if the image is being used in a post or term.
	 */
	public function prevent_attachment_deletion( $delete, $attachment ) {

		$posts = $this->helper->get_posts_by_featured_image( $attachment->ID );

		if ( count( $posts ) > 0 ) {
			return false;
		}

		$terms = $this->helper->get_terms_by_featured_image( $attachment->ID );

		if ( count( $terms ) > 0 ) {
			return false;
		}

		$content_based_posts = $this->helper->get_posts_by_content_media( $attachment->ID );

		if ( count( $content_based_posts ) > 0 ) {
			return false;
		}

		return $delete;
	}

}


