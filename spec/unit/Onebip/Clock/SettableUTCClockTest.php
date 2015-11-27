<?php
namespace Onebip\Clock;

use Onebip\DateTime\UTCDateTime;

class SettableUTCClockTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->innerClock = $this->getMock('Onebip\UTCClock');
        $this->clock = new SettableUTCClock($this->innerClock);
    }

    public function testCurrentShouldReturnStubbedTime()
    {
        $time = UTCDateTime::box('2015-02-01 10:00');
        $this->clock->setCurrent($time);

        $this->assertEquals(
            $time,
            $this->clock->current()
        );
    }

    public function testCurrentShouldBeAskedToInnerClockIfNotSet()
    {
        $time = UTCDateTime::box('2015-02-01 10:00');

        $this->innerClock
            ->expects($this->any())
            ->method('current')
            ->will($this->returnValue($time))
        ;

        $this->assertEquals(
            $time,
            $this->clock->current()
        );
    }

    public function testStubbedTimeCanBeReset()
    {
        $time = UTCDateTime::box('2015-02-01 10:00');

        $this->innerClock
            ->expects($this->any())
            ->method('current')
            ->will($this->returnValue($time))
        ;

        $this->clock->setCurrent(
            UTCDateTime::box('1985-05-21 08:40')
        );

        $this->clock->reset();

        $this->assertEquals(
            $time,
            $this->clock->current()
        );
    }
}
