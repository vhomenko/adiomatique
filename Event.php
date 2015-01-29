<?php

require_once( 'Storage.php' );
require_once( 'EventDate.php' );

class Event {

	private $ID;
	private $date;
	private $storage;
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
		'titlepage_id' => 'adi_event_titlepage_id' );

	public function __construct( $ID ) {
		$this->ID = $ID;
		$this->storage = new CustomValuesStorage( $this->ID );

		$this->timestamp = $this->storage->getInt( $this->KEYS['timestamp'] );
		if ( 0 === $this->timestamp ) {
			return;
		}
		$this->dtObj = new DateTime( '@' . $this->timestamp );
		$this->dtObj->setTimezone( new DateTimeZone( ADI_TZ ) );

		$this->set(
			$this->dtObj,
			$this->storage->getInt( $this->KEYS['periodicity'] ),
			$this->storage->getInt( $this->KEYS['week_to_skip'] ),
			$this->storage->getStr( $this->KEYS['location'] ),
			$this->storage->getInt( $this->KEYS['titlepage_id'] )
		);
	}

	public function set( $dateTime, $periodicity, $weekToSkip, $location, $titlepageID ) {
		$this->location = $location;
		$this->periodicity = $periodicity;
		$this->weekToSkip = $weekToSkip;
		$this->date = new EventDate( $dateTime, null, $periodicity, $weekToSkip );
		$this->titlepageID = $titlepageID;
		$titlepage = get_post( $this->titlepageID );
		$this->titlepageTitle = $titlepage->post_title;
		$post = get_post( $this->ID );
		$this->title = $post->post_title;
		
		$this->isEmpty = false;
		$this->update();
	}

	public function setFromPost( $time, $date, $periodicity, $weekToSkip, $location, $titlepageID ) {

		$dateTime = new DateTime();
		$this->set(
			$dateTime,
			$periodicity,
			$weekToSkip,
			$location,
			$titlepageID
		);
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

	public function getWeekDay() {
		return $this->date->weekDayDE;
	}

	public function getTimestamp() {
		return $this->date->timestamp;
	}

	public function getDateTime() {
		return $this->date->dtObj;
	}


	public function getWeekToSkip() {
		if ( $this->isEmpty ) return 0;
		return $this->date->weekToSkip;
	}

/*	public function getWeekNumber() {
		return $this->date->weekNum;
	}

	public function getWeekDayIndex() {
		return $this->date->weekDayIndex;
	}
/*
	public function compareWeekNumbers( $e ) {
		return $this->getWeekNumber() === $e->getWeekNumber();
	}

	public function compareWeekDays( $e ) {
		if ( empty( $e ) ) return false;
		return $this->getWeekDayIndex() === $e->getWeekDayIndex();
	}*/

	public function isOnTheSameDay( $e ) {
		if ( empty( $e ) ) return false;
		return $this->getDate() == $e->getDate();
	}

	private function update() {
		if ( $this->date->isNonPeriodicAndPassed() ) {
			$this->archivate();
		} else if ( $this->date->isUpdated ) {
			$this->store();
		}
	}

	private function store() {
		$this->storage->update( $this->KEYS['timestamp'], $this->date->timestamp );
	}

	private function archivate() {
		wp_set_post_terms( $this->ID, array( ADI_EVENTS_ARCHIVE_CAT_ID ), 'category' );
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

	public function getPeriodicityDesc() {
		return $this->date->getPeriodicityDesc();
	}

	public function getTitlepageID() {
		if ( $this->isEmpty ) return 0;
		return $this->titlepageID;
	}

	public function isArchived() {
		return in_category( ADI_EVENTS_ARCHIVE_CAT_ID, $this->ID );
	}

	public function isEmpty() {
		return $this->isEmpty;
	}
}

?>
