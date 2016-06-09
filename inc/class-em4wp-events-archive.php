<?php

/**
 * Event Archive.
 */
class EM4WP_Events_Archive extends EM4WP_Events_Core {

	public $archive_slug;

	/**
	 * Class constructor.
	 */
	public function __construct() {

		$this->archive_slug = __( 'archive', 'events-manager-for-wp' );

		add_action( 'init', array( $this, 'rewrite_endpoint' ) );

		add_filter(
			'query_vars',
			function( $vars ) {
				$vars[] = $this->archive_slug;
				return $vars;
			}
		);

		add_action( 'pre_get_posts', array( $this, 'modify_archive_query' ), 11 );

	}

	function rewrite_endpoint() {
		// Adding rewrite rule for URL's
		add_rewrite_rule( $this->get_option( 'permalink-slug' ) . '/' . $this->archive_slug . '/?$', 'index.php?post_type=event&' . $this->archive_slug . '=1', 'top' );
	}

	/**
	 * Modify the archive query.
	 *
	 * @param  object  $query  The main page query
	 * @return object  The modified main page query
	 */
	function modify_archive_query( $query ) {

		if ( '' != get_query_var( 'archive' ) && $query->is_main_query() && !is_admin() && ( is_post_type_archive( 'event' ) || is_tax( 'event-category' ) ) ) {	

			$meta_query = array(
				array(
					'key' => '_event_end',
					'value' => (int) current_time( 'timestamp' ),
					'compare' => '<'
				)
			);
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'ASC' );
			$query->set( 'meta_query', $meta_query );
			$query->set( 'meta_key', '_event_start' );
		}

		return $query;
	}

}
