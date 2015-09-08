<?php

namespace adi;

require_once '../../EventDate.php';

const SINGLE = 0;
const WEEKLY = 1;
const BIWEEKLY = 2;
const MONTHLY = 4;

class EventDateWeeklyTest extends \PHPUnit_Framework_TestCase {

	function setUp() {
	}

	function tearDown() {
	}

	function testWeeklyDateWhichIsPassedInTheCurrentWeek() {
		$event = new \DateTime( 'Mon 02-09-2013 12:00' );
		$today = new \DateTime( 'Wed 17-12-2014' );
		$expect = 'Mon 22-12-2014 12:00';
		
		$e = new EventDate( $event, $today, WEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testWeeklyDateWhichIsToComeInTheCurrentWeek() {
		$event = new \DateTime( 'Thu 05-09-2013 12:00' );
		$today = new \DateTime( 'Wed 17-12-2014' );
		$expect = 'Thu 18-12-2014 12:00';

		$e = new EventDate( $event, $today, WEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testWeeklyDateWhichIsTodayInTheCurrentWeek() {
		$event = new \DateTime( 'Wed 11-09-2013 12:00' );
		$today = new \DateTime( 'Wed 17-12-2014' );
		$expect = 'Wed 17-12-2014 12:00';

		$e = new EventDate( $event, $today, WEEKLY );
		$this->assertEquals( $expect, $e->format() );
	}

	function testWeeklyDateWhichIsPassedInTheCurrentWeekPlusNotFirstWeekday() {
		$event = new \DateTime( 'Thu 23-02-2012 23:45' );
		$today = new \DateTime( 'Sat 25-02-2012' );
		$expect = 'Thu 08-03-2012 23:45';

		$e = new EventDate( $event, $today, WEEKLY, 1 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testWeeklyDateWhichIsToComeInTheCurrentWeekPlusNotFirstWeekday() {
		$event = new \DateTime( 'Thu 23-02-2012 0:00' );
		$today = new \DateTime( 'Wed 29-02-2012' );
		$expect = 'Thu 08-03-2012 0:00';

		$e = new EventDate( $event, $today, WEEKLY, 1 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testWeeklyDateWhichIsPassedInTheCurrentWeekPlusNotSecondWeekday() {
		$event = new \DateTime( 'Thu 01-03-2012 0:45' );
		$today = new \DateTime( 'Sat 03-03-2012' );
		$expect = 'Thu 15-03-2012 0:45';

		$e = new EventDate( $event, $today, WEEKLY, 2 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testWeeklyDateWhichIsToComeInTheCurrentWeekPlusNotSecondWeekday() {
		$event = new \DateTime( 'Thu 01-03-2012 0:00' );
		$today = new \DateTime( 'Wed 07-03-2012' );
		$expect = 'Thu 15-03-2012 0:00';

		$e = new EventDate( $event, $today, WEEKLY, 2 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testWeeklyDateWhichIsPassedInTheCurrentWeekPlusNotThirdWeekday() {
		$event = new \DateTime( 'Mon 12-03-2012 1:45' );
		$today = new \DateTime( 'Tue 13-03-2012' );
		$expect = 'Mon 26-03-2012 1:45';

		$e = new EventDate( $event, $today, WEEKLY, 3 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testWeeklyDateWhichIsToComeInTheCurrentWeekPlusNotThirdWeekday() {
		$event = new \DateTime( 'Thu 08-03-2012 1:45' );
		$today = new \DateTime( 'Tue 13-03-2012' );
		$expect = 'Thu 22-03-2012 1:45';

		$e = new EventDate( $event, $today, WEEKLY, 3 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testWeeklyDateWhichIsPassedInTheCurrentWeekPlusNotFourthWeekday() {
		$event = new \DateTime( 'Mon 19-03-2012 1:45' );
		$today = new \DateTime( 'Tue 20-03-2012' );
		$expect = 'Mon 02-04-2012 1:45';

		$e = new EventDate( $event, $today, WEEKLY, 4 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testWeeklyDateWhichIsToComeInTheCurrentWeekPlusNotFourthWeekday() {
		$event = new \DateTime( 'Mon 19-03-2012 1:45' );
		$today = new \DateTime( 'Mon 26-03-2012' );
		$expect = 'Mon 02-04-2012 1:45';

		$e = new EventDate( $event, $today, WEEKLY, 4 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testWeeklyDatePlusNotFirstWeekdayBeingIrrelevant() {
		$event = new \DateTime( 'Thu 01-03-2012 0:00' );
		$today = new \DateTime( 'Wed 07-03-2012' );
		$expect = 'Thu 08-03-2012 0:00';

		$e = new EventDate( $event, $today, WEEKLY, 1 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testWeeklyDatePlusNotSecondWeekdayBeingIrrelevant() {
		$event = new \DateTime( 'Mon 12-03-2012 1:45' );
		$today = new \DateTime( 'Tue 13-03-2012' );
		$expect = 'Mon 19-03-2012 1:45';

		$e = new EventDate( $event, $today, WEEKLY, 2 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testWeeklyDatePlusNotThirdWeekdayBeingIrrelevant() {
		$event = new \DateTime( 'Thu 01-03-2012 0:00' );
		$today = new \DateTime( 'Wed 07-03-2012' );
		$expect = 'Thu 08-03-2012 0:00';

		$e = new EventDate( $event, $today, WEEKLY, 3 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testWeeklyDatePlusNotFourthWeekdayBeingIrrelevant() {
		$event = new \DateTime( 'Thu 01-03-2012 0:00' );
		$today = new \DateTime( 'Wed 07-03-2012' );
		$expect = 'Thu 08-03-2012 0:00';

		$e = new EventDate( $event, $today, WEEKLY, 4 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testTodaysWeeklyDateSetOnFirstWeekPlusNotFirstWeekday() {
		$event = new \DateTime( 'Thu 01-03-2012 0:00' );
		$today = new \DateTime( 'Thu 01-03-2012' );
		$expect = 'Thu 08-03-2012 0:00';

		$e = new EventDate( $event, $today, WEEKLY, 1 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testTooFarInTheFutureWeeklyDateSetOnFourthWeekPlusNotFourthWeekday() {
		$event = new \DateTime( 'Thu 22-03-2012 0:00' );
		$today = new \DateTime( 'Fri 09-03-2012 16:00' );
		$expect = 'Thu 05-04-2012 0:00';

		$e = new EventDate( $event, $today, WEEKLY, 4 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testFutureWeeklyDateSetOnFourthWeekPlusNotFourthWeekday() {
		$event = new \DateTime( 'Thu 22-03-2012 0:00' );
		$today = new \DateTime( 'Fri 16-03-2012' );
		$expect = 'Thu 05-04-2012 0:00';

		$e = new EventDate( $event, $today, WEEKLY, 4 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testFutureWeeklyDatePlusNotFirstAndThirdWeekday() {
		$event = new \DateTime( 'Wed 09-09-2015 20:00' );
		$today = new \DateTime( 'Sun 30-08-2015 14:36' );
		$expect = 'Wed 09-09-2015 20:00';

		$e = new EventDate( $event, $today, WEEKLY, 1, 3 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testWeeklyDatePlusNotFirstAndThirdWeekdayNeitherFifth() {
		$event = new \DateTime( 'Wed 09-09-2015 20:00' );
		$today = new \DateTime( 'Thu 24-09-2015 14:36' );
		$expect = 'Wed 14-10-2015 20:00';

		$e = new EventDate( $event, $today, WEEKLY, 1, 3 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testFutureWeeklyDatePlusNotFirstAndSecondWeek() {
		$event = new \DateTime( 'Mon 01-12-2014 0:00' );
		$today = new \DateTime( 'Fri 16-03-2012 14:36' );
		$expect = 'Mon 15-12-2014 0:00';

		$e = new EventDate( $event, $today, WEEKLY, 1, 2 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testWeeklyDatePlusNotThirdAndFourthWeekdayNeitherFifth() {
		$event = new \DateTime( 'Thu 09-07-2015 20:00' );
		$today = new \DateTime( 'Fri 10-07-2015 14:36' );
		$expect = 'Thu 06-08-2015 20:00';

		$e = new EventDate( $event, $today, WEEKLY, 3, 4 );
		$this->assertEquals( $expect, $e->format() );
	}

	function testWeeklyDateWhichIsPassedInTheCurrentWeekPlusNotSecondNotThirdWeekday() {
		$event = new \DateTime( 'Thu 01-03-2012 0:45' );
		$today = new \DateTime( 'Sat 03-03-2012' );
		$expect = 'Thu 22-03-2012 0:45';

		$e = new EventDate( $event, $today, WEEKLY, 2, 3 );
		$this->assertEquals( $expect, $e->format() );
	}

}

?>
