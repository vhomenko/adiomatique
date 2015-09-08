<?php

namespace adi;

require_once "WeekdayBlacklist.php";

class EventDate {
	public $dt;
	public $date;
	public $fullDate;
	public $time;
	public $weekday;
	private $weekNum;
	public $periodicity;
	public $weekdayBlacklist;
	private $today;
	public $timestamp;
	public $isUpdated = false;

	public function __construct ( $eventDT, $todayDT, $periodicity, $firstWeekdayToSkip = 0, $secondWeekdayToSkip = 0 ) {
		$this->dt = $eventDT;
		if ( ! isset( $todayDT ) )
			$this->today = new \DateTime();
		else
			$this->today = $todayDT;
		$this->today->setTime( 0, 0 );
		$this->periodicity = $periodicity;
		$this->weekdayBlacklist = new WeekdayBlacklist( $firstWeekdayToSkip, $secondWeekdayToSkip );

		$this->updateFields();
		$this->update();
	}

	private function updateFields() {
		$this->timestamp = $this->dt->getTimestamp();
		$this->time = $this->dt->format( 'H:i' );
		$this->date = $this->dt->format( 'd.m' );
		$this->fullDate = $this->dt->format( 'd.m.y' );
		$this->weekNum = intval( $this->dt->format( 'W' ) );
		$this->weekday = $this->dt->format( 'l' );
	}

	public function format() {
		return $this->dt->format( 'D d-m-Y G:i' );
	}

	public function isPassed() {
		return $this->dt < $this->today;
	}

	public function isOnOddWeek() {
		return 0 !== $this->weekNum % 2;
	}

	private function update() {
		$end = clone $this->today;

		// needed for weekly and biweekly
		if ( $this->dt > $this->today )
			$end = clone $this->dt;

		switch ( $this->periodicity ) {
			case 1:
				$interval = new \DateInterval( 'P1W' );
				$end->modify( '+6 weeks' );
				$period = new \DatePeriod( $this->dt, $interval, $end );
				foreach ( $period as $dt ) {
					if ( $dt >= $this->today ) {
						if ( false !== $this->weekdayBlacklist->verify( $dt ) ) {
							$this->dt = $dt;
							break;
						}
					}
				}
				break;
			case 2:
				$interval = new \DateInterval( 'P2W' );
				$end->modify( '+2 weeks' );
				$period = new \DatePeriod( $this->dt, $interval, $end );
				foreach ( $period as $dt ) {
					$this->dt = $dt;
				}
				break;
			case 4:
				$weekDayIndex = $this->weekdayBlacklist->getWeekDayIndex( $this->dt );
				if ( -1 === $weekDayIndex ) {
					error_log( $this->format() . ' 5th weekday of the month. Resetting');
					$this->periodicity = 0;
					$this->isUpdated = true;
					return;
				}
				$weekDayIndexWord = $this->getWeekdayIndexAsWord( $weekDayIndex );
				$weekDay = $this->dt->format( 'l' );
				$nextDate = clone $this->today;
				$nextDate->modify( $weekDayIndexWord . ' ' . $weekDay . ' of this month' );
				if ( $nextDate < $this->today ) {
					$nextDate->modify( $weekDayIndexWord . ' ' . $weekDay . ' of next month' );
				}
				$hour = intval( $this->dt->format( 'H' ) );
				$min = intval( $this->dt->format( 'i' ) );
				$nextDate->setTime( $hour, $min );
				$this->dt = $nextDate;
				break;
			case 0:
			default:
				break;
		}
		$this->updateFields();
		$this->isUpdated = true;
	}
/*
	public function isWeekly() {
		return 1 === $this->periodicity;
	}

	public function isBiweekly() {
		return 2 === $this->periodicity;
	}

	public function isMonthly() {
		return 4 === $this->periodicity;
	}
*/
	function getWeekdayIndexAsWord( $index ) {
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

}

?>
