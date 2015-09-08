<?php

namespace adi;

require_once '../../EventDate.php';

const SINGLE = 0;
const WEEKLY = 1;
const BIWEEKLY = 2;
const MONTHLY = 4;

class EventDateTest extends \PHPUnit_Framework_TestCase {

	function setUp() {
	}

	function tearDown() {
	}

	function testFutureDateIsNotChanged() {
		$dt = new \DateTime();
		$dt->modify( '+1 month' );
		$e = new EventDate( $dt, null, SINGLE );
		$this->assertEquals( $e->dt, $dt );
	}

	function testTodayIsNotChanged() {
		$dt = new \DateTime();
		$e = new EventDate( $dt, $dt, SINGLE );
		$this->assertEquals( $e->dt, $dt );
	}

	function testPastNonPeriodicDatesAreSkipped() {
		$dt = new \DateTime();
		$dt->modify( '-2 weeks' );
		$e = new EventDate( $dt, null, SINGLE );
		$this->assertEquals( $e->dt, $dt );
	}

	function testLeapDate() {
		$event = new \DateTime( 'Mon 15-02-2016 22:00' );
		$today = new \DateTime( 'Wed 24-02-2016' );
		$expect = 'Mon 29-02-2016 22:00';

		$e = new EventDate( $event, $today, BIWEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testDateFromSummertime() { //25. Oktober 2015 3:00.
		$event = new \DateTime( 'Fri 23-10-2015 12:00' );
		$today = new \DateTime( 'Mon 26-10-2015' );
		$expect = 'Fri 30-10-2015 12:00';

		$e = new EventDate( $event, $today, WEEKLY );
		$this->assertEquals( $expect, $e->format() );

		$diff = $e->timestamp - $event->getTimestamp();
		$seven_days =  7 * 24 * 60 * 60;
		$one_hour = 60 * 60;
		$this->assertEquals( $diff, $seven_days + $one_hour );
	}

	function testDateToSummertime() {
		$event = new \DateTime( 'Mon 23-03-2015 18:00' );
		$today = new \DateTime( 'Sun 29-03-2015' );
		$expect = 'Mon 30-03-2015 18:00';

		$e = new EventDate( $event, $today, WEEKLY );
		$this->assertEquals( $expect, $e->format() );

		$diff = $e->timestamp - $event->getTimestamp();
		$seven_days =  7 * 24 * 60 * 60;
		$one_hour = 60 * 60;
		$this->assertEquals( $diff, $seven_days - $one_hour );
	}

}

?>
