<?php

/**
 * Settings Page.
 */
class EM4WP_Settings extends EM4WP_Events_Core {

	/**
	 * Set some constants for setting options.
	 */
	const MENU_SLUG = 'em4wp-page';
	const GROUP     = 'em4wp-group';

	/**
	 * Fire the constructor up :)
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'create_admin_page' ) );
	}

	/**
	 * Init plugin options to white list our options.
	 */
	public function register_settings() {
		register_setting(
			self::GROUP,               // The settings group name
			$this->slug,               // The option name
			array( $this, 'sanitize' ) // The sanitization callback
		);
	}

	/**
	 * Create the page and add it to the menu.
	 */
	public function create_admin_page() {

		add_submenu_page(
			'edit.php?post_type=event',
			__ ( 'Settings', 'events-manager-for-wp' ), // Page title
			__ ( 'Settings', 'events-manager-for-wp' ),       // Menu title
			'manage_options',                                     // Capability required
			basename(__FILE__),                                   // ???
			array( $this, 'admin_page' )                          // Displays the admin page
		);

	}

	/**
	 * Output the admin page.
	 */
	public function admin_page() {

		?>
		<div class="wrap">
			<h1><?php _e( 'Events Manager for WordPress settings', 'events-manager-for-wp' ); ?></h1>
			<p><?php _e( 'Control the events settings here.', 'events-manager-for-wp' ); ?></p>

			<form method="post" action="options.php">

				<table class="form-table">

					<tr>
						<th>
							<label for="<?php echo esc_attr( $this->slug ); ?>[permalink-slug]"><?php _e( 'The Single Event Slug', 'events-manager-for-wp' ); ?></label>
						</th>
						<td>
						<p><?php _e( 'This is what comes before a single event slug shown in URLs.', 'events-manager-for-wp' ); ?></p>
							<input type="text" id="<?php echo esc_attr( $this->slug ); ?>[permalink-slug]" name="<?php echo esc_attr( $this->slug ); ?>[permalink-slug]" value="<?php echo esc_attr( $this->get_option( 'permalink-slug' ) ); ?>" />
						</td>
					</tr>

					<tr>
						<th>
							<label for="<?php echo esc_attr( $this->slug ); ?>[permalink-archive]"><?php _e( 'The Archive Slug.', 'events-manager-for-wp' ); ?></label>
						</th>
						<td>
						<p><?php _e( 'Events that are in the past will be displayed at an archive slug.', 'events-manager-for-wp' ); ?></p>
							<input type="text" id="<?php echo esc_attr( $this->slug ); ?>[permalink-archive]" name="<?php echo esc_attr( $this->slug ); ?>[permalink-archive]" value="<?php echo esc_attr( $this->get_option( 'permalink-archive' ) ); ?>" />
						</td>
					</tr>

					<tr>
						<th>
							<label for="<?php echo esc_attr( $this->slug ); ?>[permalink-taxonomy]"><?php _e( 'Event Types prefix', 'events-manager-for-wp' ); ?></label>
						</th>
						<td>
						<p><?php _e( 'Event Types archives are displayed after this slug.', 'events-manager-for-wp' ); ?></p>
							<input type="text" id="<?php echo esc_attr( $this->slug ); ?>[permalink-taxonomy]" name="<?php echo esc_attr( $this->slug ); ?>[permalink-taxonomy]" value="<?php echo esc_attr( $this->get_option( 'permalink-taxonomy' ) ); ?>" />
						</td>
					</tr>

					<tr>
						<th>
							<label for="<?php echo esc_attr( $this->slug ); ?>[permalink-landing]"><?php _e( 'Events Overview slug', 'events-manager-for-wp' ); ?></label>
						</th>
						<td>
						<p><?php _e( 'The Overview of Events are displayed at this slug.', 'events-manager-for-wp' ); ?></p>
							<input type="text" id="<?php echo esc_attr( $this->slug ); ?>[permalink-landing]" name="<?php echo esc_attr( $this->slug ); ?>[permalink-landing]" value="<?php echo esc_attr( $this->get_option( 'permalink-landing' ) ); ?>" />
						</td>
					</tr>

				</table>

				<?php settings_fields( self::GROUP ); ?>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'events-manager-for-wp' ); ?>" />
				</p>
			</form>
		</div><?php
	}

	/**
	 * Sanitize the page or product ID
	 *
	 * @param   string   $input   The input string
	 * @return  array    $output  The sanitized string
	 */
	public function sanitize( $input ) {

		$output = array();
		foreach ( $input as $key => $item ) {
			$output[$key] = wp_kses_post( $item );
		}

		return $output;
	}

}
