=== Events Manager for WordPress ===
Contributors: forsitemedia
Donate link: https://forsite.media/
Tags: events, event, manager
Requires at least: 4.5
Tested up to: 4.6
Stable tag: 1.1.0
Text Domain: events-manager-for-wp
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A full featured events plugin, including recurring events, maps/location and multisite support.

== Description ==

= Features =
* Simple event registration
* Recurring events
* Multisite support
* Genesis support
* Location / maps support
* Event categories
* Events widget

= Settings =
* Event slug shown in URLs
* Archive slug shown in URLs
* Taxonomy slug shown in URLs
* URL for landing page listing events


== Installation ==

Install and activate the plugin. An "Events" menu item will appear in the WordPress admin panel, from which you can add events and alter the URL setup for the plugin.

= Adding events =
An event needs to have it's title, description (main post content) and start/end times set. You may also set an event to be a whole day event, and set a location to display a map on the event.

= Future events =
To create an event scheduled for the future, simply set the start time to a future time.

= Displaying a calendar =
A calendar can be displayed on a page through use of the `[events-calendar]` shortcode.

= Widget =
The plugin includes an upcoming events widget. The widget includes settings for the title, number of events and button text for the "view all events" button.

= Modifying schema.org markup =

The following example will allow you to edit the schema.org event description markup.
```<?php

add_filter( 'the_content', 'replace_markup' );
function replace_markup( $content ) {
    $content = str_replace( '<div itemprop="description">', '<div itemprop="something-else">', $content );
    return $content;
}```

== Support ==
If you would like to file a bug report or ask a question, please do so in the WordPress.org support forums.

== Changelog ==

= 1.1 (2016-07-06) =
* Initial release on WordPress.org

= 1.0 (2016-06-15) =
* Initial test release
