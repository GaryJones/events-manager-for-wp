<?php


class RR_BuddyPress {

	public function __construct() {
		add_post_type_support( 'event', 'buddypress-activity' );
		add_action( 'bp_activity_before_save', array( $this, 'add_activity' ) );
	}

	/**
	 * Add activity.
	 *
	 * @param  object  $activity
	 */
	public function add_activity( $activity ) {

		// Bail out if not saving an event
		if ( 'new_events' != $activity->type ) {
			return;
		}

		$post_id = $activity->secondary_item_id;
		$current_user = wp_get_current_user();
		$post = get_post( $post_id );
		$excerpt = $post->post_excerpt;
		if ( '' == $excerpt ) {
			$excerpt = $post->post_content;
		}

		// Save the activity
		$args = array(
		//	'id'                => '',
			'action'            => '<a href="' . esc_url( get_author_posts_url( $current_user->ID, $current_user->user_nicename ) ) . '">' . $current_user->display_name . '</a>' . __( ' did something', 'XXX' ),
			'content'           => $excerpt,
			'component'         => 'groups',
			'type'              => 'activity_update',
			'item_id'           => 2, // The group ID
			'secondary_item_id' => $post_id, // Post ID
		);

		bp_activity_add( $args );
	}

}
