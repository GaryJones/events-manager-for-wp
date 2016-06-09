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
	const OPTION    = 'em4wp-option';

	/**
	 * Fire the constructor up :)
	 */
	public function __construct() {

		// Add to hooks
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'create_admin_page' ) );
	}

	/**
	 * Init plugin options to white list our options.
	 */
	public function register_settings() {
		register_setting(
			self::GROUP,               // The settings group name
			self::OPTION,              // The option name
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
			<h1><?php _e( 'Events settings', 'events-manager-for-wp' ); ?></h1>
			<p><?php _e( 'Control the events settings here.', 'events-manager-for-wp' ); ?></p>

			<form method="post" action="options.php">

				<table class="form-table">
					<tr>
						<th>
							<label for="<?php echo esc_attr( self::OPTION ); ?>"><?php _e( 'Enter your input string.', 'events-manager-for-wp' ); ?></label>
						</th>
						<td>
							<input type="text" id="<?php echo esc_attr( self::OPTION ); ?>" name="<?php echo esc_attr( self::OPTION ); ?>" value="<?php echo esc_attr( get_option( self::OPTION ) ); ?>" />
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
	 * @return  array             The sanitized string
	 */
	public function sanitize( $input ) {
		$output = wp_kses_post( $input );
		return $output;
	}

}
