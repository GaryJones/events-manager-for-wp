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
		add_action( 'template_redirect',      array( $this, 'rel_canonical_init' ) );
	}

	public function rel_canonical_init() {
		if ( $this->post_type == get_post_type() && is_single() ) {
			remove_action( 'wp_head', 'rel_canonical' );
			add_action( 'wp_head', array( $this, 'rel_canonical' ) );
		}
	}

	public function rel_canonical() {

		$canonical_blog_id = get_post_meta( get_the_ID(), 'source_blog_id', true );
		$canonical_event_id = get_post_meta( get_the_ID(), 'source_event_id', true );

		if ( $canonical_blog_id && $canonical_event_id ) {

			// Briefly switch to source site to get permalink
			switch_to_blog( $canonical_blog_id );
			$canonical_url = get_permalink( $canonical_id );
			switch_to_blog( $canonical_event_id );

echo "\n\n\n\n\n\n\n\n\n__________________\n";
			echo "<link rel='canonical' href='$canonical_url' />\n";
echo "\n__________________\n\n\n\n\n\n\n\n\n";
		}
	 
		// original code
		$link = get_permalink( $id );
		if ( $page = get_query_var('cpage') )
			$link = get_comments_pagenum_link( $page );
		echo "<link rel='canonical' href='$link' />\n";
	}

	/**
	 * Saving the post.
	 *
	 * @param  int  $post_id  The post ID
	 */
	public function save_post( $post_id ) {

		// Bail out now if not even saving an event
		if ( ! isset( $_POST['em4wp_events_calendar_date_time_nonce'] ) ) {
			return;
		}

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

		// Get the posts data
		$post = get_post( $source_id );
		$source_post_id = $post->ID;
		unset( $post->ID ); // Make sure we don't try to reuse the same post ID
		$meta_values = get_post_meta( $source_id );

		// Briefly switch to destination blog to confirm if post already exists or not (no point in copying it if it already exists)
		switch_to_blog( $to );
		$post_date_unix = strtotime( $post->post_date );
		$args = array(
			'post_type'  => $this->post_type,
			'post_name'  => $post->post_name,
			'date_query' => array(
				array(
					'year'   => date( 'Y', $post_date_unix ),
					'month'  => date( 'm', $post_date_unix ),
					'day'    => date( 'd', $post_date_unix ),
					'hour'   => date( 'H', $post_date_unix ),
					'minute' => date( 'i', $post_date_unix ),
					'second' => date( 's', $post_date_unix ),
				),
			),
		);
		$query = new WP_Query( $args );
		if ( isset( $query->posts[0] ) ) {

			// The event already exists, so there is no point in copying it again
			return;

		}

		switch_to_blog( $from );

		// Get the tags from the post we are copying
		$sourcetags = wp_get_post_tags( $source_id, array( 'fields' => 'names' ) );

		// Get the categories for the post
		$source_categories = $this->get_objects_of_post_categories( $source_id, $this->post_type );

// Get the event-categories taxonomy terms for the post

		$featured_image    = $this->get_featured_image_from_source( $source_id );

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

		add_post_meta( $post_id, 'source_blog_id', $from );
		add_post_meta( $post_id, 'source_event_id', $source_post_id );
echo "\n\n\n\n______________\n".$post_id.'|'.$source_post_id.':'.$from;die;

		// If there were media attached to the sourse post content then copy that over
		if ( $attached_images ) {
			$this->process_post_media_attachments( $post_id, $attached_images, $attached_images_alt_tags, $from, $to );
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
	 * @param int $post_id The ID of the post we are copying the media to
	 * @param array $post_media_attachments An array of media library IDs to copy. Probably generated from get_images_from_the_content()
	 * @param array $attached_images_alt_tags An array of alt tags associated with the images in $post_media_attachments array. Mirrors the array order of this for association. Probably generated from mpd_get_image_alt_tags()
	 * @param int $source_id The ID of the blog these images are being copied from.
	 * @param int $to The ID of the blog these images are going to.
	 * @return null
	 */
	public function process_post_media_attachments( $post_id, $post_media_attachments, $attached_images_alt_tags, $source_id, $to ){

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

			$image_URL_without_EXT  = str_replace( get_blog_details( $to )->siteurl, get_blog_details( $source_id )->siteurl, $image_URL_without_EXT );

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
			$new_file_url = str_replace( get_blog_details( $source_id )->siteurl, get_blog_details( $to )->siteurl, $new_file_url );

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
			$attach_id = wp_insert_attachment( $attachment, $file, $post_id );

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
			$new_image_URL_without_EXT = mpd_get_image_new_url_without_extension($attach_id, $source_id, $to, $new_file_url );
			$old_content               = get_post_field( 'post_content', $post_id );
			$middle_content            = str_replace( $image_URL_info['dirname'] . "/" . $image_URL_info['filename'], $new_image_URL_without_EXT, $old_content );
			$update_content            = str_replace( 'wp-image-' . $old_image_ids[$image_count], 'wp-image-' . $attach_id, $middle_content );

			$post_update = array(
				'ID'           => $post_id,
				'post_content' => $update_content
			);

			wp_update_post( $post_update );

			$image_count++;
		}

	}

	/**
	 * Gets information on the featured image attached to a post
	 *
	 * This function will get the meta data and other information on the posts featured image; including the url
	 * to the full size version of the image.
	 *
	 * @since 0.5
	 * @param int $post_id The ID of the post that the featured image is attached to.
	 * @return array
	 *
	 * Example
	 *
	 *          id => '23',
	 *          url => 'http://www.example.com/image/image.jpg',
	 *          alt => 'Image Alt Tag',
	 *          description => 'Probably a big string of text here',
	 *          caption => 'A nice caption for the image hopefully'
	 *
	 */
	public function get_featured_image_from_source( $post_id ) {
		$thumbnail_id   = get_post_thumbnail_id( $post_id );
		$image          = wp_get_attachment_image_src( $thumbnail_id, 'full' );
		if ( $image ) {
			$image_details = array(
				'url'           => $image[0],
				'alt'           => get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ),
				'post_title'    => get_post_field( 'post_title', $thumbnail_id ),
				'description'   => get_post_field( 'post_content', $thumbnail_id ),
				'caption'       => get_post_field( 'post_excerpt', $thumbnail_id ),
				'post_name'     => get_post_field( 'post_name', $thumbnail_id )
			);

			return $image_details;
		}
	}

	/**
	 * This function gets all the categories that a post is assigned to
	 *
	 * @since 0.8
	 * @param $post_id The id of the post that we want to get the categories for
	 * @param $post_type The post type of the post that we want to get the categories for
	 *
	 * @return array An array of the category objects.
	 *
	 */
	public function get_objects_of_post_categories( $post_id, $post_type ) {
		$args = array(
			'type' => $post_type,
		);
		$categories = wp_get_post_categories( $post_id, $args );
		 
		$array_of_category_objects = array();
		foreach ( $categories as $category ) {
			array_push( $array_of_category_objects, get_category( $category ) );
		}

		return $array_of_category_objects;
	}

}
