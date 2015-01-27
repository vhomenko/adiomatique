<?php

defined( 'ABSPATH' ) or die( '' );



function adi_get_events( $events_cat_id = ADI_EVENTS_CAT_ID, $limit = false ) {

	$args = array(
		'posts_per_page'   => -1,
		'category'         => $events_cat_id, 
		'orderby'          => 'none',
		'post_type'        => 'post',
		'post_status'      => 'publish',
		'suppress_filters' => true );

	$event_posts = get_posts( $args );

	$events = array();

	$today = new DateTime();
	$today->setTime( 0, 0 );

	if ( false !== $limit ) {
		$upper_day_limit = clone $today;
		$upper_day_limit->modify( $limit . ' days' );
	}

	foreach ( $event_posts as $post ) {

		$id = $post->ID;

		$new_date = adi_update_event( $id );

		if ( $new_date ) {
			$datetime = $new_date;
		} else {
			$event_ts = intval( get_post_meta( $id, 'adi_event_timestamp', true ) );
			$datetime = new DateTime( '@' . $event_ts );
			$datetime->setTimezone( new DateTimeZone( 'Europe/Berlin' ) );
		}

		// sort out passed non-periodic events
		if ( $datetime < $today ) continue;

		// sort out dates too far in the future
		if ( false !== $limit && $datetime > $upper_day_limit ) {
			continue;
		}

		$location = sanitize_text_field( get_post_meta( $id, 'adi_event_location', true ) );
		$periodicity = intval( get_post_meta( $id, 'adi_event_periodicity', true ) );
		$periodicity_formatted = adi_get_event_periodicity( $datetime, $periodicity );


		$event = array(
					'id' => $id,
					'timestamp' => $datetime->getTimestamp(),
					'time' => $datetime->format( 'H:i' ),
					'date' => $datetime->format( 'd.m' ),
					'weekday' => $datetime->format( 'l' ),
					'weeknum' => intval( $datetime->format( 'W' ) ),
					'location' => $location,
					'periodicity' => $periodicity,
					'periodicity_formatted' => $periodicity_formatted,
					'titlepage_id' => intval( get_post_meta( $id, 'adi_event_titlepage_id', true ) ),
					'title' => htmlspecialchars( $post->post_title, ENT_QUOTES ),
					'link' => '<a href="' . wp_get_shortlink( $id ) . '">' . $post->post_title . '</a>'
				);

		$events[$event_ts] = $event;

	}

	ksort( $events, SORT_NUMERIC );

	return $events;

}



function adi_update_event( $id ) {

	$periodicity = intval( get_post_meta( $id, 'adi_event_periodicity', true ) );

	$event_ts = intval( get_post_meta( $id, 'adi_event_timestamp', true ) );

	$event = new DateTime( '@' . $event_ts );
	$event->setTimezone( new DateTimeZone( 'Europe/Berlin' ) );

	$today = new DateTime();
	$event->setTimezone( new DateTimeZone( 'Europe/Berlin' ) );
	$today->setTime( 0, 0 );

	if ( $event < $today ) {
		if ( 0 === $periodicity ) {

			// archivate old non periodic events
			wp_set_post_terms( $id, array( ADI_EVENTS_ARCHIVE_CAT_ID ), 'category' );

		} else {
			$nd = adi_next_date( $event, $today, $periodicity );

			$new_ts = $nd->getTimestamp();

			update_post_meta( $id, 'adi_event_timestamp', $new_ts );

			return $nd;
		}
	}

}



function adi_next_date( $datetime, $today, $periodicity, $iteration_to_exclude = 0 ) {

	$today->setTime( 0, 0 );

	if ( $datetime >= $today ) return false;

	$next_date = clone $today;

	$event_weekday = $datetime->format( 'l' );

	if ( 0 === $periodicity ) {
		return false;
	} else if ( 1 === $periodicity ) {
		// weekly
		// ! strtotime/modify: if todays is Fr and you want this Tu, you'll get next Tuesday

		$next_date->modify( $event_weekday );

		if ( 0 !== $iteration_to_exclude ) {
			$day_to_exclude = new DateTime();
		}

	} else if ( 2 === $periodicity ) {
		// biweekly
		$adi_event_weeknum = intval( $datetime->format( 'W' ) );
		$is_event_weeknum_even = ( 0 === $adi_event_weeknum % 2 );
		$current_weeknum = intval( $today->format( 'W' ) );
		$is_current_weeknum_even = ( 0 === $current_weeknum % 2 );
		$both_are_on_even_weeks = $is_current_weeknum_even === $is_event_weeknum_even;

		$event_weekday_index = intval( $datetime->format( 'N' ) );
		$current_weekday_index = intval( $today->format( 'N' ) );
		$event_day_passed_in_current_week = $event_weekday_index < $current_weekday_index;
		$event_day_to_come_in_current_week = $event_weekday_index > $current_weekday_index;

		$next_date->modify( 'this ' . $event_weekday );

		if ( $event_day_passed_in_current_week ) {
			if ( $both_are_on_even_weeks ) {
				$next_date->modify( '+1 week' );
			}
		} else {
			if ( ! $both_are_on_even_weeks ) {
				$next_date->modify( '+1 week' );
			}
		}

	} else if ( 4 === $periodicity ) {
		// monthly

		$day_of_month = $datetime->format( 'j' );

		$week_index = adi_get_week_index( $day_of_month );
		$week_index_word = adi_get_week_index( $day_of_month, true );

		if ( false === $week_index ) {
			//echo '<p style="color:#900">Im Februar wird dieser monatlicher Termin wohl weg fallen, was?</p>';
			return false;
		}

		$next_date->modify( $week_index_word . ' ' . $event_weekday . ' of this month' );

		if ( $next_date < $today ) {
			$next_date->modify( $week_index_word . ' ' . $event_weekday . ' of next month' );
		}

	}

	$hour = intval( $datetime->format( 'H' ) );
	$min = intval( $datetime->format( 'i' ) );
	$next_date->setTime( $hour, $min );

	return $next_date;
}



function adi_get_week_index( $day_of_month, $return_word = false ) {

	$week_index = -1;
	$week_index_word = '';

	if ( 8 > $day_of_month ) { 
		$week_index = 1;
		$week_index_word = 'first';
	} else if ( 15 > $day_of_month ) { 
		$week_index = 2;
		$week_index_word = 'second';
	} else if ( 22 > $day_of_month ) { 
		$week_index = 3;
		$week_index_word = 'third';
	} else if ( 29 > $day_of_month ) { 
		$week_index = 4;
		$week_index_word = 'fourth';
	} else { 
		return false; 
	}

	if ( $return_word ) {
		return $week_index_word;
	}

	return $week_index;

}


function adi_get_event_periodicity( $dt, $periodicity ) {

	$weekday = adi_get_weekday_de( $dt->format( 'l' ) );

	if ( 0 === $periodicity ) {

		return;

	} else if ( 1 === $periodicity ) {

		return ' jeden ' . $weekday;

	} else if ( 2 === $periodicity ) {

		$weeknum = intval( $dt->format( 'W' ) );

		$p = ' jede ';

		if ( 0 != $weeknum % 2 ) {
			$p .= 'un';
		}

		return $p . 'gerade Woche am ' . $weekday;

	} else if ( 4 === $periodicity ) {

		$day_of_month = $dt->format( 'j' );

		return ' jeden ' . adi_get_week_index( $day_of_month ) . '. ' . $weekday . ' des Monats';

	}

}


function adi_get_titlepage_link( $id ) {

	$adi_event_titlepage_id = intval( get_post_meta( $id, 'adi_event_titlepage_id', true ) );

	$link = '';

	if ( ! empty( $adi_event_titlepage_id ) ) {

		$titlepage = get_post( $adi_event_titlepage_id );

		$link = '<a href="' . get_page_link( $titlepage->ID ) . '">' . $titlepage->post_title . '</a>';

	}

	return $link;

}


function adi_get_weekday_de( $weekday ) {

	$wochentag = '';

	if ( 'Monday' == $weekday ) {

		$wochentag = 'Montag';

	} else if ( 'Tuesday' == $weekday ) {

		$wochentag = 'Dienstag';

	} else if ( 'Wednesday' == $weekday ) {

		$wochentag = 'Mittwoch';

	} else if ( 'Thursday' == $weekday ) {

		$wochentag = 'Donnerstag';

	} else if ( 'Friday' == $weekday ) {

		$wochentag = 'Freitag';

	} else if ( 'Saturday' == $weekday ) {

		$wochentag = 'Samstag';

	} else if ( 'Sunday' == $weekday ) {

		$wochentag = 'Sonntag';

	}

	return $wochentag;

}


/* @deprecated
function adi_get_event_type( $type ) {

	if ( 1 === $type ) return 'Inaktiv';
	else if ( 25 === $type ) return 'Extern';
	else if ( 100 === $type ) return 'Wichtig';
	else if ( 75 === $type ) return 'Geändert';

}*/



function adi_page_is_in_archive( $id ) {

	$page = get_post( $id ); 

	$parent_id = $page->post_parent;

	if ( ADI_ACTIVITY_ARCHIVE_PAGE_ID === $parent_id ) {
		return true;
	}

	return false;

}



