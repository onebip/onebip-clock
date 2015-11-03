<?php
namespace Onebip\DateTime;

use MongoDate;
use PHPUnit_Framework_TestCase;

class UTCDateTimeRangeTest extends PHPUnit_Framework_TestCase
{
    public function testItCanBuildAClosedInterval()
    {
        $range = UTCDateTimeRange::fromIncludedToIncluded(
            UTCDateTime::box('1985-05-21'),
            UTCDateTime::box('2015-05-21')
        );

        $this->assertEquals(
            [
                '$gte' => new MongoDate(485481600),
                '$lte' => new MongoDate(1432166400),
            ],
            $range->toMongoQuery()
        );
    }

    public function testItCanBuildARightOpenInterval()
    {
        $range = UTCDateTimeRange::fromIncludedToExcluded(
            UTCDateTime::box('1985-05-21'),
            UTCDateTime::box('2015-05-21')
        );

        $this->assertEquals(
            [
                '$gte' => new MongoDate(485481600),
                '$lt' => new MongoDate(1432166400),
            ],
            $range->toMongoQuery()
        );
    }

    public function testToMongoQueryOnFieldCanUseACallbackFormatter()
    {
        $range = UTCDateTimeRange::fromIncludedToIncluded(
            UTCDateTime::box('1985-05-21'),
            UTCDateTime::box('2015-05-21')
        );

        $this->assertEquals(
            [
                'goofy' => [
                    '$gte' => '1985-05-21 00',
                    '$lte' => '2015-05-21 00',
                ]
            ],
            $range->toMongoQueryOnField('goofy', function(UTCDateTime $date) {
                return $date->toDateTime()->format('Y-m-d H');
            })
        );
    }

    public function testToMongoQueryOnFieldShouldReturnTheSameQueryTheNotParameterizedVersion()
    {
        $range = UTCDateTimeRange::fromIncludedToIncluded(
            UTCDateTime::box('1985-05-21'),
            UTCDateTime::box('2015-05-21')
        );

        $this->assertEquals(
            [
                'goofy' => [
                    '$gte' => new MongoDate(485481600),
                    '$lte' => new MongoDate(1432166400),
                ]
            ],
            $range->toMongoQueryOnField('goofy')
        );
    }

    public function testItCanBeFormattedWithACallback()
    {
        $range = UTCDateTimeRange::fromIncludedToExcluded(
            UTCDateTime::box('1985-05-21 10:00'),
            UTCDateTime::box('2015-05-21 12:00')
        );

        $callback = function (UTCDateTime $date) {
            return $date->toDateTime()->format('Y-m-d H');
        };

        $this->assertEquals(
            [
                '$gte' => '1985-05-21 10',
                '$lt' => '2015-05-21 12',
            ],
            $range->toMongoQuery($callback)
        );
    }

    public function testItCanExposeFrom()
    {
        $range = UTCDateTimeRange::fromIncludedToExcluded(
            $from = UTCDateTime::box('1985-05-21 10:00'),
            UTCDateTime::box('2015-05-21 12:00')
        );

        $this->assertEquals($from, $range->from());
    }

    public function testItCanBeConvertedInApiFormat()
    {
        $range = UTCDateTimeRange::fromIncludedToExcluded(
            UTCDateTime::box('2015-01-01'),
            UTCDateTime::box('2015-01-02')
        );

        $this->assertEquals('20150101000000..20150102000000', $range->toApiFormat());
    }
}
