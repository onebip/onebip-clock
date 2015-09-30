<?php
namespace Onebip\Clock;
use Onebip\Clock;
use DateTime;

class FixedClock implements Clock
{
    private $time;

    public static function fromIso8601($timeRepresentation)
    {
        return new self(new DateTime($timeRepresentation));
    }

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

    public function nowIs(DateTime $time)
    {
        $this->time = $time;
    }
}
