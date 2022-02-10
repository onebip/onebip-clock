<?php
namespace Onebip\StopWatch;

use Onebip\Clock\FixedMicrotimeClock;
use PHPUnit\Framework\TestCase;
use Onebip\StopWatch\StopWatchNotStartedException;

class MicrotimeClockStopWatchTest extends TestCase
{
    public function setUp(): void
    {
        $this->clock = new FixedMicrotimeClock(45.123456);
        $this->stopWatch = new MicrotimeClockStopWatch($this->clock);
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
        $this->clock->nowIs(98.987653);

        $this->assertEquals(53.864197, $this->stopWatch->elapsedSeconds(), '', 0.000001);
        $this->assertEquals(53864.197, $this->stopWatch->elapsedMilliseconds(), '', 0.001);
        $this->assertEquals(53864197, $this->stopWatch->elapsedMicroseconds(), '', 1);
    }
}
