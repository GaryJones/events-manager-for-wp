<?php

/**
 * Frontend view of event.
 */
class EM4WP_Frontend extends EM4WP_Events_Core {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'init',               array( $this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'css' ) );
	}

	/**
	 * init.
	 */
	public function init() {
		if ( function_exists( 'genesis' ) ) {
//			add_action( 'genesis_entry_content', array( $this, 'genesis_wrapper_beginning' ), 29 );
			add_action( 'genesis_entry_content', array( $this, 'genesis_content' ), 29 );
		} else {
			add_filter( 'the_content',    array( $this, 'the_content' ), 29 );
			add_filter( 'the_content',    array( $this, 'the_content_wrapper' ), 50 );
			add_filter( 'the_content',    array( $this, 'the_content_description_wrapper' ), 10 );
		}
	}

	/**
	 * Adding content differently for Genesis.
	 * We use a hook here for Genesis instead of the normal the_content() filter.
	 */
	public function genesis_content() {
		$content = $this->the_content( '' );
		echo $content;
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
			' . __( 'Start time: ', '' ) . '
			<time itemprop="startDate" content="2016-06-11T14:30">' . date( get_option( 'date_format' ), $start );

		if ( 1 != $allday ) {
			$content .= ', ' . date( 'H:i:s', $start );
		}

		$content .= '</time>';

		// Show the end date/time
		if ( '' != $end ) {
			$content .= '
			' . __( 'End time: ', '' ) . '
			<time itemprop="endDate" content="2016-06-11T17:00">' . date( get_option( 'date_format' ), $end );

			if ( 1 != $allday ) {
				$content .= ', ' . date( 'H:i:s', $end );
			}

			$content .= '</time>';
		}

		$content .= '
		</div>';

		return $content;
	}

	/**
	 * the_content() wrapper filter.
	 * Wraps the content in a schema.org markup div.
	 * This will break some sites, but users may unhook this filter if required.
	 *
	 * @param  string  $content  The post content
	 * @return string  The modified post content
	 */
	public function the_content_description_wrapper( $content ) {

		// Bail out now if not on event post-type
		if ( 'event' != get_post_type() ) {
			return $content;
		}

		$content = '<div itemprop="description">' . $content . '</div>';

		return $content;
	}

	/**
	 * the_content() wrapper filter.
	 * Wraps the content in a schema.org markup div.
	 * This will break some sites, but users may unhook this filter if required.
	 *
	 * @param  string  $content  The post content
	 * @return string  The modified post content
	 */
	public function the_content_wrapper( $content ) {

		// Bail out now if not on event post-type
		if ( 'event' != get_post_type() ) {
			return $content;
		}

		$content = '<div itemscope itemtype="http://schema.org/Event">' . $content . '</div>';

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
