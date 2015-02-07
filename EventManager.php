<?php

namespace adi;

require_once( 'Event.php' );

class EventManager implements \Iterator {

	private $pos = 0;
	private $events = array();

	public function __construct( $cat_id = EVENTS_CAT_ID, $limit = false ) {
		$args = array(
			'posts_per_page'   => -1,
			'category'         => $cat_id, 
			'orderby'          => 'none',
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'suppress_filters' => true );

		$posts = get_posts( $args );

		$today = new \DateTime();
		$today->setTime( 0, 0 );

		$limitingDay = null;
		if ( false !== $limit ) {
			$limitingDay = clone $today;
			$limitingDay->modify( $limit . ' days' );
		}

		$entriesWithSameTimestamp = 0;

		foreach ( $posts as $post ) {
			$e = new Event( $post->ID );
			if ( $e->isEmpty() ) {
				error_log( 'A non-event post in the events category: ' . $post->ID );
				continue;
			}
			if ( $e->getDateTime() < $today ) continue;

			if ( false !== $limit && $e->getDateTime() > $limitingDay ) {
				continue;
			}

			$key = $e->getTimestamp();
			if ( array_key_exists( $key, $this->events ) ) {
				$entriesWithSameTimestamp += 1;
				$key += $entriesWithSameTimestamp;
			}
			$this->events[$key] = $e;
		}

		ksort( $this->events, SORT_NUMERIC );
		$this->events = array_values( $this->events );
	}

	function rewind() {
		$this->pos = 0;
	}

	function current() {
		return $this->events[$this->pos];
	}

	function key() {
		return $this->pos;
	}

	function next() {
		++$this->pos;
	}

	function valid() {
		return isset( $this->events[$this->pos] );
	}

	function isEmpty() {
		return 0 === count( $this->events );
	}
}

?>
