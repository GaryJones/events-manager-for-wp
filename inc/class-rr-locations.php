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

		$location = get_post_meta( get_the_ID(), '_location', true );
		if ( isset( $location['latitude'] ) ) {
			$latitude = $location['latitude'];
		} else {
			$latitude = '52.511738';
		}
		if ( isset( $location['longitude'] ) ) {
			$longitude = $location['longitude'];
		} else {
			$longitude = '13.466479';
		}

		$embed_url = 'https://maps.google.com/maps?q=' . $latitude . ',' . $longitude . '&z=14&output=embed&iwloc=0';

		?>
		<style>
		.rr-location {
			display: inline-block;
		}
		table.rr-location {
			width: 39%;
		}
		iframe.rr-location {
			width: 59%;
			height: 300px;
		}
		</style>

		<table class="rr-location">
			<tr>
				<td><label for="latitude"><strong><?php _e( 'Latitude', 'rrewc' ); ?></strong></label></td>
				<td>
					<input type="text" name="location[latitude]" id="latitude" value="<?php echo esc_attr( $latitude ); ?>" />
				</td>
			</tr>
			<tr>
				<td><label for="longitude"><strong><?php _e( 'Longitude', 'rrewc' ); ?></strong></label></td>
				<td>
					<input type="text" name="location[longitude]" id="longitude" value="<?php echo esc_attr( $longitude ); ?>" />
				</td>
			</tr>
		</table>

		<iframe id="rr-map" class="rr-location" src="<?php echo esc_url( $embed_url ); ?>" frameborder="0" allowfullscreen></iframe>

		<script>

			var latitude = document.getElementById("latitude");
			latitude.addEventListener("change", set_map_location);

			var longitude = document.getElementById("longitude");
			longitude.addEventListener("change", set_map_location);

			function set_map_location() {
				var embed_url = 'https://maps.google.com/maps?q='+latitude.value+','+longitude.value+'&z=14&output=embed&iwloc=0';
				var map = document.getElementById("rr-map");
				map.src = embed_url;
			}
			set_map_location();

		</script>

		<input type="hidden" id="location-nonce" name="location-nonce" value="<?php echo esc_attr( wp_create_nonce( __FILE__ ) ); ?>"><?php
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
