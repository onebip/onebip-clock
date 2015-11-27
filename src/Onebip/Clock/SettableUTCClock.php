<?php
namespace Onebip\Clock;

use Onebip\DateTime\UTCDateTime;
use Onebip\UTCClock;

class SettableUTCClock implements UTCClock
{
    private $fixed;

    private $innerClock;

    public function __construct(UTCClock $innerClock)
    {
        $this->innerClock = $innerClock;
    }

    public function current()
    {
        if (null === $this->fixed) {
            return $this->innerClock->current();
        }

        return $this->fixed;
    }

    public function setCurrent(UTCDateTime $fixed)
    {
        $this->fixed = $fixed;
    }

    public function elapse(\DateInterval $amount)
    {
        $this->setCurrent($this->current()->add($amount));

        return $this->current();
    }

    public function reset()
    {
        $this->fixed = null;
    }
}
