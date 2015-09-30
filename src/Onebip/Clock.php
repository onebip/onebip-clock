<?php
namespace Onebip;

interface Clock
{
    /**
     * @return DateTime
     */
    public function current();
}
