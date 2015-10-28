<?php
namespace Onebip\StopWatch;

use DateTime;
use Onebip\Clock\FixedClock;

class ClockStopWatchTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->clock = FixedClock::fromIso8601('2015-02-03 00:12:43');
        $this->stopWatch = new ClockStopWatch($this->clock);
    }

    /**
     * @expectedException Onebip\StopWatch\StopWatchNotStartedException
     */
    public function testElapsedSecondsWithoutStarting()
    {
        $this->stopWatch->elapsedSeconds();
    }

    /**
     * @expectedException Onebip\StopWatch\StopWatchNotStartedException
     */
    public function testElapsedMillisecondsWithoutStarting()
    {
        $this->stopWatch->elapsedMilliseconds();
    }

    /**
     * @expectedException Onebip\StopWatch\StopWatchNotStartedException
     */
    public function testElapsedMicrosecondsWithoutStarting()
    {
        $this->stopWatch->elapsedMicroseconds();
    }

    public function testElapsedAfterStopping()
    {
        $this->stopWatch->start();
        $this->clock->nowIs(new DateTime('2015-02-03 00:12:49'));

        $this->assertEquals(6.0, $this->stopWatch->elapsedSeconds(), '', 0.1);
        $this->assertEquals(6000.0, $this->stopWatch->elapsedMilliseconds(), '', 0.1);
        $this->assertEquals(6000000.0, $this->stopWatch->elapsedMicroseconds(), '', 0.1);
    }
}
