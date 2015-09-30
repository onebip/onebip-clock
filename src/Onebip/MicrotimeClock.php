<?php
namespace Onebip;

interface MicrotimeClock
{
    /**
     * @see microtime()
     * @return float
     */
    public function current();
}
