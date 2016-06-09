<?php

/**
 * Event Type.
 */
class EM4WP_Event_Type extends EM4WP_Events_Core {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		parent::__construct();

		add_action( 'init',    array( $this, 'register_taxonomy' ) );
	}

	/**
	 * Add custom taxonomy.
	 */
	public function register_taxonomy() {
		register_taxonomy(
			'event-type',
			'event',
			array(
				'label'        => __( 'Event Type' ),
				'rewrite'      => array( 'slug' => 'event-type' ),
				'hierarchical' => false,
			)
		);
	}

}
