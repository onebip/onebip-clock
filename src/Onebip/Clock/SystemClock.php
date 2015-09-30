<?php
namespace Onebip\Clock;
use Onebip\Clock;
use DateTime;

class SystemClock implements Clock
{
    /**
     * @return DateTime
     */
    public function current()
    {
        return new DateTime();
    }
}
