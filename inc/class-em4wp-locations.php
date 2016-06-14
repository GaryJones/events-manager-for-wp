<?php

/**
 * Locations.
 */
class EM4WP_Locations extends EM4WP_Events_Core {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post',      array( $this, 'meta_boxes_save' ), 10, 2 );
		add_filter( 'the_content',    array( $this, 'the_content' ), 30 );
	}

	/**
	 * Add admin metabox.
	 */
	public function add_metabox() {
		add_meta_box(
			'location', // ID
			__( 'Location', 'events-manager-for-wp' ), // Title
			array(
				$this,
				'meta_box', // Callback to method to display HTML
			),
			$this->event_post_type, // Post type
			'normal', // Context, choose between 'normal', 'advanced', or 'side'
			'high'  // Position, choose between 'high', 'core', 'default' or 'low'
		);
	}

	/**
	 * Output the example meta box.
	 */
	public function meta_box() {

		$location = get_post_meta( get_the_ID(), '_location', true );
		if ( isset( $location['display'] ) ) {
			$display = $location['display'];
		} else {
			$display = '';
		}
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
		if ( isset( $location['width'] ) ) {
			$width = $location['width'];
		} else {
			$width = '640';
		}
		if ( isset( $location['height'] ) ) {
			$height = $location['height'];
		} else {
			$height = '480';
		}

		if ( '' != $latitude && '' != $longitude ) {
			$embed_url = 'https://maps.google.com/maps?q=' . $latitude . ',' . $longitude . '&z=14&output=embed&iwloc=0';
		} else {
			$embed_url = '';
		}

		?>
		<style>
		.em4wp-location {
			display: inline-block;
		}
		table.em4wp-location {
			width: 39%;
		}
		iframe.em4wp-location {
			width: 59%;
			height: 300px;
		}
		</style>

		<table class="em4wp-location">
			<tr>
				<td><label for="display"><strong><?php _e( 'Display?', 'events-manager-for-wp' ); ?></strong></label></td>
				<td>
					<input type="checkbox" name="location[display]" id="display" <?php checked( $display, 1 ); ?> value="1" />
				</td>
			</tr>
			<tr>
				<td><label for="latitude"><strong><?php _e( 'Latitude', 'events-manager-for-wp' ); ?></strong></label></td>
				<td>
					<input type="text" name="location[latitude]" id="latitude" value="<?php echo esc_attr( $latitude ); ?>" />
				</td>
			</tr>
			<tr>
				<td><label for="longitude"><strong><?php _e( 'Longitude', 'events-manager-for-wp' ); ?></strong></label></td>
				<td>
					<input type="text" name="location[longitude]" id="longitude" value="<?php echo esc_attr( $longitude ); ?>" />
				</td>
			</tr>
			<tr>
				<td><label for="width"><strong><?php _e( 'Width', 'events-manager-for-wp' ); ?></strong></label></td>
				<td>
					<input type="text" name="location[width]" id="width" value="<?php echo esc_attr( $width ); ?>" />
				</td>
			</tr>
			<tr>
				<td><label for="height"><strong><?php _e( 'Height', 'events-manager-for-wp' ); ?></strong></label></td>
				<td>
					<input type="text" name="location[height]" id="height" value="<?php echo esc_attr( $height ); ?>" />
				</td>
			</tr>
		</table>

		<iframe id="em4wp-map" class="em4wp-location" src="<?php echo esc_url( $embed_url ); ?>" frameborder="0" allowfullscreen></iframe>

		<script>

			var latitude = document.getElementById("latitude");
			latitude.addEventListener("change", set_map_location);

			var longitude = document.getElementById("longitude");
			longitude.addEventListener("change", set_map_location);

			function set_map_location() {
				var embed_url = 'https://maps.google.com/maps?q='+latitude.value+','+longitude.value+'&z=14&output=embed&iwloc=0';
				var map = document.getElementById("em4wp-map");
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
		if ( isset( $_POST['location'] ) ) {

			// Do nonce security check
			if ( ! wp_verify_nonce( $_POST['location-nonce'], __FILE__ ) ) {
				return;
			}

			// Sanitize and store the data
			foreach ( $_POST['location'] as $key => $value ) {
				if (
					in_array( $key, array( 'longitude', 'latitude', 'width', 'height', 'display' ) )
					&&
					is_numeric( $value )
				) {
					$location[$key] = $value;
				}

			}

			update_post_meta( $post_id, '_location', $location );

		}

	}

	/**
	 * Add the map to the content.
	 *
	 * @param  string  $content  The post content
	 * @return string  The modified post content
	 */
	public function the_content( $content ) {

		// Bail out now if we are not on an event
		if( $this->event_post_type != get_post_type() ) {
			return $content;
		}


		$location = get_post_meta( get_the_ID(), '_location', true );
		if ( isset( $location['display'] ) && 1 == $location['display'] && isset( $location['latitude'] ) && isset( $location['longitude'] ) ) {

			$latitude = $location['latitude'];
			$longitude = $location['longitude'];

			if ( '' != $latitude && '' != $longitude ) {

				$content .= '
				<div class="em4wp-full" itemscope itemtype="http://schema.org/Place">
					<h3>' . __( 'Event location', 'events-manager-for-wp' ) . '</h3>';

				$embed_url = 'https://maps.google.com/maps?q=' . $latitude . ',' . $longitude . '&z=14&output=embed&iwloc=0';
				$content .= '
					<iframe src="' . esc_url( $embed_url ) . '" ';

				if ( isset( $location['width'] ) ) {
					$content .= ' width="' . esc_attr( $location['width'] ) . '"';
				}
				if ( isset( $location['height'] ) ) {
					$content .= ' height="' . esc_attr( $location['height'] ) . '"';
				}

				$content .= 'frameborder="0" allowfullscreen></iframe>';

				$content .= '
				</div>';
			}
		}

		return $content;
	}

}
