<?php

/**
 * Frontend view of event.
 */
class EM4WP_Frontend extends EM4WP_Events_Core {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		add_filter( 'the_content', array( $this, 'the_content' ), 25 );
		add_action( 'wp_enqueue_scripts', array( $this, 'css' ) );

	}

	/**
	 * the_content() filter.
	 *
	 * @param  string  $content  The post content
	 * @return string  The modified post content
	 */
	public function the_content( $content ) {

		// Bail out now if not on event post-type
		if ( 'event' != get_post_type() ) {
			return $content;
		}

		$start = get_post_meta( get_the_ID(), '_event_start', true );
		$end = get_post_meta( get_the_ID(), '_event_end', true );
		$allday = get_post_meta( get_the_ID(), '_event_allday', true );

		// Show the start date/time
		$content .= '
		<div class="em4wp-one-half">
			<h3>' . __( 'Date', 'events-manager-for-wp' ) . '</h3>
			' . __( 'Start time: ', '' ) . date( get_option( 'date_format' ), $start );
		if ( 1 != $allday ) {
			$content .= ', ' . date( 'H:i:s', $start );
		}

		// Show the end date/time
		if ( '' != $end ) {
			$content .= '
				' . __( 'End time: ', '' ) . date( get_option( 'date_format' ), $end );
			if ( 1 != $allday ) {
				$content .= ', ' . date( 'H:i:s', $end );
			}
		}

		$content .= '
		</div>';

		return $content;
	}

	/**
	 * Adding CSS for event posts.
	 */
	public function css() {

		// Bail out if not on event post-type
		if ( 'event' != get_post_type() ) {
			return;
		}

		$css_url = plugins_url( 'css/events-single.css', dirname(__FILE__) );
		wp_enqueue_style( 'em4wp-css', $css_url );

	}

}
