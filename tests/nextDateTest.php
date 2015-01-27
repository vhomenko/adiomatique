<?php

define( 'ABSPATH', 'stub' );

const SINGLE = 0;
const WEEKLY = 1;
const BIWEEKLY = 2;
const MONTHLY = 4;

require_once '../util.php';


class nextDateTest extends PHPUnit_Framework_TestCase {

	function setUp() {
	}

	function tearDown() {
	}

	function testDoesSkipFutureDates() {
		$dt = new DateTime();
		$dt->modify( '+1 month' );
		$result = adi_next_date( $dt, new DateTime(), 0 );
		$this->assertFalse( $result );
	}

	function testDoesSkipToday() {
		$dt = new DateTime();
		$result = adi_next_date( $dt, $dt, 0 );
		$this->assertFalse( $result );
	}

	function testDoesSkipPastNonPeriodicDates() {
		$dt = new DateTime();
		$dt->modify( '-2 weeks' );
		$result = adi_next_date( $dt, new DateTime(), SINGLE );
		$this->assertFalse( $result );
	}

	function testWeeklyDateWhichIsPassedInTheCurrentWeek() {
		$event = new DateTime( 'Mon 02-09-2013 12:00' );
		$today = new DateTime( 'Wed 17-12-2014' );
		$expect = 'Mon 22-12-2014 12:00';
		
		$ret = adi_next_date( $event, $today, WEEKLY );
		$result = $ret->format( 'D d-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testWeeklyDateWhichIsToComeInTheCurrentWeek() {
		$event = new DateTime( 'Thu 05-09-2013 12:00' );
		$today = new DateTime( 'Wed 17-12-2014' );
		$expect = 'Thu 18-12-2014 12:00';

		$ret = adi_next_date( $event, $today, WEEKLY );
		$result = $ret->format( 'D d-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testWeeklyDateWhichIsTodayInTheCurrentWeek() {
		$event = new DateTime( 'Wed 11-09-2013 12:00' );
		$today = new DateTime( 'Wed 17-12-2014' );
		$expect = 'Wed 17-12-2014 12:00';

		$ret = adi_next_date( $event, $today, WEEKLY );
		$result = $ret->format( 'D d-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testBiweeklyOddDateWhichIsPassedInTheCurrentOddWeek() {
		$event = new DateTime( 'Mon 09-09-2013 12:00' );
		$today = new DateTime( 'Wed 17-12-2014' );
		$expect = '29-12-2014 12:00';

		$ret = adi_next_date( $event, $today, BIWEEKLY );
		$result = $ret->format( 'd-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testBiweeklyEvenDateWhichIsPassedInTheCurrentEvenWeek() {
		$event = new DateTime( 'Mon 02-09-2013 12:00' );
		$today = new DateTime( 'Wed 10-12-2014' );
		$expect = '22-12-2014 12:00';

		$ret = adi_next_date( $event, $today, BIWEEKLY );
		$result = $ret->format( 'd-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testBiweeklyOddDateWhichIsPassedInTheCurrentEvenWeek() {
		$event = new DateTime( 'Mon 09-09-2013 12:00' );
		$today = new DateTime( 'Wed 10-12-2014' );
		$expect = '15-12-2014 12:00';

		$ret = adi_next_date( $event, $today, BIWEEKLY );
		$result = $ret->format( 'd-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testBiweeklyEvenDateWhichIsPassedInTheCurrentOddWeek() {
		$event = new DateTime( 'Mon 02-09-2013 12:00' );
		$today = new DateTime( 'Wed 17-12-2014' );
		$expect = '22-12-2014 12:00';

		$ret = adi_next_date( $event, $today, BIWEEKLY );
		$result = $ret->format( 'd-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testBiweeklyOddDateWhichIsToComeInTheCurrentOddWeek() {
		$event = new DateTime( 'Thu 12-09-2013 12:00' );
		$today = new DateTime( 'Wed 17-12-2014' );
		$expect = '18-12-2014 12:00';

		$ret = adi_next_date( $event, $today, BIWEEKLY );
		$result = $ret->format( 'd-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testBiweeklyEvenDateWhichToComeInTheCurrentEvenWeek() {
		$event = new DateTime( 'Thu 05-09-2013 12:00' );
		$today = new DateTime( 'Wed 10-12-2014' );
		$expect = '11-12-2014 12:00';

		$ret = adi_next_date( $event, $today, BIWEEKLY );
		$result = $ret->format( 'd-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testBiweeklyOddDateWhichIsToComeInTheCurrentEvenWeek() {
		$event = new DateTime( 'Thu 12-09-2013 12:00' );
		$today = new DateTime( 'Wed 10-12-2014' );
		$expect = '18-12-2014 12:00';

		$ret = adi_next_date( $event, $today, BIWEEKLY );
		$result = $ret->format( 'd-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testBiweeklyEvenDateWhichIsToComeInTheCurrentOddWeek() {
		$event = new DateTime( 'Thu 05-09-2013 12:00' );
		$today = new DateTime( 'Wed 17-12-2014' );
		$expect = '25-12-2014 12:00';

		$ret = adi_next_date( $event, $today, BIWEEKLY );
		$result = $ret->format( 'd-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testBiweeklyOddDateWhichIsTodayInTheCurrentEvenWeek() {
		$event = new DateTime( 'Wed 11-09-2013 12:00' );
		$today = new DateTime( 'Wed 10-12-2014' );
		$expect = '17-12-2014 12:00';

		$ret = adi_next_date( $event, $today, BIWEEKLY );
		$result = $ret->format( 'd-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testBiweeklyEvenDateWhichIsTodayInTheCurrentOddWeek() {
		$event = new DateTime( 'Wed 04-09-2013 12:00' );
		$today = new DateTime( 'Wed 17-12-2014' );
		$expect = '24-12-2014 12:00';

		$ret = adi_next_date( $event, $today, BIWEEKLY );
		$result = $ret->format( 'd-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testBiweeklyOddDateWhichIsTodayInTheCurrentOddWeek() {
		$event = new DateTime( 'Wed 11-09-2013 12:00' );
		$today = new DateTime( 'Wed 17-12-2014' );
		$expect = '17-12-2014 12:00';

		$ret = adi_next_date( $event, $today, BIWEEKLY );
		$result = $ret->format( 'd-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testBiweeklyEvenDateWhichIsTodayInTheCurrentEvenWeek() {
		$event = new DateTime( 'Wed 04-09-2013 12:00' );
		$today = new DateTime( 'Wed 10-12-2014' );
		$expect = '10-12-2014 12:00';

		$ret = adi_next_date( $event, $today, BIWEEKLY );
		$result = $ret->format( 'd-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testEveryFirstWeekdayOfTheMonth() {
		$event = new DateTime( 'Wed 04-09-2013 12:00' );
		$today = new DateTime( 'Wed 10-12-2014' );
		$expect = '07-01-2015 12:00';

		$ret = adi_next_date( $event, $today, MONTHLY );
		$result = $ret->format( 'd-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testEverySecondWeekdayOfTheMonth() {
		$event = new DateTime( 'Wed 11-09-2013 12:00' );
		$today = new DateTime( 'Wed 10-12-2014' );
		$expect = '10-12-2014 12:00';

		$ret = adi_next_date( $event, $today, MONTHLY );
		$result = $ret->format( 'd-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testEveryThirdWeekdayOfTheMonth() {
		$event = new DateTime( 'Wed 18-09-2013 12:00' );
		$today = new DateTime( 'Wed 10-12-2014' );
		$expect = '17-12-2014 12:00';

		$ret = adi_next_date( $event, $today, MONTHLY );
		$result = $ret->format( 'd-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testEveryFourthWeekdayOfTheMonth() {
		$event = new DateTime( 'Wed 25-09-2013 12:00' );
		$today = new DateTime( 'Wed 10-12-2014' );
		$expect = '24-12-2014 12:00';

		$ret = adi_next_date( $event, $today, MONTHLY );
		$result = $ret->format( 'd-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testEveryFifthWeekdayOfTheMonth() {
		$event = new DateTime( 'Sun 29-09-2013 12:00' );
		$today = new DateTime( 'Wed 10-12-2014' );

		$result = adi_next_date( $event, $today, MONTHLY );

		$this->assertFalse( $result );
	}

	function testDateFromSummertime() {
		$event = new DateTime( 'Fri 23-10-2015 12:00' );
		$today = new DateTime( 'Mon 26-10-2015' );
		$expect = 'Fri 30-10-2015 12:00';

		$ret = adi_next_date( $event, $today, WEEKLY );
		$result = $ret->format( 'D d-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testDateToSummertime() {
		$event = new DateTime( 'Mon 23-03-2015 18:00' );
		$today = new DateTime( 'Sun 29-03-2015' );
		$expect = 'Mon 30-03-2015 18:00';

		$ret = adi_next_date( $event, $today, WEEKLY );
		$result = $ret->format( 'D d-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testLeapDate() {
		$event = new DateTime( 'Mon 15-02-2016 22:00' );
		$today = new DateTime( 'Wed 24-02-2016' );
		$expect = 'Mon 29-02-2016 22:00';

		$ret = adi_next_date( $event, $today, BIWEEKLY );
		$result = $ret->format( 'D d-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testWeeklyDateWhichIsPassedInTheCurrentWeekPlusNotFirstWeekday() {
		$event = new DateTime( 'Thu 23-02-2012 23:45' );
		$today = new DateTime( 'Sat 25-02-2012' );
		$expect = 'Thu 08-03-2012 23:45';

		$ret = adi_next_date( $event, $today, WEEKLY, 1 );
		$result = $ret->format( 'D d-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testWeeklyDateWhichIsToComeInTheCurrentWeekPlusNotFirstWeekday() {
		$event = new DateTime( 'Thu 23-02-2012 0:00' );
		$today = new DateTime( 'Wed 29-02-2012' );
		$expect = 'Thu 08-03-2012 0:00';

		$ret = adi_next_date( $event, $today, WEEKLY, 1 );
		$result = $ret->format( 'D d-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testWeeklyDateWhichIsPassedInTheCurrentWeekPlusNotSecondWeekday() {
		$event = new DateTime( 'Thu 01-03-2012 0:45' );
		$today = new DateTime( 'Sat 03-03-2012' );
		$expect = 'Thu 15-03-2012 0:45';

		$ret = adi_next_date( $event, $today, WEEKLY, 2 );
		$result = $ret->format( 'D d-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testWeeklyDateWhichIsToComeInTheCurrentWeekPlusNotSecondWeekday() {
		$event = new DateTime( 'Thu 01-03-2012 0:00' );
		$today = new DateTime( 'Wed 07-03-2012' );
		$expect = 'Thu 15-03-2012 0:00';

		$ret = adi_next_date( $event, $today, WEEKLY, 2 );
		$result = $ret->format( 'D d-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testWeeklyDateWhichIsPassedInTheCurrentWeekPlusNotThirdWeekday() {
		$event = new DateTime( 'Mon 12-03-2012 1:45' );
		$today = new DateTime( 'Tue 13-03-2012' );
		$expect = 'Mon 26-03-2012 1:45';

		$ret = adi_next_date( $event, $today, WEEKLY, 3 );
		$result = $ret->format( 'D d-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testWeeklyDateWhichIsToComeInTheCurrentWeekPlusNotThirdWeekday() {
		$event = new DateTime( 'Thu 08-03-2012 1:45' );
		$today = new DateTime( 'Tue 13-03-2012' );
		$expect = 'Thu 22-03-2012 1:45';

		$ret = adi_next_date( $event, $today, WEEKLY, 3 );
		$result = $ret->format( 'D d-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testWeeklyDateWhichIsPassedInTheCurrentWeekPlusNotFourthWeekday() {
		$event = new DateTime( 'Mon 19-03-2012 1:45' );
		$today = new DateTime( 'Tue 20-03-2012' );
		$expect = 'Mon 02-04-2012 1:45';

		$ret = adi_next_date( $event, $today, WEEKLY, 4 );
		$result = $ret->format( 'D d-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testWeeklyDateWhichIsToComeInTheCurrentWeekPlusNotFourthWeekday() {
		$event = new DateTime( 'Mon 19-03-2012 1:45' );
		$today = new DateTime( 'Mon 26-03-2012' );
		$expect = 'Mon 02-04-2012 1:45';

		$ret = adi_next_date( $event, $today, WEEKLY, 4 );
		$result = $ret->format( 'D d-m-Y G:i' );

		$this->assertSame( $expect, $result );
	}

	function testWeeklyDatePlusBadIterationIndex() {
		$event = new DateTime( 'Mon 19-03-2012 1:45' );
		$today = new DateTime( 'Mon 26-03-2012' );

		$ret = adi_next_date( $event, $today, WEEKLY, 'wrong' );

		$this->assertFalse( $ret );
	}

}

?>
