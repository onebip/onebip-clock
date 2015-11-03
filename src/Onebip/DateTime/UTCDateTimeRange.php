<?php
namespace Onebip\DateTime;

final class UTCDateTimeRange
{
    private $from;
    private $to;
    private $toOperator;
    private $defaultFormatter;

    public static function fromIncludedToExcluded(UTCDateTime $from, UTCDateTime $to)
    {
        return new self($from, $to, '$lt');
    }

    public static function fromIncludedToIncluded(UTCDateTime $from, UTCDateTime $to)
    {
        return new self($from, $to, '$lte');
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
            $this->toOperator => $formatter($this->to),
        ];
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

    public function toApiFormat()
    {
        return sprintf('%s..%s', $this->from->toApiFormat(), $this->to->toApiFormat());
    }
}
