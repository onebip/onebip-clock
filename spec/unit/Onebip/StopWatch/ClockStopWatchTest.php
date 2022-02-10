<?php
namespace Onebip\StopWatch;

use DateTime;
use Onebip\Clock\FixedClock;
use PHPUnit\Framework\TestCase;
use Onebip\StopWatch\StopWatchNotStartedException;

class ClockStopWatchTest extends TestCase
{
    public function setUp(): void
    {
        $this->clock = FixedClock::fromIso8601('2015-02-03 00:12:43');
        $this->stopWatch = new ClockStopWatch($this->clock);
    }

    public function testElapsedSecondsWithoutStarting()
    {
        $this->expectException(StopWatchNotStartedException::class);

        $this->stopWatch->elapsedSeconds();
    }

    public function testElapsedMillisecondsWithoutStarting()
    {
        $this->expectException(StopWatchNotStartedException::class);

        $this->stopWatch->elapsedMilliseconds();
    }

    public function testElapsedMicrosecondsWithoutStarting()
    {
        $this->expectException(StopWatchNotStartedException::class);

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
