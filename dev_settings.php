<?php

namespace adi;

function dequeue_autosave_script() {
   wp_dequeue_script( 'autosave' );
}
add_action( 'admin_print_scripts', 'adi\dequeue_autosave_script', 100 );

const NEWS_CAT_ID = 2;
const EVENTS_CAT_ID = 5;
const EVENTS_ARCHIVE_CAT_ID = 6;
const INDEPENDENT_EVENTS_CAT_ID = 9;

