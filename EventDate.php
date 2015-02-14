<?php

namespace adi;

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

	public function __construct ( $dateTime, $today, $periodicity, $weekToSkip = 0 ) {
		$this->dtObj = $dateTime;
		$this->timestamp = $this->dtObj->getTimestamp();
		$this->periodicity = $periodicity;

		$isValidWeekToSkipIndex = ( is_numeric( $weekToSkip ) && -1 < $weekToSkip && 5 > $weekToSkip );
		if ( ! $isValidWeekToSkipIndex ) {
			throw new Exception( 'EventDate: Invalid WeekToSkip index: ' . print_r( $weekToSkip, true ) );
		}
		$this->weekToSkip = $weekToSkip;

		if ( ! $today ) $today = new \DateTime();
		$this->today = $today;
		$this->today->setTime( 0, 0 );

		$this->updateFields();
		$this->next();
	}

	private function updateFields() {
		$this->time = $this->dtObj->format( 'H:i' );
		$this->date = $this->dtObj->format( 'd.m' );
		$this->fullDate = $this->dtObj->format( 'd.m.y' );
		$this->weekNum = intval( $this->dtObj->format( 'W' ) );
		$this->weekDay = $this->dtObj->format( 'l' );
		$this->weekDayDE = $this->WEEKDAYS_DICT[$this->weekDay];
		$this->dayOfMonth = intval( $this->dtObj->format( 'j' ) );
		$this->weekDayIndex = intval( $this->dtObj->format( 'N' ) );
	}

	public function format() {
		return $this->dtObj->format( 'D d-m-Y G:i' );
	}

	public function isPeriodic() {
		return 0 < $this->periodicity;
	}

	public function isPassed() {
		return $this->dtObj < $this->today;
	}

	public function next() {
		$isInFuture = false;
		if ( $this->dtObj >= $this->today ) {
			if ( $this->isMonthly() ||
				( $this->isWeekly() && $this->weekToSkip ) ) {
					$isInFuture = true;
				} else return false;
		}
		$nextDate = clone $this->today;
		if ( $isInFuture ) $nextDate = clone $this->dtObj;

		switch( $this->periodicity ) {
			case 1:
				// weekly
				// ! strtotime/modify: if todays is Fr and you want this Tu, you'll get next Tuesday
				$nextDate->modify( $this->weekDay );
				if ( 0 === $this->weekToSkip ) break;
				$weekIndexToSkip = $this->getWeekIndexAsWord( $this->weekToSkip );
				$dateToSkip = clone $nextDate;
				$dateToSkip->modify( $weekIndexToSkip . ' ' . $this->weekDay . ' of this month' );
				if ( $dateToSkip < $nextDate ) {
					$dateToSkip->modify( $weekIndexToSkip . ' ' . $this->weekDay . ' of next month' );
				}
				if ( $nextDate == $dateToSkip ) {
					$nextDate->modify( '+1 week' );
				}
				break;
			case 2:
				// biweekly
				$is_event_weeknum_even = ( 0 === $this->weekNum % 2 );
				$current_weeknum = intval( $this->today->format( 'W' ) );
				$is_current_weeknum_even = ( 0 === $current_weeknum % 2 );
				$both_are_on_even_weeks = $is_current_weeknum_even === $is_event_weeknum_even;

				$current_weekday_index = intval( $this->today->format( 'N' ) );
				$event_day_passed_in_current_week = $this->weekDayIndex < $current_weekday_index;

				$nextDate->modify( 'this ' . $this->weekDay );

				if ( $event_day_passed_in_current_week ) {
					if ( $both_are_on_even_weeks ) {
						$nextDate->modify( '+1 week' );
					}
				} else {
					if ( ! $both_are_on_even_weeks ) {
						$nextDate->modify( '+1 week' );
					}
				}
				break;
			case 4:
				// monthly
				$weekIndex = $this->getWeekIndex();
				if ( ! $weekIndex ) {
					error_log( $this->format() . ' 5th week of the month. Resetting');
					$this->periodicity = 0;
					$this->isUpdated = true;
					return false;
				}
				$weekIndexWord = $this->getWeekIndexAsWord( $weekIndex );
				$nextDate->modify( $weekIndexWord . ' ' . $this->weekDay . ' of this month' );
				if ( $nextDate < $this->today ) {
					$nextDate->modify( $weekIndexWord . ' ' . $this->weekDay . ' of next month' );
				}
				break;
			case 0:
			default: 
				return false;
		}
		$hour = intval( $this->dtObj->format( 'H' ) );
		$min = intval( $this->dtObj->format( 'i' ) );
		$nextDate->setTime( $hour, $min );

		$this->dtObj = $nextDate;

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
		} else return false;
	}

	public function isWeekly() {
		return 1 === $this->periodicity;
	}

	public function isBiweekly() {
		return 2 === $this->periodicity;
	}

	public function isMonthly() {
		return 4 === $this->periodicity;
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
