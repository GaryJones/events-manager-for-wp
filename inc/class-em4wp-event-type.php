<?php

/**
 * Event Type.
 */
class EM4WP_Event_Type extends EM4WP_Events_Core {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'init',           array( $this, 'register_taxonomy' ) );
		add_filter( 'the_content',    array( $this, 'the_content' ), 29 );
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
				'rewrite'      => array( 'slug' => $this->get_option( 'permalink-taxonomy') ),
				'hierarchical' => false,
			)
		);
	}

	/**
	 * the_content() filter.
	 *
	 * @param  string  $content  The post content
	 * @return string  The modified post content
	 */
	public function the_content( $content ) {

		if ( 'event' != get_post_type() ) {
			return $content;
		}

		$terms = get_terms( array(
			'taxonomy'   => 'event-type',
			'hide_empty' => false,
		) );

		if ( $terms ) {
			$content .= '
			<div class="em4wp-one-half">
				<h3>' . __( 'Event type', 'events-manager-for-wp' ) . '</h3>
				<ul>';

			foreach ( $terms as $term ) {
				$url = get_term_link( $term->term_id );
				$content .= '
					<li>
						<a href="' . esc_url( $url ) . '">
							' . esc_html( $term->name ) . '
						</a>
					</li>';
			}

			$content .= '
				</ul>
			</div>';
		}

		return $content;
	}

}
