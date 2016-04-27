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
		foreach ( $this->sites as $key => $site ) {

			foreach ( $site as $from => $to ) {
				if ( get_current_blog_id() == $from ) {

					// Copy $post_id between relevant sites within $this->sites.
					$this->duplicate_over_multisite( $from, $to, $post_id, $this->post_type, 'author??' );
					echo 'Saving Event!';
					die;

				}
			}

		}

	}

	/**
	 * Contains the main function that processes any requested duplication
	 * @since 0.1
	 * @author Mario Jaconelli <mariojaconelli@gmail.com>
	 *
	 * This is the main core function on Multisite Post Duplicator that processes the duplication of a post on a network from one
	 * site to another
	 * 
	 * @param int $from The ID of the source blog to copy from.
	 * @param int $to The ID of the destination blog to copy to.
	 * @param int $source_id The ID of the source post to copy.
	 * @param string $post_type The destination post type.
	 * 
	 * @return array An array containing information about the newly created post
	 * 
	 * Example:
	 * 
	 *          id           => 20,
	 *          edit_url     => 'http://[...]/site1/wp-admin/post.php?post=20&action=edit',
	 *          site_name    => 'Another Site'
	 * 
	 */
	public function duplicate_over_multisite( $from, $to, $source_id, $post_type ) {

		// Switch to the source blog if not already on it
		if ( get_current_blog_id() != $from ) {
			switch_to_blog( $from );
		}

		$post_author = 1; // Should pick up origin post author

		// Get the object of the post we are copying
		$post = get_post( $source_id );
		unset( $post->ID ); // Make sure we don't try to reuse the same post ID

		// Get the tags from the post we are copying
		$sourcetags = wp_get_post_tags( $source_id, array( 'fields' => 'names' ) );
		$source_blog_id  = get_current_blog_id();

		// Get the categories for the post
//		$source_categories = mpd_get_objects_of_post_categories( $source_id, $mpd_process_info['post_type']);

//		$featured_image    = mpd_get_featured_image_from_source( $source_id );

		// If we are copying the sourse post to another site on the network we will collect data about those 
		// images.
		if ( $to != $source_blog_id ) {

//			$attached_images = mpd_get_images_from_the_content( $source_id );

			if($attached_images){
//				$attached_images_alt_tags   = mpd_get_image_alt_tags($attached_images);
			}

		} else{
			$attached_images = false;
		}

		////////////////////////////////////////////////
		// Tell WordPress to work in the destination site
		switch_to_blog( $to );
		////////////////////////////////////////////////

		// Make the new post
		$post_id = wp_insert_post( $post );

		// Copy the meta data collected from the sourse post to the new post
		foreach ( $meta_values as $key => $values ) {

			foreach ($values as $value) {
				//If the data is serialised we need to unserialise it before adding or WordPress will serialise the serialised data
				//...which is bad
				if ( is_serialized( $value ) ) {
					add_post_meta( $post_id, $key, unserialize( $value ) );
				} else {
					add_post_meta( $post_id, $key, $value );
				}
			}

		}

		// If there were media attached to the sourse post content then copy that over
		if($attached_images){
			mpd_process_post_media_attachements($post_id, $attached_images, $attached_images_alt_tags, $source_blog_id, $to);
		}

		// If there was a featured image in the sourse post then copy it over
		if($featured_image){
			// Check that the users plugin settings actually want this process to happen
			if(isset($options['mdp_default_featured_image']) || !$options ){

				mpd_set_featured_image_to_destination( $post_id, $featured_image ); 

			}

		}
		// If there were tags in the sourse post then copy them over
		if($sourcetags){
			// Check that the users plugin settings actually want this process to happen
			if(isset($options['mdp_default_tags_copy']) || !$options ){

				wp_set_post_tags( $post_id, $sourcetags );

			}
			
		}

		// If there were categories in the sourse post then copy them over
		if($source_categories){

			if(isset($options['mdp_copy_post_categories']) || !$options ){

				mpd_set_destination_categories($post_id, $source_categories, $mdp_post['post_type']);

			}

		}
		
		// Collect information about the new post 
		$site_edit_url = get_edit_post_link( $post_id );
		$blog_details  = get_blog_details( $to );
		$site_name     = $blog_details->blogname;

		//////////////////////////////////////
		// Go back to the current blog so we can update information about the action that just took place
		restore_current_blog();
		//////////////////////////////////////

		return $createdPostObject;
	}

}
