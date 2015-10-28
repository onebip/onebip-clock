<?php
namespace Onebip;

interface StopWatch
{
    public function start();

    /**
     * @return float
     * @throws Onebip\StopWatch\StopWatchNotStartedException
     */
    public function elapsedSeconds();

    /**
     * @return float
     * @throws Onebip\StopWatch\StopWatchNotStartedException
     */
    public function elapsedMilliseconds();

    /**
     * @return float
     * @throws Onebip\StopWatch\StopWatchNotStartedException
     */
    public function elapsedMicroseconds();
}
