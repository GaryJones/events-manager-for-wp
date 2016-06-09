<?php

/**
 * Frontend view of event.
 */
class EM4WP_Frontend extends EM4WP_Events_Core {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		parent::__construct();

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

		$start = get_post_meta( get_the_ID(), 'em4wp_event_start', true );
		$end = get_post_meta( get_the_ID(), 'em4wp_event_end', true );
		$allday = get_post_meta( get_the_ID(), 'em4wp_event_allday', true );

		// Show the start date/time
		$content .= '
		<div class="em4wp-date">
			' . __( 'Start time: ', '' ) . date( get_option( 'date_format' ), $start );
		if ( 1 != $allday ) {
			$content .= ', ' . date( 'H:i:s', $start );
		}

		// Show the end date/time
		if ( '' != $end ) {
			echo '
				' . __( 'End time: ', '' ) . date( get_option( 'date_format' ), $end );
			if ( 1 != $allday ) {
				$content .= ', ' . date( 'H:i:s', $end );
			}
		}

		echo '
		</div>';

		return $content;
/*
		EVENT TYPE
		MORE INFORMATION LINK
*/
	}

	/**
	 * Adding CSS for event posts.
	 */
	public function css() {
		if ( 'event' != get_post_type() ) {
			return;
		}

		//add CSS enqueue here

	}

}
