<?php
namespace Onebip\Clock;

use Onebip\DateTime\UTCDateTime;
use Onebip\UTCClock;

class SystemUTCClock implements UTCClock
{
    public function current()
    {
        return UTCDateTime::fromMicrotime(microtime());
    }
}
