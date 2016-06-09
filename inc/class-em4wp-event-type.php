<?php

/**
 * Event Type.
 */
class EM4WP_Event_Type extends EM4WP_Events_Core {

	public $taxonomy_slug = 'event-type';

	/**
	 * Class constructor.
	 */
	public function __construct() {

		parent::__construct();

		add_action( 'init',           array( $this, 'register_taxonomy' ) );
		add_filter( 'the_content',    array( $this, 'the_content' ), 29 );
	}

	/**
	 * Add custom taxonomy.
	 */
	public function register_taxonomy() {
		register_taxonomy(
			$this->taxonomy_slug,
			'event',
			array(
				'label'        => __( 'Event Type' ),
				'rewrite'      => array( 'slug' => $taxonomy_slug ),
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
			'taxonomy'   => $this->taxonomy_slug,
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
