<?php

class EM4WP_BuddyPress extends EM4WP_Events_Core {

	const META_KEY  = 'group';

	/**
	 * Class constructor.
	 */
	public function __construct() {

		parent::__construct();

		add_action( 'bp_init', array( $this, 'init' ) );
	}

	/**
	 * Initialisation of functions.
	 **/
	public function init() {

		// Bail out now if BuddyPress Groups component (aka user discussions) not loaded
		if ( ! class_exists( 'BP_Groups_Group' ) ) {
			return;
		}

		add_filter( 'bp_before_activity_add_parse_args', array( $this, 'activity_add' ), 10, 1 );
		add_action( 'add_meta_boxes',                    array( $this, 'add_metabox' ) );
		add_action( 'save_post',                         array( $this, 'meta_boxes_save' ), 10, 2 );

		$this->post_type_activities_in_group();
	}

	/**
	 * Add post-type creation to activity stream.
	 */
	public function post_type_activities_in_group() {

		if ( ! bp_is_active( 'activity' ) ) {
			return;
		}

		add_post_type_support( $this->event_slug, 'buddypress-activity' );

	}

	/**
	 * Add activity.
	 *
	 * @param  array  $args
	 * @return array  The modified arguments
	 */
	public function activity_add( $args = array() ) {

		if ( empty( $args['type'] ) || 'new_' . $event_slug !== $args['type'] ) {
			return $args;
		}

		// If to be posted in a group... (we need to save it here, because otherwise the normal post_save functionality won't have fired yet)
		$group_id = $this->meta_boxes_save( get_the_ID() );
		if ( '' != $group_id ) {
			$group_id = absint( $group_id );
			$args['component'] = 'groups';
			$args['item_id']   = $group_id;
		}

		return $args;
	}

	/**
	 * Add admin metabox.
	 */
	public function add_metabox() {
		add_meta_box(
			self::META_KEY, // ID
			__( 'BuddyPress group', 'events-manager-for-wp' ), // Title
			array(
				$this,
				'meta_box', // Callback to method to display HTML
			),
			$this->event_slug, // Post type
			'side', // Context, choose between 'normal', 'advanced', or 'side'
			'high'  // Position, choose between 'high', 'core', 'default' or 'low'
		);
	}

	/**
	 * Output the meta box.
	 */
	public function meta_box() {
		?>

		<p>
			<label for="<?php echo esc_attr( '_' . self::META_KEY ); ?>"><strong><?php _e( 'Select a group activity stream to add the event to.', 'events-manager-for-wp' ); ?></strong></label>
			<br />
			<select name="<?php echo esc_attr( '_' . self::META_KEY ); ?>" id="<?php echo esc_attr( '_' . self::META_KEY ); ?>"><?php

			$groups = BP_Groups_Group::get(array(
				'type'     =>'alphabetical',
				'per_page' =>999
			));
			echo '<option value="">' . esc_html__( 'None', 'events-manager-for-wp' ) . '</option>';
			foreach ( $groups['groups'] as $key => $group ) {
				echo '<option ' . selected( $group->id, get_post_meta( get_the_ID(), '_' . self::META_KEY, true ), false ) . ' value="' . esc_attr( $group->id ) . '">' . esc_html( $group->name ) . '</option>';
			}
			?>

			</select>
			<input type="hidden" id="<?php echo esc_attr( self::META_KEY . '-nonce' ); ?>" name="<?php echo esc_attr( self::META_KEY . '-nonce' ); ?>" value="<?php echo esc_attr( wp_create_nonce( __FILE__ ) ); ?>">
		</p><?php
	}

	/**
	 * Save opening times meta box data.
	 *
	 * @param  int  $post_id   The post ID
	 * @return int  $group_id  The group ID
	 */
	public function meta_boxes_save( $post_id, $post ) {
		$group_id = null;

		// Only save if correct post data sent
		if ( isset( $_POST['_' . self::META_KEY] ) ) {

			// Do nonce security check
			if ( ! wp_verify_nonce( $_POST[self::META_KEY . '-nonce'], __FILE__ ) ) {
				return;
			}

			// Sanitize and store the data
			if (
				is_numeric( $_POST['_' . self::META_KEY] )
				||
				'' == $_POST['_' . self::META_KEY]
			) {
				$group_id = $_POST['_' . self::META_KEY];
				update_post_meta( $post_id, '_' . self::META_KEY, $group_id );
			}
		}

		return $group_id;
	}

}
