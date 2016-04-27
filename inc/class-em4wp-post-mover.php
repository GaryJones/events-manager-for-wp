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

		// Get the posts data
		$post = get_post( $source_id );
		unset( $post->ID ); // Make sure we don't try to reuse the same post ID
		$meta_values = get_post_meta( $source_id );

		// Get the tags from the post we are copying
		$sourcetags = wp_get_post_tags( $source_id, array( 'fields' => 'names' ) );

		// Get the categories for the post
//		$source_categories = mpd_get_objects_of_post_categories( $source_id, $mpd_process_info['post_type']);

//		$featured_image    = mpd_get_featured_image_from_source( $source_id );

		// Collect data about attached images.
//			$attached_images = mpd_get_images_from_the_content( $source_id );

			if($attached_images){
//				$attached_images_alt_tags   = mpd_get_image_alt_tags($attached_images);
			}

//		}

		////////////////////////////////////////////////
		// Tell WordPress to work in the destination site
		switch_to_blog( $to );
		////////////////////////////////////////////////

		// Make the new post
		$post_id = wp_insert_post( $post );

		// Copy the meta data collected from the sourse post to the new post
		foreach ( $meta_values as $key => $values ) {

			foreach ( $values as $value ) {
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
		if ( $attached_images ) {
			$this->process_post_media_attachements( $post_id, $attached_images, $attached_images_alt_tags, $from, $to );
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

	/**
	 * This function performs the action of copying the attached media image(s) to the newly created post in
	 * the core function.
	 *
	 * @since 0.5
	 * @param int $destination_post_id The ID of the post we are copying the media to
	 * @param array $post_media_attachments An array of media library IDs to copy. Probably generated from mpd_get_images_from_the_content()
	 * @param array $attached_images_alt_tags An array of alt tags associated with the images in $post_media_attachments array. Mirrors the array order of this for association. Probably generated from mpd_get_image_alt_tags()
	 * @param int $source_id The ID of the blog these images are being copied from.
	 * @param int $new_blog_id The ID of the blog these images are going to.
	 * @return null
	 */
	public function process_post_media_attachements( $destination_post_id, $post_media_attachments, $attached_images_alt_tags, $source_id, $new_blog_id ){

		// Variable to return the count of images we have processed and also to patch the source keys with the destination keys
		$image_count = 0;
		// Get array of the IDs of the source images pulled from the source content
		$old_image_ids = array_keys( $post_media_attachments );

		// Do stuff with each image from the source post content
		foreach ( $post_media_attachments as $post_media_attachment ) {

			// Get all the data inside a file and attach it to a variable
			$image_data             = file_get_contents( mpd_fix_wordpress_urls( $post_media_attachment->guid ) );
			// Break up the source URL into targetable sections
			$image_URL_info         = pathinfo( $post_media_attachment->guid );
			//Just get the url without the filename extension...we are doing this because this will be the standard URL
			//for all the thumbnails attached to this image and we can therefore 'find and replace' all the possible
			//intermediate image sizes later down the line. See: https://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
			$image_URL_without_EXT  = $image_URL_info['dirname'] ."/". $image_URL_info['filename'];
			//Do the find and replace for the site path
			// ie   http://www.somesite.com/source_blog_path/uploads/10/10/file... will become
			//      http://www.somesite.com/destination_blog_path/uploads/10/10/file...

			$image_URL_without_EXT  = str_replace( get_blog_details( $new_blog_id )->siteurl, get_blog_details( $source_id )->siteurl, $image_URL_without_EXT );

			$filename               = basename( $post_media_attachment->guid );

			// Get the upload directory for the current site
			$upload_dir = wp_upload_dir();
			// Make the path to the desired path to the new file we are about to create
			if ( wp_mkdir_p( $upload_dir['path'] ) ) {
				$file = $upload_dir['path'] . '/' . $filename;
			} else {
				$file = $upload_dir['basedir'] . '/' . $filename;
			}

			// Get the URL (not the URI) of the new file
			$new_file_url = $upload_dir['url'] . '/' . $filename;
			$new_file_url = str_replace( get_blog_details( $source_id )->siteurl, get_blog_details( $new_blog_id )->siteurl, $new_file_url );

			// Add the file contents to the new path with the new filename
			file_put_contents( $file, $image_data );
			// Get the mime type of the new file extension
			$wp_filetype = wp_check_filetype( $filename, null );

			$attachment = array(
				'post_mime_type' => 'image/jpeg',
				'post_title'     => sanitize_file_name( $filename ),
				'post_content'   => $post_media_attachment->post_content,
				'post_status'    => 'inherit',
				'post_excerpt'   => $post_media_attachment->post_excerpt,
				'post_name'      => $post_media_attachment->post_name,
				'guid'           => $new_file_url
			);

			// Attach the new file and its information to the database
			$attach_id = wp_insert_attachment( $attachment, $file, $destination_post_id );

			// Add alt text to the destination image
			if ( $attached_images_alt_tags ) {
				update_post_meta( $attach_id, '_wp_attachment_image_alt', $attached_images_alt_tags[$image_count] );
			}

			// Include code to process functions below:
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			// Define attachment metadata
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

			// Assign metadata to attachment
			wp_update_attachment_metadata( $attach_id, $attach_data );

			// Now that we have all the data for the newly created file and its post we need to manipulate the old content so that
			// it now reflects the destination post
			$new_image_URL_without_EXT = mpd_get_image_new_url_without_extension($attach_id, $source_id, $new_blog_id, $new_file_url );
			$old_content               = get_post_field( 'post_content', $destination_post_id );
			$middle_content            = str_replace( $image_URL_info['dirname'] . "/" . $image_URL_info['filename'], $new_image_URL_without_EXT, $old_content );
			$update_content            = str_replace( 'wp-image-' . $old_image_ids[$image_count], 'wp-image-' . $attach_id, $middle_content );

			$post_update = array(
				'ID'           => $destination_post_id,
				'post_content' => $update_content
			);

			wp_update_post( $post_update );

			$image_count++;
		}

	}

}
