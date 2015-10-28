<?php
namespace Onebip\StopWatch;

use Onebip\MicrotimeClock;
use Onebip\StopWatch;

class MicrotimeClockStopWatch implements StopWatch
{
    private $clock;
    private $start;
    private $elapsed;

    public function __construct(MicrotimeClock $clock)
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
        return $this->elapsedMilliseconds() / 1000;
    }

    /**
     * @return float
     */
    public function elapsedMilliseconds()
    {
        return $this->elapsedMicroseconds() / 1000;
    }

    /**
     * @return float
     */
    public function elapsedMicroseconds()
    {
        if (!$this->start) {
            throw new StopWatchNotStartedException();
        }

        $now = $this->clock->current();
        return $now - $this->start;
    }
}
