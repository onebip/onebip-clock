<?php
namespace Onebip\Clock;
use Onebip\MicrotimeClock;

class SystemMicrotimeClock implements MicrotimeClock
{
    /**
     * @return float  e.g. 1300000000.234567
     */
    public function current()
    {
        return microtime(true);
    }
}
