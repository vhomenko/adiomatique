<?php

namespace adi;

const SINGLE = 0;
const WEEKLY = 1;
const BIWEEKLY = 2;
const MONTHLY = 4;

const ADI_TZ = 'Europe/Berlin';

require_once '../../Event.php';

class EventTest extends \PHPUnit_Framework_TestCase {

	function setUp() {
	}

	function tearDown() {
	}

// format: 'd.m.y G:i', all values are with leading zeros

	function testNormalizeTime() {
		$e = new Event( 0, true );
		$this->assertSame( '14:00', $e->normalizeTime( '14:00' ) );
		$this->assertSame( '00:00',  $e->normalizeTime( '00:00' ) );
		$this->assertSame( '00:00',  $e->normalizeTime( '0:0' ) );
		$this->assertSame( '04:00',  $e->normalizeTime( '4:00' ) );
		$this->assertSame( '04:00',  $e->normalizeTime( '04:0' ) );
	}

	function testNormalizeDate() {
		$e = new Event( 0, true );
		$this->assertSame( '06.02.15', $e->normalizeDate( '06.02.15' ) );
		$this->assertSame( '06.02.15', $e->normalizeDate( '06.02.2015' ) );
		$this->assertSame( '06.02.15', $e->normalizeDate( '6.02.15' ) );
		$this->assertSame( '06.02.15', $e->normalizeDate( '06.2.15' ) );
		$this->assertSame( '06.02.08', $e->normalizeDate( '06.02.2008' ) );
	}
}

?>
