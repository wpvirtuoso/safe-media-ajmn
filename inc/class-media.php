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

		add_filter( 'pre_delete_attachment', array( $this, 'prevent_attachment_deletion' ), 10, 2 );
		add_filter( 'media_view_strings', array( $this, 'change_delete_attachment_error_message' ), 10, 2 );

		add_action( 'cmb2_admin_init', array( $this, 'create_term_image_field' ) );

		add_action( 'before_delete_post', array( $this, 'delete_post_attachment_transients' ), 10, 2 );
		add_action( 'pre_post_update', array( $this, 'delete_post_attachment_transients' ), 10, 2 );
		add_action( 'post_updated', array( $this, 'delete_post_attachment_transients' ), 10, 3 );

		add_action( 'edited_terms', array( $this, 'delete_term_attachment_transients' ), 10, 3 );
		add_action( 'edit_terms', array( $this, 'delete_term_attachment_transients' ), 10, 3 );
		add_action( 'pre_delete_term', array( $this, 'delete_term_attachment_transients' ), 10, 2 );

		add_action( 'attachment_submitbox_misc_actions', array( $this, 'show_attachment_associated_objects' ), 10, 1 );
	}

	/**
	 * Get associated objects of attachments.
	 * @param integer $attachment integer
	 */
	public function get_associated_objects( $attachment_id ) {
		$posts               = $this->helper->get_posts_by_featured_image( $attachment_id );
		$terms               = $this->helper->get_terms_by_featured_image( $attachment_id );
		$content_based_posts = $this->helper->get_posts_by_content_media( $attachment_id );

		$associated_objects = array(
			'posts'               => $posts,
			'terms'               => $terms,
			'content_based_posts' => $content_based_posts,
		);

		return $associated_objects;
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
	 * @param bool $delete bool
	 * @param object $attachment object
	 */
	public function prevent_attachment_deletion( $delete, $attachment ) {

		$associated_objects = $this->get_associated_objects( $attachment->ID );

		$posts               = $associated_objects['posts'];
		$terms               = $associated_objects['terms'];
		$content_based_posts = $associated_objects['content_based_posts'];
		$unique_posts        = array_unique( array_merge( $posts, $content_based_posts ) );

		$message = 'Error deleting, Item is being used in following objects:<br><ul>';
		foreach ( $unique_posts as $post_id ) {
			$post_edit_link = get_edit_post_link( $post_id );
			$message       .= '<li><a href="' . $post_edit_link . '"> Post # ' . $post_id . '</a></li>';
		}
		foreach ( $terms as $term_id ) {
			$term_edit_link = get_edit_term_link( $term_id );
			$message       .= '<li><a href="' . $term_edit_link . '"> Term #	 ' . $term_id . '</a></li>';
		}
		$message .= '</ul>';

		if ( count( $posts ) > 0 || count( $terms ) > 0 || count( $content_based_posts ) > 0 ) {
			wp_die( esc_html( $message ) );
		}

		return $delete;
	}

	/**
	 * Delete transient for the featured image when term is deleted.
	 * @param integer $term_id Term ID
	 * @param string $taxonomy taxonomy slug
	 * @param array $args additional arguments
	 */
	public function delete_term_attachment_transients( $term_id, $taxonomy, $args = array() ) {

		$featured_image_id = get_term_meta( $term_id, 'term_featured_image_id', true );
		$transient         = 'linked_terms_by_feature_area_' . $featured_image_id;
		delete_transient( $transient );
	}

	/**
	 * Delete transients for the post media when post is  deleted/updated.
	 * @param integer $post_id Post ID
	 * @param WP_POST $post Post object
	 * @param WP_POST $post_before Post object before update
	 */
	public function delete_post_attachment_transients( $post_id, $post, $post_before = array() ) {
		$featured_image_id        = get_post_thumbnail_id( $post_id );
		$transient_featured_image = 'linked_posts_by_featured_area_' . $featured_image_id;
		delete_transient( $transient_featured_image );

		$content_media = get_attached_media( 'image', $post_id );
		foreach ( $content_media as $image ) {
			$image_id                = $image->ID;
			$transient_content_media = 'linked_posts_by_content_media_' . $image_id;
			delete_transient( $transient_content_media );
		}
	}

	/**
	 * Displays associated terms and posts for an attachment
	 * @param object $attachment attachment object
	 */
	public function show_attachment_associated_objects( $attachment ) {

		$associated_objects  = $this->get_associated_objects( $attachment->ID );
		$posts               = $associated_objects['posts'];
		$terms               = $associated_objects['terms'];
		$content_based_posts = $associated_objects['content_based_posts'];

		$unique_posts = array_unique( array_merge( $posts, $content_based_posts ) );

		echo '<div class="misc-pub-section misc-pub-uploadedto">Associated posts: ';
		$ctr = 1;
		if ( count( $unique_posts ) === 0 ) {
			echo 'None';
		}
		foreach ( $unique_posts as $post_id ) {
			if ( 1 === $ctr ) {
				echo '<a href="' . esc_attr( get_edit_post_link( $post_id ) ) . '">' . esc_html( $post_id ) . '</a>';
			} else {
				echo ' ,<a href="' . esc_attr( get_edit_post_link( $post_id ) ) . '">' . esc_html( $post_id ) . '</a>';
			}
			$ctr++;
		}
		echo '</div>';

		echo '<div class="misc-pub-section misc-pub-uploadedto">Associated terms: ';
		$ctr = 1;
		if ( count( $terms ) === 0 ) {
			echo 'None';
		}
		foreach ( $terms as $term_id ) {

			if ( 1 > $ctr ) {
				echo '<a href="' . esc_attr( get_edit_term_link( $term_id ) ) . '">' . esc_html( $term_id ) . '</a>';
			} else {
				echo ' ,<a href="' . esc_attr( get_edit_term_link( $term_id ) ) . '">' . esc_html( $term_id ) . '</a>';
			}
			$ctr++;
		}
		echo '</div>';
	}

	/**
	 * Changes alert notification content on attachment deletion
	 * @param string $error error message
	 * @param WP_POST $post Post object
	 */
	public function change_delete_attachment_error_message( $error, $post ) {

		$error['errorDeleting'] = 'Could not delete the attachment. It is being used on some objects.';
		return $error;
	}
}
