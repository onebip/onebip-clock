<?php
namespace Onebip\StopWatch;

use Onebip\Clock\FixedMicrotimeClock;

class MicrotimeClockStopWatchTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->clock = new FixedMicrotimeClock(45.123456);
        $this->stopWatch = new MicrotimeClockStopWatch($this->clock);
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
        $this->clock->nowIs(98.987653);

        $this->assertEquals(53.864197, $this->stopWatch->elapsedSeconds(), '', 0.000001);
        $this->assertEquals(53864.197, $this->stopWatch->elapsedMilliseconds(), '', 0.001);
        $this->assertEquals(53864197, $this->stopWatch->elapsedMicroseconds(), '', 1);
    }
}
