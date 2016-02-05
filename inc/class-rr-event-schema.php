<?php

/**
 * Schema for Genesis
 *
 * @link       https://github.com/billerickson/BE-Events-Calendar
 * @author     Bill Erickson <bill@billerickson.net>
 * @copyright  Copyright (c) 2014, Bill Erickson
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
 
class RR_Event_Schema {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );	
	}
	
	/**
	 * Initialize and go.
	 */
	public function init() {

		add_filter( 'genesis_attr_content', array( $this, 'empty_schema' ), 20 );
		add_filter( 'genesis_attr_entry', array( $this, 'event_schema' ), 20 );
		add_filter( 'genesis_attr_entry-title', array( $this, 'event_name_itemprop' ), 20 );
		add_filter( 'genesis_attr_entry-content', array( $this, 'event_description_itemprop' ), 20 );
		add_filter( 'genesis_post_title_output', array( $this, 'title_link' ), 20 );
		add_action( 'genesis_entry_header', array( $this, 'event_date' ) );
	}
	
	/**
	 * Empty Schema.
	 *
	 * @param array $attr
	 * @return array
	 */
	public function empty_schema( $attr ) {
	
		// Only run on events archive
		if( !is_post_type_archive( 'event' ) )
			return $attr;
			
		$attr['itemtype'] = '';
		$attr['itemprop'] = '';
		$attr['itemscope'] = '';
		return $attr;	
	}
	
	/**
	 * Event Schema.
	 *
	 * @param array $attr
	 * @return array
	 */
	public function event_schema( $attr ) {

		// Only run on event
		if( ! 'event' == get_post_type() )
			return $attr;
			
		$attr['itemtype'] = 'http://schema.org/Event';
		$attr['itemprop'] = '';
		$attr['itemscope'] = 'itemscope';
		return $attr;
	}

	/**
	 * Event Name Itemprop.
	 *
	 * @param array $attr
	 * @return array
	 */
	public function event_name_itemprop( $attr ) {
		if( 'event' == get_post_type() )
			$attr['itemprop'] = 'name';
		return $attr;
	}
	
	/**
	 * Event Description Itemprop.
	 * 
	 * @param array $attr
	 * @return array
	 */
	public function event_description_itemprop( $attr ) {
		if( 'event' == get_post_type() )
			$attr['itemprop'] = 'description';
		return $attr;
	}
	
	/**
	 * Title Link.
	 * 
	 * @param string $output
	 * @return string
	 */
	public function title_link( $output ) {
		if( 'event' == get_post_type() )
			$output = str_replace( 'rel="bookmark"', 'rel="bookmark" itemprop="url"', $output );
		return $output;
	}
	
	/**
	 * Event Date.
	 */
	public function event_date() {
		if ( 'event' !== get_post_type() ) {
			return;
		}
			
		$start = get_post_meta( get_the_ID(), 'rr_event_start', true );
		if ( $start ) {
			echo '<meta itemprop="startDate" content="' . date('c', $start ).'">';
		}
		
		$end = get_post_meta( get_the_ID(), 'rr_event_end', true );
		if ( $end ) {
			echo '<meta itemprop="endDate" content="' . date( 'c', $end ).'">';
		}
		
	}
}
