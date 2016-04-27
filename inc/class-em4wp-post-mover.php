<?php

class EM4WP_Post_Mover {

	/**
	 * Class constructor.
	 *
	 * @param  array  $sites      The sites which should have their posts sync'd
	 * @param  string $post_type  The post-type to be sync'd
	 */
	public function __construct( $sites, $post_type ) {

		// Set variables
		$this->post_type = $post_type;
		$this->sites = $sites;

		add_action( 'save_post', array( $this, 'save_post' ), 200 );
return;

		echo $this->post_type."\n\n";
		print_r( $sites );die;

	}

	/**
	 * Saving the post.
	 *
	 * @param  int  $post_id  The post ID
	 */
	public function save_post( $post_id ) {

		// Bail out if just a post revision
		if ( wp_is_post_revision( $post_id ) ) {
			return $post_id;
		}

		// Bail out if not an event
		if ( 'event' != get_post_type( $post_id ) ) {
			return;
		}

		// Bail out if not in $this->sites

		// Copy $post_id between relevant sites within $this->sites.

echo 'Saving Event!';die;
	}

}
