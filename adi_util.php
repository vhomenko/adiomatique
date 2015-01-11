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

	$today_ts = mktime( 0, 0, 0, date( 'm' ), date( 'j' ), date( 'y' ) );
	
	if ( false !== $limit ) {
		$upper_limit_ts = $today_ts + 60 * 60 * 24 * $limit;
	}
	
	foreach ( $event_posts as $post ) {
		
		$id = $post->ID;
	
		adi_update_event( $id );
	
		$event_ts = intval( get_post_meta( $id, 'adi_event_timestamp', true ) );
	
		// sort out passed non-periodic events
		if ( $event_ts < $today_ts ) continue;

		$event_special = intval( get_post_meta( $id, 'adi_event_special', true ) );

		// sort out dates too far in the future
		if ( false !== $limit && $event_ts > $upper_limit_ts ) {
			// the important dates are kept
			if ( 100 !== $event_special ) continue;
		}
		
		// the inactive events are filtered
		if ( 1 === $event_special ) continue;

		$datetime = new DateTime( '@' . $event_ts );
		
		$event = array(
					'id' => $id,
					'timestamp' => $event_ts,
					'time' => $datetime->format( 'H:i' ),
					'date' => $datetime->format( 'd.m' ),
					'weekday' => $datetime->format( 'l' ),
					'weeknum' => intval( $datetime->format( 'W' ) ),
					'special' => $event_special,
					'periodicity' => intval( get_post_meta( $id, 'adi_event_periodicity', true ) ),
					'titlepage_id' => intval( get_post_meta( $id, 'adi_event_titlepage_id', true ) ),
					'title' => $post->post_title,
					'link' => '<a href="' . wp_get_shortlink( $id ) . '">' . $post->post_title . '</a>'
				);

		$events[$event_ts] = $event;

	}

	ksort ( $events, SORT_NUMERIC );

	return $events;

}



function adi_update_event( $id ) {

	$event_ts = intval( get_post_meta( $id, 'adi_event_timestamp', true ) );	
	$periodicity = intval( get_post_meta( $id, 'adi_event_periodicity', true ) );
	
	$today_ts = mktime( 0, 0, 0, date( 'm' ), date( 'j' ), date( 'y' ) );

	if ( $event_ts < $today_ts ) {
		if ( 0 == $periodicity ) {
		
			// archivate old non periodic events
			wp_set_post_terms( $id, array( ADI_EVENTS_ARCHIVE_CAT_ID ), 'category' );

		} else {
			$new_ts = adi_next_date( $event_ts, $today_ts, $periodicity );
			
			update_post_meta( $id, 'adi_event_timestamp', $new_ts );
			
			return $new_ts;
		}
	}
	
	return 0;
	
}



function adi_next_date( $event_ts, $today_ts, $periodicity ) {

	$datetime = new DateTime( '@' . $event_ts );

	$adi_event_weeknum = intval( $datetime->format( 'W' ) );
	$adi_event_weekday = $datetime->format( 'l' );

	$day_of_month = $datetime->format( 'j' );

	$week_index = adi_get_week_index( $day_of_month );
	$week_index_word = adi_get_week_index( $day_of_month, true );

	$event_weekday_index = intval( $datetime->format( 'N' ) );
	$current_weekday_index = intval( date( 'N' ) );

	$d = 0;

// ! strtotime: if todays is Fr and you want this Tu, you'll get next Tuesday
	
	if ( $event_ts < $today_ts ) {

		if ( 1 == $periodicity ) {
			// weekly

			$d = strtotime( $adi_event_weekday );
		
		} else if ( 2 == $periodicity ) {
			// biweekly

			$is_event_weeknum_even = ( 0 == $adi_event_weeknum % 2 );
			
			$current_weeknum = date( 'W' );
			$is_current_weeknum_even = ( 0 == $current_weeknum % 2 );

			if ( $event_weekday_index < $current_weekday_index ) {
				if ( $is_current_weeknum_even == $is_event_weeknum_even ) {
					$d = strtotime( '+1 week ' . $adi_event_weekday );
				} else {
					$d = strtotime( $adi_event_weekday );
				}
			} else if ( $event_weekday_index == $current_weekday_index ) {
				if ( $is_current_weeknum_even == $is_event_weeknum_even ) {
					$d = strtotime( $adi_event_weekday );
				} else {
					$d = strtotime( '+1 week ' . $adi_event_weekday );
				}
			} else if ( $event_weekday_index > $current_weekday_index ) {
				if ( $is_current_weeknum_even == $is_event_weeknum_even ) {
					$d = strtotime( $adi_event_weekday );
				} else {
					$d = strtotime( '+1 week ' . $adi_event_weekday );
				}
			}

		} else if ( 4 == $periodicity ) {
			// monthly

			if ( $week_index === false ) {
				echo '<p style="color:#900">Im Februar wird dieser monatlicher Termin wohl weg fallen, was?</p>';
			}
			
			$d = strtotime( $week_index_word . ' ' . $adi_event_weekday . ' of this month' );

			if ( $d < $today_ts ) {
				$d = strtotime( $week_index_word . ' ' . $adi_event_weekday . ' of next month' );
			}

		}

		$hour_in_sec = 60 * 60 * intval( $datetime->format( 'G' ) );
		$min_in_sec = 60 * intval( $datetime->format( 'i' ) );
		
		$t = $hour_in_sec + $min_in_sec;

		return $d + $t;
	}

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


function adi_get_event_periodicity( $ts, $periodicity ) {

	$datetime = new DateTime( '@' . $ts );
		
	$weekday = adi_get_weekday_de( $datetime->format( 'l' ) );

	if ( 0 === $periodicity ) {
	
		return;
	
	} else if ( 1 === $periodicity ) {
	
		return ' jeden ' . $weekday;
	
	} else if ( 2 === $periodicity ) {
	
		$weeknum = intval( $datetime->format( 'W' ) );
	
		$p = ' jede ';
	
		if ( 0 != $weeknum % 2 ) {
			$p .= 'un';
		}
		
		return $p . 'gerade Woche am ' . $weekday;
	
	} else if ( 4 === $periodicity ) {

		$day_of_month = $datetime->format( 'j' );

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



function adi_get_event_special( $special ) {

	if ( 1 === $special ) return 'Inaktiv';
	else if ( 25 === $special ) return 'Extern';
	else if ( 100 === $special ) return 'Wichtig!';
	else if ( 75 === $special ) return 'Geändert';

}



function adi_page_is_in_archive( $id ) {

	$page = get_post( $id ); 

	$parent_id = $page->post_parent;

	if ( ADI_ACTIVITY_ARCHIVE_PAGE_ID === $parent_id ) {
		return true;
	}
	
	return false;	
}



add_filter( 'hidden_meta_boxes', 'adi_hide_meta_boxes', 10, 2 );

function adi_hide_meta_boxes( $hidden, $screen ) {
	
	//if ( $screen->in_admin() ) return $hidden;
	
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

