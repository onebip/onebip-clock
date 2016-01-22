<?php
namespace Onebip\DateTime;

final class UTCDateTimeRange
{
    private $from;
    private $to;
    private $toOperator;
    private $defaultFormatter;

    const LESS_THAN = 1;
    const LESS_THAN_EQUALS = 2;

    public static function fromIncludedToExcluded(UTCDateTime $from, UTCDateTime $to)
    {
        return new self($from, $to, self::LESS_THAN);
    }

    public static function fromIncludedToIncluded(UTCDateTime $from, UTCDateTime $to)
    {
        return new self($from, $to, self::LESS_THAN_EQUALS);
    }

    private function __construct($from, $to, $toOperator)
    {
        $this->from = $from;
        $this->to = $to;
        $this->toOperator = $toOperator;
        $this->defaultFormatter = function (UTCDateTime $date) {
            return $date->toMongoDate();
        };
    }

    public function toMongoQuery(callable $formatter = null)
    {
        $formatter = $formatter ?: $this->defaultFormatter;

        return [
            '$gte' => $formatter($this->from),
            $this->mongoOperator($this->toOperator) => $formatter($this->to),
        ];
    }

    private function mongoOperator($toOperator)
    {
        switch ($toOperator) {
            case self::LESS_THAN: return '$lt';
            case self::LESS_THAN_EQUALS: return '$lte';
        }
    }

    public function toMongoQueryOnField($fieldName, callable $formatter = null)
    {
        return [$fieldName => $this->toMongoQuery($formatter)];
    }

    /**
     * @return UTCDateTime
     */
    public function from()
    {
        return $this->from;
    }

    /**
     * @return UTCDateTime
     */
    public function to()
    {
        return $this->to;
    }

    public function toOperator()
    {
        return $this->toOperator;
    }

    public function toApiFormat()
    {
        return sprintf('%s..%s', $this->from->toApiFormat(), $this->to->toApiFormat());
    }

    public function iteratorOnHours($increment = 1)
    {
        return $this->generatorWith(
            function ($dateTime) use ($increment) {
                return $dateTime->addHours($increment);
            }
        );
    }

    public function iterateOnDays($increment = 1)
    {
        return $this->generatorWith(
            function ($dateTime) use ($increment) {
                return $dateTime->addDays($increment);
            }
        );
    }

    public function iterateOnMonths($increment = 1)
    {
        return $this->generatorWith(
            function ($dateTime) use ($increment) {
                return $dateTime->addMonths($increment);
            }
        );
    }

    private function generatorWith(callable $incrementer)
    {
        return new RangeIterator(
            $this->from,
            $this->to,
            $this->dateComparator(),
            $incrementer
        );
    }

    private function dateComparator()
    {
        switch ($this->toOperator) {
        case self::LESS_THAN:
            return function ($x, $y) { return $x < $y; };
        case self::LESS_THAN_EQUALS:
            return function ($x, $y) { return $x <= $y; };
        }
    }
}
