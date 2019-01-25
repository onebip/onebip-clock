<?php
namespace Onebip\DateTime;

use DomainException;

final class UTCDateTimeRange
{
    private $from;
    private $to;
    private $toOperator;
    private $defaultFormatter;

    const LESS_THAN = 1;
    const LESS_THAN_EQUALS = 2;

    const ASCENDING = 1;
    const DESCENDING = 2;

    public static function fromIncludedToExcluded(UTCDateTime $from, UTCDateTime $to)
    {
        return new self($from, $to, self::LESS_THAN);
    }

    public static function fromIncludedToIncluded(UTCDateTime $from, UTCDateTime $to)
    {
        return new self($from, $to, self::LESS_THAN_EQUALS);
    }

    public static function fromMinimumToMaximum()
    {
        return self::fromIncludedToIncluded(
            UTCDateTime::minimum(),
            UTCDateTime::maximum()
        );
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

    public function toMongoDBQuery()
    {
        return $this->toMongoQuery(function ($date) {
            return $date->toMongoUTCDateTime();
        });
    }

    private function mongoOperator($toOperator)
    {
        switch ($toOperator) {
            case self::LESS_THAN: return '$lt';
            case self::LESS_THAN_EQUALS: return '$lte';
        }
    }

    private function toOperatorParenthesis($toOperator)
    {
        switch ($toOperator) {
            case self::LESS_THAN: return ')';
            case self::LESS_THAN_EQUALS: return ']';
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

    public function __debugInfo()
    {
        $debug = '[';
        $debug .= $this->from->toIso8601WithMicroseconds();
        $debug .= ',';
        $debug .= $this->to->toIso8601WithMicroseconds();
        $debug .= $this->toOperatorParenthesis($this->toOperator);

        return ['ISO' => $debug];
    }

    public function reverse()
    {
        if ($this->toOperator === self::LESS_THAN) {
            throw new DomainException("can't reverse an open range");
        }

        return new self(
            $this->to,
            $this->from,
            $this->toOperator
        );
    }

    public function direction()
    {
        if ($this->from->lessThanOrEqual($this->to)) {
            return self::ASCENDING;
        } else {
            return self::DESCENDING;
        }
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
