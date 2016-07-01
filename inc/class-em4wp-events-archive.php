<?php

/**
 * Event Archive.
 */
class EM4WP_Events_Archive extends EM4WP_Events_Core {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'rewrite_endpoint' ) );
		add_action( 'template_redirect', array( $this, 'redirect_endpoint' ) );

		add_filter(
			'query_vars',
			function( $vars ) {
				$vars[] = $this->get_option( 'permalink-archive' );
				return $vars;
			}
		);

		add_action( 'pre_get_posts', array( $this, 'modify_archive_query' ), 11 );

	}

	public function redirect_endpoint() {
//global $wp;print_r( $wp );die;

		$url_bits = explode( '/', get_option( 'siteurl' ) );
		$site_path = $url_bits[3];

		$path = '/';
		if ( ! empty( $site_path ) ) {
			$path .= $site_path . '/';
		}
		$path .= $this->get_option( 'permalink-slug' ) . '/';

		// On primary archive URL
		if ( $path == $_SERVER['REQUEST_URI'] ) {
			$url = home_url() . '/' . $this->get_option( 'permalink-landing' ) . '/';
			wp_redirect( esc_url( $url ), 302 );
			exit;
		}

	}

	/**
	 * Rewriting the archive URLs.
	 */
	public function rewrite_endpoint() {

		add_rewrite_rule(
			$this->get_option( 'permalink-landing' ) . '/' . $this->get_option( 'permalink-archive' ) . '/?$',
			'index.php?post_type=event&' . $this->get_option( 'permalink-archive' ) . '=1',
			'top'
		);
 // add_rewrite_rule(
//		'^leaf/([0-9]+)/?',
//		'index.php?page_id=$matches[1]', 'top');

		add_rewrite_rule(
			$this->get_option( 'permalink-landing' ) . '/' . $this->get_option( 'permalink-archive' ) . '/page/([0-9]+)/?',
			'index.php?post_type=event&' . $this->get_option( 'permalink-archive' ) . '=$matches[1]',
			'top'
		);

		add_rewrite_rule(
			$this->get_option( 'permalink-landing' ) . '/?$',
			'index.php?post_type=event',
			'top'
		);
	}

	/**
	 * Modify the archive query.
	 *
	 * @param  object  $query  The main page query
	 * @return object  The modified main page query
	 */
	public function modify_archive_query( $query ) {

		if ( '' != get_query_var( $this->get_option( 'permalink-archive' ) ) && $query->is_main_query() && ! is_admin() && ( is_post_type_archive( 'event' ) || is_tax( 'event-category' ) ) ) {

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
