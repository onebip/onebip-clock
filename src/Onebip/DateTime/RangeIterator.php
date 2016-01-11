<?php
namespace Onebip\DateTime;

class RangeIterator implements \Iterator
{
    private $from;
    private $to;
    private $comparator;
    private $incrementer;

    private $current;

    public function __construct($from, $to, callable $comparator, callable $incrementer)
    {
        $this->from = $from;
        $this->to = $to;
        $this->comparator = $comparator;
        $this->incrementer = $incrementer;

        $this->rewind();
    }

    public function current()
    {
        return $this->current;
    }

    public function key()
    {
        return $this->index;
    }

    public function next()
    {
        $this->current = call_user_func($this->incrementer, $this->current);
        $this->index++;
    }

    public function rewind()
    {
        $this->current = clone $this->from;
        $this->index = 0;
    }

    public function valid()
    {
        return call_user_func($this->comparator, $this->current, $this->to);
    }
}
