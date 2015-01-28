<?php

const ADI_NEWS_CAT_ID = 25;
const ADI_EVENTS_CAT_ID = 26;
const ADI_EVENTS_ARCHIVE_CAT_ID = 37;
const ADI_INDEPENDENT_EVENTS_CAT_ID = 33;



add_filter( 'hidden_meta_boxes', 'adi_hide_meta_boxes', 10, 2 );

function adi_hide_meta_boxes( $hidden, $screen ) {

	global $post;

	if ( empty( $post ) ) return $hidden;

	$adi_is_titlepage = get_post_meta( $post->ID, 'adi_is_titlepage', true );
	$adi_event_timestamp = get_post_meta( $post->ID, 'adi_event_timestamp', true );

	if ( 'post' == $screen->post_type && ! empty( $adi_event_timestamp ) ) {
		$hidden = array( 'postexcerpt', 'slugdiv', 'trackbacksdiv', 'commentstatusdiv', 'commentsdiv', 'authordiv', 'revisionsdiv', 'categorydiv', 'postcustom' );
	} else if ( 'page' == $screen->post_type && ! empty( $adi_is_titlepage ) ) {
		$hidden = array( 'postexcerpt', 'slugdiv', 'trackbacksdiv', 'commentstatusdiv', 'commentsdiv', 'authordiv', 'revisionsdiv', 'pageparentdiv', 'postcustom' );
	}

	return $hidden;

}



add_filter( 'the_generator', 'wpgenny_remove_version' );

function wpgenny_remove_version() {

	return '';

}



add_filter( 'pre_get_posts', 'filter_rss_query' );

function filter_rss_query( $query ) {

	if ( $query->is_feed ) {
		$query->set( 'cat', ADI_NEWS_CAT_ID );
	}

	return $query;

}
