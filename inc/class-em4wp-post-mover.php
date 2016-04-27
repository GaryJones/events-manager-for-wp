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
		foreach ( $this->sites as $key => $site ) {

			foreach ( $site as $from => $to ) {
				if ( get_current_blog_id() == $from ) {

					// Copy $post_id between relevant sites within $this->sites.
					$this->duplicate_over_multisite( $from, $to, $this->post_type, 'author??', 'prefix???' );
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
	 * @param int $from The ID of the source post to copy
	 * @param int $to The ID of the destination blog to copy to.
	 * @param string $post_type The destination post type.
	 * @param string $prefix Optional prefix to be used on the destination post.
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
	public function duplicate_over_multisite( $from, $to, $post_type, $prefix ) {

		$post_author = 1; // Should pick up origin post author
		$post_status = 'publish'; // Should pick up origin post status

		//Collect function arguments into a single variable
		$mpd_process_info = array(
			'source_id'             => $from,
			'destination_id'        => $to,
			'post_type'             => $post_type,
			'post_author'           => $post_author,
			'prefix'                => $prefix,
			'requested_post_status' => $post_status
		);

		//Get plugin options
		$options    = get_option( 'mdp_settings' );
		//Get the object of the post we are copying
		$mdp_post   = get_post($mpd_process_info['source_id']);
		//Get the title of the post we are copying
		$title      = get_the_title($mdp_post);
		//Get the tags from the post we are copying
		$sourcetags = wp_get_post_tags( $mpd_process_info['source_id'], array( 'fields' => 'names' ) );
		//Get the ID of the sourse blog
		$source_blog_id  = get_current_blog_id();
		//Get the categories for the post
//		$source_categories = mpd_get_objects_of_post_categories($mpd_process_info['source_id'], $mpd_process_info['post_type']);

		//Format the prefix into the correct format if the user adds their own whitespace
		if($mpd_process_info['prefix'] != ''){

			$mpd_process_info['prefix'] = trim($mpd_process_info['prefix']) . ' ';

		}

		//Using the orgininal post object we now want to insert our any new data based on user settings for use
		//in the post object that we will be adding to the destination site
		$mdp_post = array(

				'post_title'    => $mpd_process_info['prefix'] . $title,
				'post_status'   => $mpd_process_info['requested_post_status'],
				'post_type'     => $mpd_process_info['post_type'],
				'post_author'   => $mpd_process_info['post_author'],
				'post_content'  => $mdp_post->post_content,
				'post_excerpt'  => $mdp_post->post_excerpt,
				'post_content_filtered' => $mdp_post->post_content_filtered

		);

		$data              = get_post_custom( $mdp_post );
		$meta_values       = get_post_meta( $mpd_process_info['source_id'] );
//		$featured_image    = mpd_get_featured_image_from_source($mpd_process_info['source_id']);

		//If we are copying the sourse post to another site on the network we will collect data about those 
		//images.
		if($mpd_process_info['destination_id'] != $source_blog_id){

//			$attached_images = mpd_get_images_from_the_content($mpd_process_info['source_id']);

			if($attached_images){

				$attached_images_alt_tags   = mpd_get_image_alt_tags($attached_images);
				
			}

		}else{
			
			$attached_images = false;

		}

		////////////////////////////////////////////////
		//Tell WordPress to work in the destination site
		switch_to_blog($mpd_process_info['destination_id']);
		////////////////////////////////////////////////

		//Make the new post
		$post_id = wp_insert_post($mdp_post);
		//Add the source post meta to the destination post
		foreach ( $data as $key => $values) {

		   foreach ($values as $value) {

			   add_post_meta( $post_id, $key, $value );

			}

		}
		
		//Copy the meta data collected from the sourse post to the new post
		foreach ($meta_values as $key => $values) {

		   foreach ($values as $value) {
				//If the data is serialised we need to unserialise it before adding or WordPress will serialise the serialised data
				//...which is bad
				if(is_serialized($value)){
				 
					add_post_meta( $post_id, $key, unserialize($value));

				}else{

					add_post_meta( $post_id, $key, $value );

				}
			   
			}

		}
		//If there were media attached to the sourse post content then copy that over
		if($attached_images){
			//Check that the users plugin settings actually want this process to happen
			if(isset($options['mdp_copy_content_images']) || !$options ){
				
				mpd_process_post_media_attachements($post_id, $attached_images, $attached_images_alt_tags, $source_blog_id, $to);

			}

		}
		//If there was a featured image in the sourse post then copy it over
		if($featured_image){
			//Check that the users plugin settings actually want this process to happen
			if(isset($options['mdp_default_featured_image']) || !$options ){

				mpd_set_featured_image_to_destination( $post_id, $featured_image ); 

			}

		}
		//If there were tags in the sourse post then copy them over
		if($sourcetags){
			//Check that the users plugin settings actually want this process to happen
			if(isset($options['mdp_default_tags_copy']) || !$options ){

				wp_set_post_tags( $post_id, $sourcetags );

			}
			
		}

		//If there were categories in the sourse post then copy them over
		if($source_categories){

			if(isset($options['mdp_copy_post_categories']) || !$options ){

				mpd_set_destination_categories($post_id, $source_categories, $mdp_post['post_type']);

			}

		}
		
		//Collect information about the new post 
		$site_edit_url = get_edit_post_link( $post_id );
		$blog_details  = get_blog_details($mpd_process_info['destination_id']);
		$site_name     = $blog_details->blogname;

		//////////////////////////////////////
		//Go back to the current blog so we can update information about the action that just took place
		restore_current_blog();
		//////////////////////////////////////

		return $createdPostObject;
	}

}
