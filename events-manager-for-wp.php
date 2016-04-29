<?php
/*
Plugin Name: Events Manager for WordPress
Plugin URI: https://forsite.media/
Description: Events Manager for WordPress
Version: 1.0
Author: Forsite Media
Author URI: https://forsite.media/
License: GPLv2 or later
Text Domain: events-manager-for-wordpress
*/

/*

Plugins code is based on BE Events Calendar (c) 2014, Bill Erickson <bill@billerickson.net>.
https://github.com/billerickson/BE-Events-Calendar

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/

// Define plugin constants
define( 'EM4WP_EVENTS_CALENDAR_VERSION', '1.1.0' );
define( 'EM4WP_EVENTS_CALENDAR_DIR', plugin_dir_path( __FILE__ ) );
define( 'EM4WP_EVENTS_CALENDAR_URL', plugin_dir_url( __FILE__ ) );
define( 'EM4WP_EVENTS_CALENDAR_PLUGIN_FILE', __FILE__ );

/**
 * Autoload the classes.
 * Includes the classes, and automatically instantiates them via spl_autoload_register().
 *
 * @param  string  $class  The class being instantiated
 */
function autoload_em4wp( $class ) {

	// Bail out if not loading a Media Manager class
	if ( 'EM4WP_' != substr( $class, 0, 6 ) || class_exists( $class ) ) {
		return;
	}

	// Convert from the class name, to the classes file name
	$file_data = strtolower( $class );
	$file_data = str_replace( '_', '-', $file_data );
	$file_name = 'class-' . $file_data . '.php';

	// Get the classes file path
	$dir = dirname( __FILE__ );
	$path = $dir . '/inc/' . $file_name;

	// Include the class (spl_autoload_register will automatically instantiate it for us)
	require( $path );
}
spl_autoload_register( 'autoload_em4wp' );

new EM4WP_Events_Calendar;
new EM4WP_Recurring_Events;
new EM4WP_Genesis_Schema;
new EM4WP_Events_Calendar_View;
new EM4WP_Upcoming_Events;
new EM4WP_Locations;
new EM4WP_Events_Archive;
