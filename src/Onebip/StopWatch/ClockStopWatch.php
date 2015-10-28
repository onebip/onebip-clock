<?php
namespace Onebip\StopWatch;

use Onebip\Clock;
use Onebip\StopWatch;

class ClockStopWatch implements StopWatch
{
    private $clock;
    private $start;
    private $elapsed;

    public function __construct(Clock $clock)
    {
        $this->clock = $clock;
    }

    public function start()
    {
        $this->start = $this->clock->current();
    }

    /**
     * @return float
     */
    public function elapsedSeconds()
    {
        if (!$this->start) {
            throw new StopWatchNotStartedException();
        }

        $now = $this->clock->current();
        return (float)$now->diff($this->start)->s;
    }

    /**
     * @return float
     */
    public function elapsedMilliseconds()
    {
        return $this->elapsedSeconds() * 1000;
    }

    /**
     * @return float
     */
    public function elapsedMicroseconds()
    {
        return $this->elapsedMilliseconds() * 1000;
    }
}
