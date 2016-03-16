<?php
namespace Onebip\Clock;

use DateTime;
use DateInterval;
use Onebip\Clock;

class SettableClock implements Clock
{
    private $current;
    
    public function __construct(DateTime $current)
    {
        $this->current = $current;
    }

    public function advance($seconds)
    {
        $this->current->add(new DateInterval("PT{$seconds}S"));
    }

    public function current()
    {
        return $this->current;
    }
}
