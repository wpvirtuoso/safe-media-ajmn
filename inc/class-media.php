<?php
/**
 * Media Class
 *
 */
class Media {


	/**
	 * Constructor for the class
	 *
	 */
	public function __construct() {

		add_action( 'cmb2_admin_init', array( $this, 'create_term_image_field' ) );
	}

	/**
	 * Creates image field for the term interfaces
	 */
	public function create_term_image_field() {

		$term_media = new_cmb2_box(
			array(
				'id'           => 'term_image',
				'taxonomies'   => array( 'category' ),
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

}


