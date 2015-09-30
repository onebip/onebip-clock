<?php
namespace Onebip\Clock;
use Onebip\Clock;
use DateTime;
use DateInterval;

class AcceleratedClock implements Clock
{
    private $time;

    public function __construct(DateTime $time)
    {
        $this->time = $time;
    }

    /**
     * @return DateTime
     */
    public function current()
    {
        return clone $this->time;
    }

    public function advance(DateInterval $interval)
    {
        $this->time->add($interval);
    }
}
