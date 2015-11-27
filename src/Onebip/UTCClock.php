<?php
namespace Onebip;

use Onebip\DateTime\UTCDateTime;

interface UTCClock
{
    /**
     * @return UTCDateTime
     */
    public function current();
}
