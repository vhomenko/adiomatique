<?php

defined( 'ABSPATH' ) or die( '' );



/* TODO: Either refactor into OOD, or at least define adi_get_event singular -> SOC */

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

		// FIXME: redundant, apart for being a key 
		$event_ts = intval( get_post_meta( $id, 'adi_event_timestamp', true ) );

		if ( $new_date ) {
			$datetime = $new_date;
		} else {
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
		$week_to_skip = intval( get_post_meta( $id, 'adi_event_week_to_skip', true ) );
		$periodicity_formatted = adi_get_event_periodicity( $datetime, $periodicity, $week_to_skip );

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
					'week_to_skip' => $week_to_skip,
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
	$week_to_skip = intval( get_post_meta( $id, 'adi_event_week_to_skip', true ) );

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
			$nd = adi_next_date( $event, $today, $periodicity, $week_to_skip );

			$new_ts = $nd->getTimestamp();

			update_post_meta( $id, 'adi_event_timestamp', $new_ts );

			return $nd;
		}
	}

}



function adi_next_date( $event_date, $today, $periodicity, $iteration_to_exclude_index = 0 ) {

	$today->setTime( 0, 0 );

	if ( $event_date >= $today ) return false;

	$next_date = clone $today;

	$event_weekday = $event_date->format( 'l' );

	if ( 0 === $periodicity ) {
		return false;
	} else if ( 1 === $periodicity ) {
		// weekly
		// ! strtotime/modify: if todays is Fr and you want this Tu, you'll get next Tuesday

		$next_date->modify( $event_weekday );

		if ( 0 !== $iteration_to_exclude_index ) {

			$iteration_to_exclude = null;

			switch ( $iteration_to_exclude_index ) {
				case 1:
					$iteration_to_exclude = 'first';
					break;
				case 2:
					$iteration_to_exclude = 'second';
					break;
				case 3:
					$iteration_to_exclude = 'third';
					break;
				case 4:
					$iteration_to_exclude = 'fourth';
					break;
				default:
					return false;
			}

			$date_to_exclude = clone $next_date;

			$date_to_exclude->modify( $iteration_to_exclude . ' ' . $event_weekday . ' of this month' );

			if ( $date_to_exclude < $next_date ) {
				$date_to_exclude->modify( $iteration_to_exclude . ' ' . $event_weekday . ' of next month' );
			}

			//error_log( $next_date->format( 'D d-m-Y G:i' ) . '; to exclude: ' . $date_to_exclude->format( 'D d-m-Y G:i' ) );

			if ( $next_date == $date_to_exclude ) {
				$next_date->modify( '+1 week' );
			}

		}

	} else if ( 2 === $periodicity ) {
		// biweekly
		$adi_event_weeknum = intval( $event_date->format( 'W' ) );
		$is_event_weeknum_even = ( 0 === $adi_event_weeknum % 2 );
		$current_weeknum = intval( $today->format( 'W' ) );
		$is_current_weeknum_even = ( 0 === $current_weeknum % 2 );
		$both_are_on_even_weeks = $is_current_weeknum_even === $is_event_weeknum_even;

		$event_weekday_index = intval( $event_date->format( 'N' ) );
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

		$day_of_month = $event_date->format( 'j' );

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

	$hour = intval( $event_date->format( 'H' ) );
	$min = intval( $event_date->format( 'i' ) );
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


function adi_get_event_periodicity( $dt, $periodicity, $week_to_skip ) {

	$weekday = adi_get_weekday_de( $dt->format( 'l' ) );

	if ( 0 === $periodicity ) {

		return;

	} else if ( 1 === $periodicity ) {

		$indices = ' jeden ';

		$suffix = '';

		if ( 0 !== $week_to_skip ) {

			if ( 1 !== $week_to_skip ) {
				$indices .= '1. ';
			}
			if ( 2 !== $week_to_skip ) {
				$indices .= '2. ';
			}
			if ( 3 !== $week_to_skip ) {
				$indices .= '3. ';
			}
			if ( 4 !== $week_to_skip ) {
				$indices .= '4. ';
			}

			$indices = substr_replace( $indices, ' und', 12, 0 );
			$suffix = ' des Monats';
		}

		return $indices . $weekday . $suffix;

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



function adi_page_is_in_archive( $id ) {

	$page = get_post( $id ); 

	$parent_id = $page->post_parent;

	if ( ADI_ACTIVITY_ARCHIVE_PAGE_ID === $parent_id ) {
		return true;
	}

	return false;

}



