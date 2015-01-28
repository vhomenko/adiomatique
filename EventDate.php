<?php

class EventDate {
	var $timestamp;
	var $dtObj;
	var $date;
	var $fullDate;
	var $time;
	var $weekDay;
	var $weekDayIndex;
	var $weekDayDE;
	var $weekNum;
	var $dayOfMonth;
	var $periodicity;
	var $weekToSkip;
	var $today;
	var $isUpdated = false;

	private $WEEKDAYS_DICT = array(
		'Monday' => 'Montag',
		'Tuesday' => 'Dienstag',
		'Wednesday' => 'Mittwoch',
		'Thursday' => 'Donnerstag',
		'Friday' => 'Freitag',
		'Saturday' => 'Samstag',
		'Sunday' => 'Sonntag' );

	public function __construct ( $timestampOrDateTime, $today, $periodicity, $weekToSkip = 0 ) {
		if ( is_numeric( $timestampOrDateTime ) ) {
			$this->timestamp = $timestampOrDateTime;
			$this->dtObj = new DateTime( '@' . $this->timestamp );
		} else if ( is_object( $timestampOrDateTime ) ) {
			$this->dtObj = $timestampOrDateTime;
			$this->timestamp = $this->dtObj->getTimestamp();
		} else {
			error_log( 'EventDate: first arg is neither a timestamp nor a DateTime object.' );
			die();
		}
		
		$this->dtObj->setTimezone( new DateTimeZone( ADI_TZ ) );

		$this->periodicity = $periodicity;

		$isValidWeekToSkipIndex = ( is_numeric( $weekToSkip ) && -1 < $weekToSkip && 5 > $weekToSkip );
		if ( ! $isValidWeekToSkipIndex ) {
			throw new Exception( 'EventDate: Invalid WeekToSkip index: ' . print_r( $weekToSkip, true ) );
		}
		$this->weekToSkip = $weekToSkip;

		if ( ! $today ) $today = new DateTime();
		$this->today = $today;
		$this->today->setTime( 0, 0 );

		$this->updateFields();

		$this->next();
	}

	private function updateFields() {
		$this->time = $this->dtObj->format( 'H:i' );
		$this->date = $this->dtObj->format( 'd.m' );
		$this->fullDate = $this->dtObj->format( 'j.m.y' );
		$this->weekNum = intval( $this->dtObj->format( 'W' ) );
		$this->weekDay = $this->dtObj->format( 'l' );
		$this->weekDayDE = $this->WEEKDAYS_DICT[$this->weekDay];
		$this->dayOfMonth = intval( $this->dtObj->format( 'j' ) );
		$this->weekDayIndex = intval( $this->dtObj->format( 'N' ) );
	}

	public function format() {
		return $this->dtObj->format( 'D d-m-Y G:i' );
	}

	public function isNonPeriodicAndPassed() {
		return 0 === $this->periodicity && $this->dtObj < $this->today;
	}

	public function next() {
		if ( $this->dtObj >= $this->today ) {
			if ( 0 === $this->weekToSkip ) return false;
			if ( 1 !== $this->periodicity ) return false;
		}

		$next_date = clone $this->today;

		switch( $this->periodicity ) {
			case 1:
				// weekly
				// ! strtotime/modify: if todays is Fr and you want this Tu, you'll get next Tuesday
				$next_date->modify( $this->weekDay );
				if ( 0 === $this->weekToSkip ) break;
				$iteration_to_exclude = $this->getWeekIndexAsWord( $this->weekToSkip );
				$date_to_exclude = clone $next_date;
				$date_to_exclude->modify( $iteration_to_exclude . ' ' . $this->weekDay . ' of this month' );
				if ( $date_to_exclude < $next_date ) {
					$date_to_exclude->modify( $iteration_to_exclude . ' ' . $this->weekDay . ' of next month' );
				}
				if ( $next_date == $date_to_exclude ) {
					$next_date->modify( '+1 week' );
				}
				break;
			case 2:
				// biweekly
				$is_event_weeknum_even = ( 0 === $this->weeknum % 2 );
				$current_weeknum = intval( $this->today->format( 'W' ) );
				$is_current_weeknum_even = ( 0 === $current_weeknum % 2 );
				$both_are_on_even_weeks = $is_current_weeknum_even === $is_event_weeknum_even;

				$current_weekday_index = intval( $this->today->format( 'N' ) );
				$event_day_passed_in_current_week = $this->weekDayIndex < $current_weekday_index;

				$next_date->modify( 'this ' . $this->weekDay );

				if ( $event_day_passed_in_current_week ) {
					if ( $both_are_on_even_weeks ) {
						$next_date->modify( '+1 week' );
					}
				} else {
					if ( ! $both_are_on_even_weeks ) {
						$next_date->modify( '+1 week' );
					}
				}
				break;
			case 4:
				// monthly
				$week_index = $this->getWeekIndex();
				if ( empty( $week_index ) ) {
					return false;
				}
				$week_index_word = $this->getWeekIndexAsWord( $week_index );
				$next_date->modify( $week_index_word . ' ' . $this->weekDay . ' of this month' );
				if ( $next_date < $this->today ) {
					$next_date->modify( $week_index_word . ' ' . $this->weekDay . ' of next month' );
				}
				break;
			case 0:
			default: 
				return false;
		}
		$hour = intval( $this->dtObj->format( 'H' ) );
		$min = intval( $this->dtObj->format( 'i' ) );
		$next_date->setTime( $hour, $min );

		$this->dtObj = $next_date;
		$this->timestamp = $this->dtObj->getTimestamp();
		$this->updateFields();
		$this->isUpdated = true;
	}

	function getWeekIndex() {
		$dom = $this->dayOfMonth;
		if ( 8 > $dom ) {
			return 1;
		} else if ( 15 > $dom ) { 
			return 2;
		} else if ( 22 > $dom ) { 
			return 3;
		} else if ( 29 > $dom ) { 
			return 4;
		}
	}

	function getWeekIndexAsWord( $index ) {
		switch( $index ) {
			case 1:
				return 'first';
			case 2:
				return 'second';
			case 3:
				return 'third';
			case 4:
				return 'fourth';
		}
	}

	public function getPeriodicityDesc() {
		switch( $this->periodicity ) {
			case 0:
				return;
			case 1:
				$indices = ' jeden ';

				$w2s = $this->weekToSkip;

				if ( 0 === $w2s ) return $indices . $this->weekDayDE;

				if ( 1 !== $w2s ) {
					$indices .= '1. ';
				}
				if ( 2 !== $w2s ) {
					$indices .= '2. ';
				}
				if ( 3 !== $w2s ) {
					$indices .= '3. ';
				}
				if ( 4 !== $w2s ) {
					$indices .= '4. ';
				}
				$indices = substr_replace( $indices, ' und', 12, 0 );
				return $indices . $this->weekDayDE . ' des Monats';
			case 2:
				$p = ' jede ';

				if ( 0 !== $this->weekNum % 2 ) {
					$p .= 'un';
				}

				return $p . 'gerade Woche am ' . $this->weekDayDE;
			case 4:
				return ' jeden ' . $this->getWeekIndex() . '. ' . $this->weekDayDE . ' des Monats';
		}
	}
}

?>
