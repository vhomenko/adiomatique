<?php

namespace adi;

class WeekdayBlacklist {
	private $allowedWeekdayIndices;
	public $weekdayToSkip;
	public $extraWeekdayToSkip;

	function __construct( $weekdayToSkip = 0, $extraWeekdayToSkip = 0 ) {
		$isValidWeekdayToSkipIndex = ( is_numeric( $weekdayToSkip ) && -1 < $weekdayToSkip && 5 > $weekdayToSkip );
		if ( ! $isValidWeekdayToSkipIndex ) {
			error_log( 'EventDate: Invalid weekdayToSkip index: ' . print_r( $weekdayToSkip, true ) );
			$weekdayToSkip = 0;
		}
		$this->weekdayToSkip = $weekdayToSkip;

		$isValidExtraWeekdayToSkipIndex = ( is_numeric( $extraWeekdayToSkip ) && -1 < $extraWeekdayToSkip && 5 > $extraWeekdayToSkip && ( $extraWeekdayToSkip === 0 || $extraWeekdayToSkip > $weekdayToSkip ) && 1 !== $extraWeekdayToSkip );
		if ( ! $isValidExtraWeekdayToSkipIndex ) {
			error_log( 'EventDate: Invalid extraWeekdayToSkip index: ' . print_r( $extraWeekdayToSkip, true ) );
			$this->extraWeekdayToSkip = 0;
		}
		$this->extraWeekdayToSkip = $extraWeekdayToSkip;

		$this->allowedWeekdayIndices = [ 1, 2, 3, 4 ];

		if ( $weekdayToSkip ) {
			$pos = array_search( $weekdayToSkip, $this->allowedWeekdayIndices );
			array_splice( $this->allowedWeekdayIndices, $pos, 1 );
		}
		if ( $extraWeekdayToSkip ) {
			$pos = array_search( $extraWeekdayToSkip, $this->allowedWeekdayIndices );
			array_splice( $this->allowedWeekdayIndices, $pos, 1 );
		}
	}

	public function verify( $dt ) {
		// if no weekdays to skip, don't skip the fifth weekday either
		if ( count( $this->allowedWeekdayIndices ) === 4 ) return true;
		
		$wi = $this->getWeekdayIndex( $dt );
		if ( $wi === -1 ) return false;
		return array_search( $wi, $this->allowedWeekdayIndices );
	}

	public function getWeekdayIndex( $dt ) {
		$dom = intval( $dt->format( 'j' ) );
		if ( 8 > $dom ) {
			return 1;
		} else if ( 15 > $dom ) { 
			return 2;
		} else if ( 22 > $dom ) { 
			return 3;
		} else if ( 29 > $dom ) { 
			return 4;
		} else {
			// always skip the fifth weekday
			return -1;
		}
	}
}

?>
