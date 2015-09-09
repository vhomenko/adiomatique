<?php

namespace adi;

require_once( 'Storage.php' );
require_once( 'EventDate.php' );

class Event {

	private $storage;
	private $date;

	private $ID;
	private $location;
	private $titlepageID;
	private $titlepageTitle;
	private $title;
	private $isEmpty = true;

	private $KEYS = array(
		'timestamp' => 'adi_event_timestamp',
		'location' => 'adi_event_location',
		'periodicity' => 'adi_event_periodicity',
		'week_to_skip' => 'adi_event_week_to_skip',
		'extra_week_to_skip' => 'adi_event_extra_week_to_skip',
		'titlepage_id' => 'adi_event_titlepage_id' );

	const DATE_TIME_FORMAT = 'd.m.y G:i';

	private $WEEKDAYS_DICT = array(
		'Monday' => 'Montag',
		'Tuesday' => 'Dienstag',
		'Wednesday' => 'Mittwoch',
		'Thursday' => 'Donnerstag',
		'Friday' => 'Freitag',
		'Saturday' => 'Samstag',
		'Sunday' => 'Sonntag' );

	public function __construct( $ID, $doNotLoad = false ) {
		$this->ID = $ID;
		$this->storage = new CustomValuesStorage( $this->ID );

		if ( $doNotLoad ) return;

		$this->timestamp = $this->storage->getInt( $this->KEYS['timestamp'] );
		if ( 0 === $this->timestamp ) return;
		$dt = new \DateTime( '@' . $this->timestamp );
		$dt->setTimezone( new \DateTimeZone( TZ ) );

		$this->set(
			$dt,
			$this->storage->getInt( $this->KEYS['periodicity'] ),
			$this->storage->getInt( $this->KEYS['week_to_skip'] ),
			$this->storage->getInt( $this->KEYS['extra_week_to_skip'] ),
			$this->storage->getStr( $this->KEYS['location'] ),
			$this->storage->getInt( $this->KEYS['titlepage_id'] )
		);
	}

	public function set( $dateTime, $periodicity, $weekToSkip, $extraWeekToSkip, $location, $titlepageID ) {
		$this->date = new EventDate( $dateTime, null, $periodicity, $weekToSkip, $extraWeekToSkip );
		$this->location = $location;
		$this->titlepageID = $titlepageID;
		$titlepage = get_post( $this->titlepageID );
		$this->titlepageTitle = $titlepage->post_title;
		$post = get_post( $this->ID );
		$this->title = $post->post_title;

		$this->isEmpty = false;
		$this->update();
	}

	public function setFromPost( $time, $date, $periodicity, $weekToSkip, $extraWeekToSkip, $location, $titlepageID ) {

		if ( empty( $time ) ) {
			$this->deleteStorage();
			return;
		}

		$dateTime = \DateTime::createFromFormat( self::DATE_TIME_FORMAT, $this->normalizeDate( $date ) . ' ' . $this->normalizeTime( $time ), new \DateTimeZone( TZ ) );

		if ( ! $dateTime ) {
			return;
			error_log( 'Bad date/time: ' . $date . '/' . $time );
		}

		$catID = INDEPENDENT_EVENTS_CAT_ID;
		if ( 0 !== $titlepageID ) {
			$catID = $this->storage->getIntFromAnotherPost( $titlepageID, 'adi_titlepage_cat_id' );
		}
		$this->setCategory( $catID );

		$this->set(
			$dateTime,
			$periodicity,
			$weekToSkip,
			$extraWeekToSkip,
			$location,
			$titlepageID
		);

		$this->storeAll( $dateTime->getTimestamp(), $periodicity, $weekToSkip, $extraWeekToSkip, $location, $titlepageID );
	}

	private function update() {
		if ( ! $this->isPeriodic() && $this->date->isPassed() ) {
			$this->archivate();
		} else if ( $this->date->isUpdated ) {
			$this->storeDate();
		}
	}

/** UTILS */

	private function normalizeTime( $time ) {
		$arr = explode( ':', $time );
		$h = $arr[0];
		$m = $arr[1];

		if ( 1 === strlen( $h ) )
			$h = '0' . $h;
		if ( 1 === strlen( $m ) )
			$m = '0' . $m;

		return $h . ':' . $m;
	}

	private function normalizeDate( $date ) {
		$arr = explode( '.', $date );
		$d = $arr[0];
		$m = $arr[1];
		$y = $arr[2];

		if ( 1 === strlen( $d ) )
			$d = '0' . $d;
		if ( 1 === strlen( $m ) )
			$m = '0' . $m;
		if ( 4 === strlen( $y ) )
			$y = substr( $y, 2 );

		return $d . '.' . $m . '.' . $y;
	}

	public function getTitlepageLink() {
		$link = '';
		if ( ! empty( $this->titlepageID ) ) {
			$link = '<a href="' . get_page_link( $this->titlepageID ) . '">' . $this->titlepageTitle . '</a>';
		}
		return $link;
	}

	public function getLink() {
		return '<a href="' . wp_get_shortlink( $this->ID ) . '">' . $this->title . '</a>';
	}

	public function getPeriodicityDesc() {
		switch( $this->date->periodicity ) {
			case 0:
				return;
			case 1:
				$indices = ' jeden ';

				$w2s = $this->date->weekdayBlacklist->firstWeekdayToSkip;
				$extraW2s = $this->date->weekdayBlacklist->extraWeekdayToSkip;

				if ( 0 === $w2s && 0 === $extraW2s ) return $indices . $this->getWeekday();

				if ( 1 !== $w2s ) {
					$indices .= '1. ';
				}
				if ( 2 !== $w2s && 2 !== $extraW2s ) {
					$indices .= '2. ';
				}
				if ( 3 !== $w2s && 3 !== $extraW2s ) {
					$indices .= '3. ';
				}
				if ( 4 !== $w2s && 4 !== $extraW2s ) {
					$indices .= '4. ';
				}
				
				if ( ! $extraW2s ) {
					$indices = substr_replace( $indices, ' und', 12, 0 );
				} else {
					$indices = substr_replace( $indices, ' und', 9, 0 );
				}
				return $indices . $this->getWeekday() . ' des Monats';
			case 2:
				$p = ' jede ';

				if ( $this->date->isOnOddWeek() ) {
					$p .= 'un';
				}

				return $p . 'gerade Woche am ' . $this->getWeekday();
			case 4:
				return ' jeden ' . $this->date->weekdayBlacklist->getWeekdayIndex( $this->date->dt ) . '. ' . $this->getWeekday() . ' des Monats';
		}
	}

/** WRAPPERS */

	private function archivate() {
		$this->setCategory( EVENTS_ARCHIVE_CAT_ID );
	}

	public function setCategory( $catID ) {
		wp_set_post_terms( $this->ID, array( $catID ), 'category' );
	}

	public function isOnTheSameDay( $e ) {
		if ( empty( $e ) ) return false;
		return $this->getDate() == $e->getDate();
	}

	public function getDate() {
		return $this->date->date;
	}

	public function getFullDate() {
		return $this->date->fullDate;
	}

	public function getTime() {
		return $this->date->time;
	}

	public function getWeekday() {
		return $this->WEEKDAYS_DICT[$this->date->weekday];
	}

	public function getTimestamp() {
		return $this->date->timestamp;
	}

	public function getDateTime() {
		return $this->date->dt;
	}

	public function getWeekToSkip() {
		if ( $this->isEmpty ) return 0;
		return $this->date->weekdayBlacklist->firstWeekdayToSkip;
	}

	public function getExtraWeekToSkip() {
		if ( $this->isEmpty ) return 0;
		return $this->date->weekdayBlacklist->extraWeekdayToSkip;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getLocation() {
		if ( $this->isEmpty ) return '';
		return $this->location;
	}

	public function isExternal() {
		return ! empty( $this->location );
	}

	public function getPeriodicity() {
		if ( $this->isEmpty ) return 0;
		return $this->date->periodicity;
	}

	public function isPeriodic() {
		return 0 < $this->date->periodicity;
	}

	public function getTitlepageID() {
		if ( $this->isEmpty ) return 0;
		return $this->titlepageID;
	}

	public function isArchived() {
		return in_category( EVENTS_ARCHIVE_CAT_ID, $this->ID );
	}

	public function isEmpty() {
		return $this->isEmpty;
	}


/** STORAGE */

	private function storeDate() {
		$this->storage->update( $this->KEYS['timestamp'], $this->getTimestamp() );
		$this->storage->update( $this->KEYS['periodicity'], $this->getPeriodicity() );
	}

	private function storeAll( $timestamp, $periodicity, $weekToSkip, $extraWeekToSkip, $location, $titlepageID ) {
		$this->storage->update( $this->KEYS['timestamp'], $timestamp );
		$this->storage->update( $this->KEYS['periodicity'], $periodicity );
		$this->storage->update( $this->KEYS['week_to_skip'], $weekToSkip );
		$this->storage->update( $this->KEYS['extra_week_to_skip'], $extraWeekToSkip );
		$this->storage->update( $this->KEYS['location'], $location );
		$this->storage->update( $this->KEYS['titlepage_id'], $titlepageID );
	}

	private function deleteStorage() {
		$this->storage->delete( $this->KEYS['timestamp'] );
		$this->storage->delete( $this->KEYS['periodicity'] );
		$this->storage->delete( $this->KEYS['week_to_skip'] );
		$this->storage->delete( $this->KEYS['extra_week_to_skip'] );
		$this->storage->delete( $this->KEYS['location'] );
		$this->storage->delete( $this->KEYS['titlepage_id'] );
	}

}

?>
