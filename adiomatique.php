<?php
/**
 * Plugin Name: Adiomatique
 * Description: Einfache Terminverwaltung.
 * Version: 3
 * Author: Vitali Homenko
 * Author URI: mailto:vitali.homenko@gmail.com
 * License: GPL-3.0
 

	Adiomatique – simple event management for WordPress
	Copyright (C) 2015 Vitali Homenko

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.


 */
 
defined( 'ABSPATH' ) or die( '' );

require_once( 'adi_util.php' );
require_once( 'adi_admin_pages.php' );
require_once( 'adi_admin_posts.php' );


/*TODO

for better output structure of terminübersicht:
* a block for date line
* a block for time and title
* a block for special and the rest

 */
 
const ADI_ACTIVITY_PARENT_PAGE_ID = 553;
const ADI_ACTIVITY_ARCHIVE_PAGE_ID = 388;

/*
// live values:
const ADI_NEWS_CAT_ID = 25;
const ADI_EVENTS_CAT_ID = 26;
const ADI_EVENTS_ARCHIVE_CAT_ID = 37;
const ADI_INDEPENDENT_EVENTS_CAT_ID = 33;
*/


// test values
const ADI_NEWS_CAT_ID = 2;
const ADI_EVENTS_CAT_ID = 5;
const ADI_EVENTS_ARCHIVE_CAT_ID = 6;
const ADI_INDEPENDENT_EVENTS_CAT_ID = 9;



add_filter( 'the_content', 'adi_add_event_data', 20 );

function adi_add_event_data( $content ) {
	
	$id = get_the_ID();
		
	$adi_event_timestamp = intval( get_post_meta( $id, 'adi_event_timestamp', true ) );
	
	$titlepage_cat_id = intval( get_post_meta( $id, 'adi_titlepage_cat_id', true ) );

	if ( 0 !== $titlepage_cat_id ) {
		$content = adi_display_page( $id, $titlepage_cat_id ) . $content;
	} else if ( 0 !== $adi_event_timestamp ) {
		$content = adi_display_post( $id, $adi_event_timestamp ) . $content;
	}
	
	return $content;
}


function adi_display_page( $id, $titlepage_cat_id ) {

		if ( adi_page_is_in_archive( $id ) ) {
			return 'Diese Aktivität wurde archiviert.';
		}
			
		$events = adi_get_events( $titlepage_cat_id, false );
		
		if ( 0 === count( $events ) ) {
			return;
		}
		
		$output = '<p>' . $titlepage_cat_id;
		
		foreach ( $events as $event ) {
			$output .= $event['date'] . ', ';
			$output .= $event['time'] . ' Uhr &nbsp;&nbsp;&nbsp;' . $event['link'] . ' &nbsp;&nbsp;&nbsp;';
		
			if ( 25 == $event['special'] ) 
				$output .= '<i>extern</i>';
			else if ( 0 < $event['periodicity'] ) 
				$output .= '<i>regelmäßig</i>';

			$output .= '<br>';
		}
			
		$output .= '</p>';

		return '<span>Anstehende Termine:<a class="small_right" href="' . get_category_link( $titlepage_cat_id ) . '">Alle Termine ansehen</a><br>' . $output . '</span>';

}


function adi_display_post( $id, $adi_event_timestamp ) {

	$new_ts = adi_update_event( $id );
	
	if ( 0 !== $new_ts ) {
		$adi_event_timestamp = $new_ts;
	}
	
	$datetime = new DateTime( '@' . $adi_event_timestamp );
	$adi_event_date = $datetime->format( 'd.m' );
	$adi_event_time = $datetime->format( 'H:i' );

	$link = adi_get_titlepage_link( $id );
	
	$parent_link = ' <span class="small_right">' . $link . '</span>';
	
	if ( empty( $link ) ) $parent_link = '';

	$periodicity = adi_get_event_periodicity( $adi_event_timestamp, intval( get_post_meta( $id, 'adi_event_periodicity', true ) ) );

	$special = adi_get_event_special( intval( get_post_meta( $id, 'adi_event_special', true ) ) );

	if ( in_category( ADI_EVENTS_ARCHIVE_CAT_ID, $id ) ) {
		$special = 'Diese Terminankündigung ist archiviert.';
	}

	return 
		'<p>' . $special . '</p>' .
		'<p><strong>Termin:</strong> ' .
		'am ' . $adi_event_date . ' um ' . $adi_event_time . ' Uhr' . '<i>' . $periodicity . '</i>.' . 
		$parent_link . '</p>';

}




add_shortcode( 'adi_termine', 'adi_the_events' );

function adi_the_events( $atts ) {

	wp_enqueue_style( 'adiomatique-css', plugins_url() . '/adiomatique/css/adiomatique.css' );

	$a = shortcode_atts( array(
		'zeige_tage' => '8',
	), $atts );

	if ( 'alle' === $a['zeige_tage'] ) {
		$output_limit = false;
	} else {
		$output_limit = intval( $a['zeige_tage'] );
	}
	
	$output = "<div id='aditue'>";

	$events = adi_get_events( ADI_EVENTS_CAT_ID, $output_limit );

	$prev_event = null;

	foreach ( $events as $event ) {
		// TODO: make prepping of blocks into functions

		$output .= '<div>';

		$display_date = true;
	
		if ( $event['weekday'] === $prev_event['weekday'] ) {
			if ( $event['weeknum'] === $prev_event['weeknum'] ) $display_date = false;
		}
	
		if ( $display_date ) {
			$output .= "<div class='adi_date'><span>" . $event['date'] . "</span><span>" . adi_get_weekday_de( $event['weekday'] ) . "</span></div>";
		}

		
		$special_style = '';
		
		if ( 100 == $event['special'] ) {
			$special_style = ' adi_wichtig';
		} else if ( 75 == $event['special'] ) {
			$special_style = ' adi_geaendert';
		}
		
		$output .= "<div class='adi_time" . $special_style . "'><span>" . $event['time'] . "</span><span>" . $event['link'] . "</span></div>";
		
		
		$special = '';
		$special_fname = '';
		
		if ( 25 == $event['special'] ) {
			$special = "extern";
			$special_fname = 'external.png';
		} else if ( 0 < $event['periodicity'] ) {
			$special = "regelmäßig";
			$special_fname = 'periodic.png';
		}

		$special_template = "<img class='" . ( '' !== $special ? 'adi_special_img' : '') . "' src='" . plugins_url() . "/adiomatique/images/" . $special_fname . "' alt='" . $special . "'>";

		if ( '' === $special ) 
			$special_template = '';

		$periodicity = adi_get_event_periodicity( $event['timestamp'], $event['periodicity'] );
		$periodicity_click = " onclick=\"javascript:alert(' " . $event['title'] . ":\\n" . $periodicity . "')\"";
		
		if ( 0 == $event['periodicity'] ) {
			$periodicity_click = '';
		}

		$link = adi_get_titlepage_link( $event['id'] );

		$sub = "<div class='adi_special'><span " . ( "" === $periodicity_click ? "" : "style='cursor:pointer;'" ) . $periodicity_click . ">" . $special_template . "</span><span>" . $link . "</span>";

		if ( 75 == $event['special'] ) 
			$sub .= "<span class='adi_geaendert'> Diese Ankündigung wurde geändert!</span>";
		
		$sub .= "</div>";

		if ( '' !== $special || ! empty( $link ) || 75 == $event['special'] ) {
			$output .= $sub;
		}
		
		$output .= '</div>';

		$prev_event = $event;
	}

	$output .= '</div><!--/aditue-->';

	return $output;
}
