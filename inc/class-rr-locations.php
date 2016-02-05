<?php

/**
 * Locations.
 *
 * @link       https://github.com/billerickson/BE-Events-Calendar
 * @author     Bill Erickson <bill@billerickson.net>
 * @copyright  Copyright (c) 2014, Bill Erickson
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class RR_Locations {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post',      array( $this, 'meta_boxes_save' ), 10, 2 );
		//
	}

	/**
	 * Add admin metabox.
	 */
	public function add_metabox() {
		add_meta_box(
			'location', // ID
			__( 'Location', 'rrewc' ), // Title
			array(
				$this,
				'meta_box', // Callback to method to display HTML
			),
			'event', // Post type
			'normal', // Context, choose between 'normal', 'advanced', or 'side'
			'high'  // Position, choose between 'high', 'core', 'default' or 'low'
		);
	}

	/**
	 * Output the example meta box.
	 */
	public function meta_box() {

		?>

		<p>
			<label for="_location"><strong><?php _e( 'Location', 'rrewc' ); ?></strong></label>
			<br />
			<input type="text" name="location" id="_location" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_location', true ) ); ?>" />
			<input type="hidden" id="location-nonce" name="location-nonce" value="<?php echo esc_attr( wp_create_nonce( __FILE__ ) ); ?>">
		</p><?php
	}

	/**
	 * Save opening times meta box data.
	 *
	 * @param  int     $post_id  The post ID
	 * @param  object  $post     The post object
	 */
	public function meta_boxes_save( $post_id, $post ) {

		// Only save if correct post data sent
		if ( isset( $_POST['_location'] ) ) {

			// Do nonce security check
			if ( ! wp_verify_nonce( $_POST['location-nonce'], __FILE__ ) ) {
				return;
			}

			// Sanitize and store the data
			$_example = wp_kses_post( $_POST['_location'] );
			update_post_meta( $post_id, '_location', $_example );
		}

	}

}
