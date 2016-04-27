<?php

class EM4WP_Post_Mover {

	/**
	 * Class constructor.
	 *
	 * @param  array  $sites      The sites which should have their posts sync'd
	 * @param  string $post_type  The post-type to be sync'd
	 */
	public function __construct( $sites, $post_type ) {

		// 
		echo $post_type."\n\n";
		print_r( $sites );die;

	}

}


$current_site_id = get_current_blog_id();
$sites = array(
	0 => array(
		$current_site_id => 1,
	),
);
new Post_Mover( $sites, 'event' );
