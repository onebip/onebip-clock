<?php
namespace Onebip\Clock;
use Onebip\Clock;
use DateTime;
use DateInterval;

class ProgressiveClock implements Clock
{
    private $current;
    private $defaultInterval;

    public function __construct(DateTime $start = null, DateInterval $defaultInterval = null)
    {
        if ($start === null) {
            $start = new DateTime();
        }
        $this->current = $start;

        if (!$defaultInterval) {
            $this->defaultInterval = new DateInterval('PT1S');
        } else {
            $this->defaultInterval = $defaultInterval;
        }
    }

    public function current()
    {
        $toReturn = clone $this->current;
        $this->current->add($this->defaultInterval);
        return $toReturn;
    }

    public function forwardInTime(DateInterval $interval)
    {
        $this->current->add($interval);
        return $this;
    }
}
