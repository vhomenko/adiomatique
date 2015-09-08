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

	function testEveryFirstWeekdayOfTheMonth() {
		$event = new \DateTime( 'Wed 04-09-2013 12:00' );
		$today = new \DateTime( 'Wed 10-12-2014' );
		$expect = 'Wed 07-01-2015 12:00';

		$e = new EventDate( $event, $today, MONTHLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testEverySecondWeekdayOfTheMonth() {
		$event = new \DateTime( 'Wed 11-09-2013 12:00' );
		$today = new \DateTime( 'Wed 10-12-2014' );
		$expect = 'Wed 10-12-2014 12:00';

		$e = new EventDate( $event, $today, MONTHLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testEveryThirdWeekdayOfTheMonth() {
		$event = new \DateTime( 'Wed 18-09-2013 12:00' );
		$today = new \DateTime( 'Wed 10-12-2014' );
		$expect = 'Wed 17-12-2014 12:00';

		$e = new EventDate( $event, $today, MONTHLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testEveryFourthWeekdayOfTheMonth() {
		$event = new \DateTime( 'Wed 25-09-2013 12:00' );
		$today = new \DateTime( 'Wed 10-12-2014' );
		$expect = 'Wed 24-12-2014 12:00';

		$e = new EventDate( $event, $today, MONTHLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testEveryFifthWeekdayOfTheMonth() {
		$event = new \DateTime( 'Sun 29-09-2013 12:00' );
		$today = new \DateTime( 'Wed 16-10-2013' );
		$expect = 'Sun 29-09-2013 12:00';

		$e = new EventDate( $event, $today, MONTHLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testFutureMonthlyDate() {
		$event = new \DateTime( 'Mon 07-09-2015 8:00' );
		$today = new \DateTime( 'Tue 08-09-2015 14:36' );
		$expect = 'Mon 05-10-2015 8:00';

		$e = new EventDate( $event, $today, MONTHLY );
		$this->assertEquals( $expect, $e->format() );
	}
}

?>
