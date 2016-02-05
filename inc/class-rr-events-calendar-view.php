<?php

/**
 * Calendar view.
 *
 * @link       https://github.com/billerickson/BE-Events-Calendar
 * @author     Bill Erickson <bill@billerickson.net>
 * @copyright  Copyright (c) 2015, Bill Erickson
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

class RR_Events_Calendar_View {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Setup ajax
		add_action( 'wp_ajax_rr_events_calendar',        array( $this, 'ajax' ) );
		add_action( 'wp_ajax_nopriv_rr_events_calendar', array( $this, 'ajax' ) );

		// Register shortcode
		add_shortcode( 'rr-events-calendar', array( $this, 'output' ) );
	}

	/**
	 * Creates the calendar view output.
	 */
	public function output() {

		// Load javascript assets
		wp_enqueue_script( 'moment',       RR_EVENTS_CALENDAR_URL . 'js/moment.min.js',       array( 'jquery' ),           RR_EVENTS_CALENDAR_VERSION );
		wp_enqueue_script( 'fullcalendar', RR_EVENTS_CALENDAR_URL . 'js/fullcalendar.min.js', array( 'jquery', 'moment' ), RR_EVENTS_CALENDAR_VERSION );
		
		// Setup JS vars
		$data = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'rr_events_calendar' ),
		);
		wp_localize_script( 'fullcalendar', 'rr_events_calendar', $data );

		// Load CSS unless overriden
		$css = apply_filters( 'r_events_calendar_view_css', true );
		if ( true === $css ) {
			wp_enqueue_style( 'fullcalendar', RR_EVENTS_CALENDAR_URL . 'css/fullcalendar.min.css' );
		}
		
		ob_start();

		do_action( 'rr_events_calendar_view_before' );

		// Placeholder markup for the calendar
		echo '<div id="rr-event-calendar"></div>';

		// More info - http://fullcalendar.io/docs/
		$calendar_args = array(
			'header_left'    => 'title',
			'header_center'  => '',
			'header_right'   => 'prev,next today',
			'aspectRatio'    => '2',
			'fixedWeekCount' => 'false',
		);
		$calendar_args = apply_filters( 'rr_events_calendar_view_js_args', $calendar_args );
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('#rr-event-calendar').fullCalendar({
					<?php 
					// Add hook so other args can be easily added if needed
					do_action( 'rr_events_calendar_view_js' );
					?>
					header: {
						left: '<?php echo $calendar_args['header_left']; ?>',
						center: '<?php echo $calendar_args['header_center']; ?>',
						right: '<?php echo $calendar_args['header_right']; ?>'
					},
					fixedWeekCount: <?php echo $calendar_args['fixedWeekCount']; ?>,
					aspectRatio: <?php echo $calendar_args['aspectRatio']; ?>,
					eventRender: function(event, element, view) {					
						var ntoday = new Date().getTime();
						var eventEnd = moment( event.end ).valueOf();
						var eventStart = moment( event.start ).valueOf();
						if (!event.end){
							if (eventStart < ntoday){
								element.addClass("past-event");
								element.children().addClass("past-event");
							}
						} else {
							if (eventEnd < ntoday){
								element.addClass("past-event");
								element.children().addClass("past-event");
							}
						}
						if ( event.allDay === true ) {
							element.addClass("allday-event");
							element.children().addClass("allday-event");
						}
					},
					eventSources: [
						{
							url: rr_events_calendar.ajax_url,
							type: 'POST',
							cache: true,
							data: {
								nonce: rr_events_calendar.nonce,
								action: 'rr_events_calendar'
							},
							success: function( res ) {
								// enjoy the shot
							},
							error: function( xhr, textStatus, e ) {
								if ( xhr.responseText ) {
									console.log(xhr.responseText);
								}
							}
						}
					]
				})
			});
		</script>
		<?php

		do_action( 'rr_events_calendar_view_after' );

		// Let's kick it.
		$output = ob_get_clean();
		return $output;
	}

	/**
	 * Event calendar ajax.
	 *
	 * Returns events within a specific window in JSON format.
	 */
	public function ajax() {

		check_ajax_referer( 'rr_events_calendar', 'nonce' );

		$start       = $_POST['start'];
		$start_unix  = strtotime( $start );
		$end         = $_POST['end'];
		$end_unix    = strtotime( $end );
		$calendar    = array();
		$events_args = array (
			'post_type' => 'event',
			'meta_query' => array(
				array(
					'key'     => 'rr_event_start',
					'value'   => array( $start_unix, $end_unix ),
					'type'    => 'NUMERIC',
					'compare' => 'BETWEEN',
				)
			),
		);									
		$events = new WP_Query( $events_args ); while( $events->have_posts() ) : $events->the_post();
			$all_day         = false;
			$start_timestamp = get_post_meta( get_the_ID(), 'rr_event_start', true );
			$start_time      = date( 'g:iA', $start_timestamp );
			$end_timestamp   = get_post_meta( get_the_ID(), 'rr_event_end', true );
			$end_time        = date( 'g:iA', $end_timestamp );

			// Determine if the event is "all day". If the editor selects "All Day" checkbox,
			// the start time will be set to 12:01 AM and end time to 11:59 PM automatically.
			if ( $start_time == '12:01AM' && $end_time == '11:59PM' ) {
				$all_day = true;
			}

			$calendar[] = array(
				'title'  => get_the_title(),
				'start'  => date( 'c', $start_timestamp ),
				'end'    => date( 'c', $end_timestamp ),
				'url'    => get_permalink(),
				'allDay' => $all_day,
			);
		endwhile;

		echo json_encode( $calendar );
		die;
	}
}
