<?php

namespace adi;

const NEWS_CAT_ID = 25;
const EVENTS_CAT_ID = 26;
const EVENTS_ARCHIVE_CAT_ID = 37;
const INDEPENDENT_EVENTS_CAT_ID = 33;



add_filter( 'hidden_meta_boxes', 'adi\hide_meta_boxes', 10, 2 );

function hide_meta_boxes( $hidden, $screen ) {

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



add_filter( 'the_generator', 'adi\remove_wp_version' );

function remove_wp_version() {

	return '';

}



add_filter( 'pre_get_posts', 'adi\filter_rss_query' );

function filter_rss_query( $query ) {

	if ( $query->is_feed ) {
		$query->set( 'cat', NEWS_CAT_ID );
	}

	return $query;

}
