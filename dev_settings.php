<?php


function wp_autosave_dequeue_script() {
   wp_dequeue_script( 'autosave' );
}
add_action( 'admin_print_scripts', 'wp_autosave_dequeue_script', 100 );

const ADI_NEWS_CAT_ID = 2;
const ADI_EVENTS_CAT_ID = 5;
const ADI_EVENTS_ARCHIVE_CAT_ID = 6;
const ADI_INDEPENDENT_EVENTS_CAT_ID = 9;

