<?php

namespace adi;

class WeekdayBlacklist {
	private $allowedWeekdayIndices;
	public $firstWeekdayToSkip;
	public $secondWeekdayToSkip;

	function __construct( $firstWeekdayToSkip = 0, $secondWeekdayToSkip = 0 ) {
		$isValidFirstWeekdayToSkipIndex = ( is_numeric( $firstWeekdayToSkip ) && -1 < $firstWeekdayToSkip && 5 > $firstWeekdayToSkip );
		if ( ! $isValidFirstWeekdayToSkipIndex ) {
			error_log( 'EventDate: Invalid WeekToSkip index: ' . print_r( $firstWeekdayToSkip, true ) );
			$firstWeekdayToSkip = 0;
		}
		$this->firstWeekdayToSkip = $firstWeekdayToSkip;

		$isValidSecondWeekdayToSkipIndex = ( is_numeric( $secondWeekdayToSkip ) && -1 < $secondWeekdayToSkip && 5 > $secondWeekdayToSkip && ( $secondWeekdayToSkip === 0 || $secondWeekdayToSkip > $firstWeekdayToSkip ) );
		if ( ! $isValidSecondWeekdayToSkipIndex ) {
			error_log( 'EventDate: Invalid secondWeekdayToSkip index: ' . print_r( $secondWeekdayToSkip, true ) );
			$this->secondWeekdayToSkip = 0;
		}
		$this->secondWeekdayToSkip = $secondWeekdayToSkip;

		$this->allowedWeekdayIndices = [ 1, 2, 3, 4 ];

		if ( $firstWeekdayToSkip ) {
			$pos = array_search( $firstWeekdayToSkip, $this->allowedWeekdayIndices );
			array_splice( $this->allowedWeekdayIndices, $pos, 1 );
		}
		if ( $secondWeekdayToSkip ) {
			$pos = array_search( $secondWeekdayToSkip, $this->allowedWeekdayIndices );
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
