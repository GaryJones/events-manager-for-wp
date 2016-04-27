<?php

/**
 * Event Calendar Core.
 */
class EM4WP_Events_Core {

	public $event_slug;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->event_slug = __( 'event', 'events-manager-for-wp' );
	}

}
