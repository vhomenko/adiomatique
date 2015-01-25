<?php
/**
 * Plugin Name: Adiomatique
 * Description: Einfache Terminverwaltung.
 * Version: 7
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

date_default_timezone_set( 'Europe/Berlin' );

require_once( 'util.php' );
require_once( 'admin_page.php' );
require_once( 'admin_post.php' );

const ADI_ACTIVITY_PARENT_PAGE_ID = 553;
const ADI_ACTIVITY_ARCHIVE_PAGE_ID = 388;
const ADI_START_PAGE_ID = 17;
const ADI_EVENTS_PAGE_ID = 1563;


if ( defined( 'ADIOMATIQUE_DEV' ) ) {
	require_once( 'dev_settings.php' );
} else {
	require_once( 'settings.php' ); 
}



add_filter( 'the_content', 'adi_add_event_data', 20 );

function adi_add_event_data( $content ) {
	
	$id = get_the_ID();
		
	$titlepage_cat_id = intval( get_post_meta( $id, 'adi_titlepage_cat_id', true ) );

	$adi_event_timestamp = intval( get_post_meta( $id, 'adi_event_timestamp', true ) );
	
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
		
		$output = '<p>';
		
		foreach ( $events as $event ) {
			$output .= $event['date'] . ', ';
			$output .= $event['time'] . ' Uhr &nbsp;&nbsp;&nbsp;' . $event['link'] . ' &nbsp;&nbsp;&nbsp;';
		
			if ( ! empty( $event['location'] ) ) 
				$output .= '<i>extern</i>';
			else if ( 0 < $event['periodicity'] ) 
				$output .= '<i>regelmäßig</i>';

			$output .= '<br>';
		}
			
		$output .= '</p>';

		return '<span>Anstehende Termine:<a class="small_right" href="' . get_category_link( $titlepage_cat_id ) . '">Alle Termine ansehen</a><br>' . $output . '</span>';

}


function adi_display_post( $id, $adi_event_timestamp ) {

	$new_date = adi_update_event( $id );
	
	if ( $new_date ) {
		$datetime = $new_date;
	} else {
		$datetime = new DateTime( '@' . $adi_event_timestamp );
		$datetime->setTimezone( new DateTimeZone( 'Europe/Berlin' ) );
	}

	$adi_event_date = $datetime->format( 'd.m' );
	$adi_event_time = $datetime->format( 'H:i' );

	$link = adi_get_titlepage_link( $id );
	
	$parent_link = ' <span class="small_right">' . $link . '</span>';
	
	if ( empty( $link ) ) $parent_link = '';

	$periodicity = adi_get_event_periodicity( $datetime, intval( get_post_meta( $id, 'adi_event_periodicity', true ) ) );

	$location = sanitize_text_field( get_post_meta( $id, 'adi_event_location', true ) );

	if ( ! empty( $location ) ) {
		$location = '<br><strong>Ort:</strong> ' . $location . '';
	}

	$type = '';
	if ( in_category( ADI_EVENTS_ARCHIVE_CAT_ID, $id ) ) {
		$type = 'Archivierter ';
	}

	return 
		'<p><strong>' . $type . 'Termin:</strong> ' .
		'am ' . $adi_event_date . ' um ' . $adi_event_time . ' Uhr' . '<i>' . $periodicity . '</i>.' . $location .
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

		$output .= '<div>';

		$display_date = true;
	
		if ( $event['weekday'] === $prev_event['weekday'] ) {
			if ( $event['weeknum'] === $prev_event['weeknum'] ) $display_date = false;
		}
	
		if ( $display_date ) {
			$output .= "<div class='adi_date'><span>" . $event['date'] . "</span><span>" . adi_get_weekday_de( $event['weekday'] ) . "</span></div>";
		}
		
		$output .= "<div class='adi_time'><span>" . $event['time'] . "</span><span>" . $event['link'] . "</span></div>";
		
		
		$type = '';
		$type_fname = '';

		$output .= '<!-- huzza ' . $event['location'] . ' -->';
		
		if ( ! empty( $event['location'] ) ) {
			$type = "extern";
			$type_fname = 'external.png';
		} else if ( 0 < $event['periodicity'] ) {
			$type = "regelmäßig";
			$type_fname = 'periodic.png';
		}

		$type_template = '';

		if ( '' !== $type ) {
			$type_template = "<img class='" . ( '' !== $type ? 'adi_type_img' : '') . "' src='" . plugins_url() . "/adiomatique/images/" . $type_fname . "' alt='" . $type . "'>";
		}

		$periodicity = $event['periodicity_formatted'];
		$periodicity_click = " onclick=\"javascript:alert(' " . $event['title'] . ":\\n" . $periodicity . "')\"";
		
		if ( 0 == $event['periodicity'] ) {
			$periodicity_click = '';
		}

		$link = adi_get_titlepage_link( $event['id'] );

		$sub = "<div class='adi_type'><span " . ( "" === $periodicity_click ? "" : "style='cursor:pointer;'" ) . $periodicity_click . ">" . $type_template . "</span><span>" . $link . "</span>";
		
		$sub .= "</div>";

		if ( '' !== $type || ! empty( $link ) ) {
			$output .= $sub;
		}
		
		$output .= '</div>';

		$prev_event = $event;
	}

	$output .= '</div><!--/aditue-->';

	return $output;
}



add_filter( 'hidden_meta_boxes', 'adi_hide_meta_boxes', 10, 2 );

function adi_hide_meta_boxes( $hidden, $screen ) {
	
	global $post;
	
	if ( empty( $post ) ) return $hidden;

	$adi_is_titlepage = get_post_meta( $post->ID, 'adi_is_titlepage', true );
	$adi_event_timestamp = get_post_meta( $post->ID, 'adi_event_timestamp', true );

	if ( 'post' == $screen->post_type && ! empty( $adi_event_timestamp ) ) {
		$hidden = array('postexcerpt', 'slugdiv', 'trackbacksdiv', 'commentstatusdiv', 'commentsdiv', 'authordiv', 'revisionsdiv', 'categorydiv', 'postcustom');
	} else if ( 'page' == $screen->post_type && ! empty( $adi_is_titlepage ) ) {
		$hidden = array('postexcerpt', 'slugdiv', 'trackbacksdiv', 'commentstatusdiv', 'commentsdiv', 'authordiv', 'revisionsdiv', 'pageparentdiv', 'postcustom');
	}
	
	return $hidden;
	
}



add_filter( 'the_generator', 'wpgenny_remove_version' );

function wpgenny_remove_version() {

	return '';

}



add_filter( 'pre_get_posts', 'filterRSSQuery' );

function filterRSSQuery( $query ) {

   	if ( $query->is_feed ) {
		$query->set( 'cat', ADI_NEWS_CAT_ID );
 	}

	return $query;

}

