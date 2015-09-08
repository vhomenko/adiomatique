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

	function testBiweeklyOddDateWhichIsPassedInTheCurrentOddWeek() {
		$event = new \DateTime( 'Mon 09-09-2013 12:00' );
		$today = new \DateTime( 'Wed 17-12-2014' );
		$expect = 'Mon 29-12-2014 12:00';

		$e = new EventDate( $event, $today, BIWEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testBiweeklyEvenDateWhichIsPassedInTheCurrentEvenWeek() {
		$event = new \DateTime( 'Mon 02-09-2013 12:00' );
		$today = new \DateTime( 'Wed 10-12-2014' );
		$expect = 'Mon 22-12-2014 12:00';

		$e = new EventDate( $event, $today, BIWEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testBiweeklyOddDateWhichIsPassedInTheCurrentEvenWeek() {
		$event = new \DateTime( 'Mon 09-09-2013 12:00' );
		$today = new \DateTime( 'Wed 10-12-2014' );
		$expect = 'Mon 15-12-2014 12:00';

		$e = new EventDate( $event, $today, BIWEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testBiweeklyEvenDateWhichIsPassedInTheCurrentOddWeek() {
		$event = new \DateTime( 'Mon 02-09-2013 12:00' );
		$today = new \DateTime( 'Wed 17-12-2014' );
		$expect = 'Mon 22-12-2014 12:00';

		$e = new EventDate( $event, $today, BIWEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testBiweeklyOddDateWhichIsToComeInTheCurrentOddWeek() {
		$event = new \DateTime( 'Thu 12-09-2013 12:00' );
		$today = new \DateTime( 'Wed 17-12-2014' );
		$expect = 'Thu 18-12-2014 12:00';

		$e = new EventDate( $event, $today, BIWEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testBiweeklyEvenDateWhichToComeInTheCurrentEvenWeek() {
		$event = new \DateTime( 'Thu 05-09-2013 12:00' );
		$today = new \DateTime( 'Wed 10-12-2014' );
		$expect = 'Thu 11-12-2014 12:00';

		$e = new EventDate( $event, $today, BIWEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testBiweeklyOddDateWhichIsToComeInTheCurrentEvenWeek() {
		$event = new \DateTime( 'Thu 12-09-2013 12:00' );
		$today = new \DateTime( 'Wed 10-12-2014' );
		$expect = 'Thu 18-12-2014 12:00';

		$e = new EventDate( $event, $today, BIWEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testBiweeklyEvenDateWhichIsToComeInTheCurrentOddWeek() {
		$event = new \DateTime( 'Thu 05-09-2013 12:00' );
		$today = new \DateTime( 'Wed 17-12-2014' );
		$expect = 'Thu 25-12-2014 12:00';

		$e = new EventDate( $event, $today, BIWEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testBiweeklyOddDateWhichIsTodayInTheCurrentEvenWeek() {
		$event = new \DateTime( 'Wed 11-09-2013 12:00' );
		$today = new \DateTime( 'Wed 10-12-2014' );
		$expect = 'Wed 17-12-2014 12:00';

		$e = new EventDate( $event, $today, BIWEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testBiweeklyEvenDateWhichIsTodayInTheCurrentOddWeek() {
		$event = new \DateTime( 'Wed 04-09-2013 12:00' );
		$today = new \DateTime( 'Wed 17-12-2014' );
		$expect = 'Wed 24-12-2014 12:00';

		$e = new EventDate( $event, $today, BIWEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testBiweeklyOddDateWhichIsTodayInTheCurrentOddWeek() {
		$event = new \DateTime( 'Wed 11-09-2013 12:00' );
		$today = new \DateTime( 'Wed 17-12-2014' );
		$expect = 'Wed 17-12-2014 12:00';

		$e = new EventDate( $event, $today, BIWEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testBiweeklyEvenDateWhichIsTodayInTheCurrentEvenWeek() {
		$event = new \DateTime( 'Wed 04-09-2013 12:00' );
		$today = new \DateTime( 'Wed 10-12-2014' );
		$expect = 'Wed 10-12-2014 12:00';

		$e = new EventDate( $event, $today, BIWEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testFutureBiweeklyDate() {
		$event = new \DateTime( 'Mon 01-12-2014 0:00' );
		$today = new \DateTime( 'Fri 16-03-2012 14:36' );
		$expect = 'Mon 01-12-2014 0:00';

		$e = new EventDate( $event, $today, BIWEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testFutureBiweeklyDateOneDayBeforeNext() {
		$event = new \DateTime( 'Mon 07-09-2015 8:00' );
		$today = new \DateTime( 'Sun 20-09-2015 14:36' );
		$expect = 'Mon 21-09-2015 8:00';

		$e = new EventDate( $event, $today, BIWEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testFutureBiweeklyDateNextIsToday() {
		$event = new \DateTime( 'Mon 07-09-2015 8:00' );
		$today = new \DateTime( 'Mon 21-09-2015 14:36' );
		$expect = 'Mon 21-09-2015 8:00';

		$e = new EventDate( $event, $today, BIWEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testFutureBiweeklyDateWhichWasYesterday() {
		$event = new \DateTime( 'Mon 07-09-2015 8:00' );
		$today = new \DateTime( 'Tue 08-09-2015 14:36' );
		$expect = 'Mon 21-09-2015 8:00';

		$e = new EventDate( $event, $today, BIWEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}
}

?>
