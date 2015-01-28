<?php
/**
 * Plugin Name: Adiomatique
 * Description: Einfache Terminverwaltung.
 * Version: 10
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

const ADI_TZ = 'Europe/Berlin';

date_default_timezone_set( ADI_TZ );

require_once( 'admin_page.php' );
require_once( 'admin_post.php' );
require_once( 'EventManager.php' );


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
	if ( 0 !== $adi_event_timestamp ) {
		$content = adi_display_post( $id ) . $content;
	} else if ( 0 !== $titlepage_cat_id ) {
		$content = adi_display_page( $titlepage_cat_id ) . $content;
	}
	return $content;
}


function adi_display_page( $titlepage_cat_id ) {
	global $post;
	if ( ADI_ACTIVITY_ARCHIVE_PAGE_ID === $post->post_parent ) {
		return 'Dieses Projekt ist archiviert.';
	}

	$events = new EventManager( $titlepage_cat_id );

	if ( $events->isEmpty() ) {
		return;
	}

	$output = '<p>';

	foreach ( $events as $e ) {
		$output .= $e->getDate() . ', ';
		$output .= $e->getTime() . ' Uhr &nbsp;&nbsp;&nbsp;' . $e->getLink() . ' &nbsp;&nbsp;&nbsp;';

		if ( $e->isExternal() ) 
			$output .= '<i>extern</i>';
		else if ( $e->isPeriodic() ) 
			$output .= '<i>regelmäßig</i>';

		$output .= '<br>';
	}

	$output .= '</p>';

	return '<span>Anstehende Termine:<a class="small_right" href="' . get_category_link( $titlepage_cat_id ) . '">Alle Termine ansehen</a><br>' . $output . '</span>';

}


function adi_display_post( $id ) {

	$e = new Event( $id );

	$link = $e->getTitlepageLink();
	$parent_link = ' <span class="small_right">' . $link . '</span>';
	if ( empty( $link ) ) $parent_link = '';

	$periodicity = $e->getPeriodicityDesc();

	$location = $e->getLocation();

	if ( $e->isExternal() ) {
		$location = '<br><strong>Ort:</strong> ' . $location . '';
	}

	$type = '';
	if ( $e->isArchived() ) {
		$type = 'Archivierter ';
	}

	return
		'<p><strong>' . $type . 'Termin:</strong> ' .
		'am ' . $e->getDate() . ' um ' . $e->getTime() . ' Uhr' . '<i>' . $periodicity . '</i>.' . $location .
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

	$events = new EventManager( ADI_EVENTS_CAT_ID, $output_limit );

	//print_r( $events );

	$prevE = null;

	foreach ( $events as $e ) {

		$output .= '<div>';

		if ( ! $e->isOnTheSameDay( $prevE ) ) {
			$output .= "<div class='adi_date'><span>" . $e->getDate() . "</span><span>" . $e->getWeekDay() . "</span></div>";
		}

		$output .= "<div class='adi_time'><span>" . $e->getTime() . "</span><span>" . $e->getLink() . "</span></div>";

		$type = '';
		$type_fname = '';

		if ( ! empty( $e->getLocation() ) ) {
			$type = "extern";
			$type_fname = 'external.png';
		} else if ( 0 < $e->getPeriodicity() ) {
			$type = "regelmäßig";
			$type_fname = 'periodic.png';
		}

		$type_template = '';

		if ( '' !== $type ) {
			$type_template = "<img class='" . ( '' !== $type ? 'adi_type_img' : '') . "' src='" . plugins_url() . "/adiomatique/images/" . $type_fname . "' alt='" . $type . "'>";
		}

		$periodicity = $e->getPeriodicityDesc();
		$periodicity_click = " onclick=\"javascript:alert(' " . $e->getTitle() . ":\\n" . $periodicity . "')\"";

		if ( 0 === $e->getPeriodicity() ) {
			$periodicity_click = '';
		}

		$link = $e->getTitlepageLink();

		$sub = "<div class='adi_type'><span " . ( "" === $periodicity_click ? "" : "style='cursor:pointer;'" ) . $periodicity_click . ">" . $type_template . "</span><span>" . $link . "</span>";

		$sub .= "</div>";

		if ( '' !== $type || ! empty( $link ) ) {
			$output .= $sub;
		}

		$output .= '</div>';

		$prevE = $e;
	}

	$output .= '</div><!--/aditue-->';

	return $output;
}
