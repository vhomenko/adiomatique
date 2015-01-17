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
    
    
    
    function testMofo() {
    	$event = new DateTime( 'Wed 14-01-2015 14:00' );
    	$today = new DateTime( );
    	$today->setTime( 0, 0 );
    	$expect = 'Wed 28-01-2015 14:00 Europe/Berlin';
    	
    	echo PHP_EOL, 'today: ', $today->format( 'D d-m-Y G:i' ), PHP_EOL;
    	echo 'event: ', $event->format( 'D d-m-Y G:i' ), PHP_EOL;
    	
    	$ret = adi_next_date( $event, $today, BIWEEKLY );
        $result = $ret->format( 'D d-m-Y G:i e' );
        
        $this->assertSame( $expect, $result );
    }
}

?>
