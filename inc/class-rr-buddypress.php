<?php


function ryanhellyer_post_type_activities_in_group() {
    if ( ! bp_is_active( 'activity' ) ) {
        return;
    }

    add_post_type_support( 'page', 'buddypress-activity' );

    bp_activity_set_post_type_tracking_args( 'page', array(
        'action_id' => 'new_ryanhellyer_page',
    ) );
}
add_action( 'bp_init', 'ryanhellyer_post_type_activities_in_group' );

function ryanhellyer_activity_add( $args = array() ) {
    if ( empty( $args['type'] ) || 'new_ryanhellyer_page' !== $args['type'] ) {
        return $args;
    }

    // if posted in a group...
    if ( bp_is_group() ) {
        $args['component'] = 'groups';
        $args['item_id']   = 2;//groups_get_current_group()->id;
    }

    return $args;
}
add_filter( 'bp_before_activity_add_parse_args', 'ryanhellyer_activity_add', 10, 1 );





class RR_BuddyPress {

	public function __construct() {
return;
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
