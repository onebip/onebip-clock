<?php
namespace Onebip\StopWatch;

use Onebip\Clock\FixedMicrotimeClock;

class MicrotimeClockStopWatchTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->clock = new FixedMicrotimeClock(42.32);
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
        $this->clock->nowIs(98.98);

        $this->assertEquals(56.66, $this->stopWatch->elapsedMicroseconds(), '', 0.01);
        $this->assertEquals(0.05666, $this->stopWatch->elapsedMilliseconds(), '', 0.00001);
        $this->assertEquals(0.00005666, $this->stopWatch->elapsedSeconds(), '', 0.00000001);
    }
}
