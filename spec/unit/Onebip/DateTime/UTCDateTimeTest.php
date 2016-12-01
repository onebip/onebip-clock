<?php
namespace Onebip\DateTime;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Eris;
use Eris\Generator;
use MongoDB\BSON\UTCDateTime as MongoUTCDateTime;
use MongoDate;
use PHPUnit_Framework_TestCase;

class UTCDateTimeTest extends PHPUnit_Framework_TestCase
{
    use Eris\TestTrait;

    /**
     * @requires extension mongo
     */
    public function testBoxingMongoDate()
    {
        $mongoDate = new MongoDate();
        $dateTime = UTCDateTime::box($mongoDate);

        $this->assertEquals($mongoDate, $dateTime->toMongoDate());
    }

    /**
     * @requires extension mongodb
     */
    public function testBoxingUTCMongoDate()
    {
        $mongoDate = new MongoUTCDateTime(1466170836123);
        $dateTime = UTCDateTime::box($mongoDate);

        $this->assertEquals($mongoDate, $dateTime->toMongoUTCDateTime());
    }

    public function testBoxingDateTime()
    {
        $date = new DateTime();
        $dateTime = UTCDateTime::box($date);

        $output = $dateTime->toDateTime(new DateTimeZone("Europe/Rome"));
        $this->assertEquals($date->getTimestamp(), $output->getTimestamp());
        $this->assertEquals($date, $output);
    }

    public function testBoxingDateTimeImmutable()
    {
        $date = new DateTimeImmutable('2016-01-01 12:34:56 UTC');
        $dateTime = UTCDateTime::box($date);
        $output = $dateTime->toDateTime();

        $this->assertEquals($date->getTimestamp(), $output->getTimestamp());
        $this->assertEquals($date, $output);
    }

    public function testUnboxingToDateTimeImmutable()
    {
        $this->assertEquals(
            new DateTimeImmutable('2016-01-01 12:34:56', new DateTimeZone('UTC')),
            UTCDateTime::box('2016-01-01 12:34:56')->toDateTimeImmutable()
        );
    }

    public function testBoxingNullValueReturnsNull()
    {
        $this->assertNull(UTCDateTime::box(null));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage false is not a valid value to box
     */
    public function testBoxingNonObjectNorNullThrowsException()
    {
        UTCDateTime::box(false);
    }

    /**
     * @depends testBoxingDateTime
     */
    public function testBoxingUTCDateTime()
    {
        $date = UTCDateTime::box(new \DateTime('now'));
        $this->assertEquals($date, UTCDateTime::box($date));
    }

    public function testBoxingDateTimeInTheApiFormat()
    {
        $this->assertEquals(
            UTCDateTime::fromIso8601('2014-09-01T12:01:02Z')->sec(),
            UTCDateTime::fromApiFormat('20140901120102')->sec()
        );
    }

    public function testBoxingDateTimeInTheIso8601Format()
    {
        $this->assertEquals(
            '2014-09-01T12:01:02+0000',
            UTCDateTime::fromIso8601('2014-09-01T12:01:02Z')->toIso8601()
        );
    }

    /**
     * @requires extension mongo
     */
    public function testBoxingDateTimeAndUnboxingMongoDate()
    {
        $date = new DateTime();
        $dateTime = UTCDateTime::box($date);

        $output = $dateTime->toMongoDate();

        $expectedOutput = new MongoDate(
            $date->getTimestamp(),
            0
        );

        $this->assertEquals($expectedOutput, $output);
    }

    /**
     * @requires extension mongo
     */
    public function testBoxingMongoDateAndUnboxingDateTime()
    {
        $mongoDate = new MongoDate();
        $dateTime = UTCDateTime::box($mongoDate);

        $expectedDateTime = DateTime::createFromFormat(
            "U",
            $mongoDate->sec,
            new DateTimeZone("UTC")
        );
        $expectedDateTime->setTimeZone(new DateTimeZone("UTC"));

        $this->assertEquals($expectedDateTime, $dateTime->toDateTime(new DateTimeZone("UTC")));
    }

    public function testFromStringFactoryMethod()
    {
        $expectedDate = UTCDateTime::fromTimestamp(0);
        $actualDate = UTCDateTime::fromString('1970-01-01');

        $this->assertEquals($expectedDate, $actualDate);
    }

    public function testTimezoneSetInTheStringOverwriteTheDefaultUtcTimeZone()
    {
        $expectedDate = UTCDateTime::fromString('2016-07-18T12:53:21+0000');
        $actualDate = UTCDateTime::fromString('2016-07-18T14:53:21+0200');

        $this->assertEquals($expectedDate, $actualDate);
    }

    public function testNowFactoryMethod()
    {
        $this->assertNotNull(UTCDateTime::now());
    }

    /**
     * @requires extension mongo
     */
    public function testPrecisionIsMaintainedwhenCreatedFromAMicrotimeString()
    {
        $this->assertEquals(
            UTCDateTime::box(new MongoDate(1000, 123000)),
            UTCDateTime::fromMicrotime('0.123000 1000')
        );

        $this->assertEquals(
            UTCDateTime::fromFloat("1000000001.123"),
            UTCDateTime::fromMicrotime("0.123 1000000001")
        );
    }

    /**
     * @expectedException Exception
     */
    public function testOverflowingMicrotimeString()
    {
        UTCDateTime::fromMicrotime('1 1000');
    }

    /**
     * @requires extension mongo
     */
    public function testFromIso8601FactoryMethod()
    {
        $this->assertEquals(
            new MongoDate(1401624000, 0),
            UTCDateTime::fromIso8601('2014-06-01T12:00:00+0000')->toMongoDate()
        );
    }

    public function testFromDayOfYearFactoryMethodRespectsDistanceBetweenDays()
    {
        $this->forAll(
            Generator\choose(2000, 2020),
            Generator\choose(0, 364),
            Generator\choose(0, 364)
        )
            ->then(function($year, $dayOfYear, $anotherDayOfYear) {
                $day = UTCDateTime::fromZeroBasedDayOfYear($year, $dayOfYear);
                $anotherDay = UTCDateTime::fromZeroBasedDayOfYear($year, $anotherDayOfYear);
                $this->assertEquals(
                    abs($dayOfYear - $anotherDayOfYear) * 86400,
                    abs($day->differenceInSeconds($anotherDay)),
                    "Days of the year $year: $dayOfYear, $anotherDayOfYear" . PHP_EOL
                    . "{$day->toIso8601()}, {$anotherDay->toIso8601()}"
                );
        });
    }

    public function testFromOneDayOfYearFactoryMethodRespectsDistanceBetweenDays()
    {
        $this->forAll(
            Generator\choose(2000, 2020),
            Generator\choose(1, 365),
            Generator\choose(1, 365)
        )
            ->then(function($year, $dayOfYear, $anotherDayOfYear) {
                $day = UTCDateTime::fromOneBasedDayOfYear($year, $dayOfYear);
                $anotherDay = UTCDateTime::fromOneBasedDayOfYear($year, $anotherDayOfYear);
                $this->assertEquals(
                    abs($dayOfYear - $anotherDayOfYear) * 86400,
                    abs($day->differenceInSeconds($anotherDay)),
                    "Days of the year $year: $dayOfYear, $anotherDayOfYear" . PHP_EOL
                    . "{$day->toIso8601()}, {$anotherDay->toIso8601()}"
                );
        });
    }

    public function testCanBeDumpedAsAHumanReadableString()
    {
        $this->assertEquals(
            "2001-09-09T01:46:40.123+0000",
            UTCDateTime::fromMicrotime("0.123000 1000000000")->toIso8601WithMilliseconds()
        );
    }

    public function testToYearMonth()
    {
        $this->assertEquals(
            "2001-09",
            UTCDateTime::fromString("2001-09-02 12:43:23")->toYearMonth()
        );
    }

    public function testMicrosecondsHaveAZerofillRepresentationForConsistency()
    {
        $this->assertEquals(
            "2001-09-09T01:46:40.000+0000",
            UTCDateTime::box("2001-09-09T01:46:40")->toIso8601WithMilliseconds()
        );
        $this->assertEquals(
            "2001-09-09T01:46:40.001+0000",
            UTCDateTime::fromMicrotime("0.001000 1000000000")->toIso8601WithMilliseconds()
        );
    }

    public function testMicrosecondsAreReportedDuringFormattingWhenAvailable()
    {
        $this->assertEquals(
            "2001-09-09T01:46:40.000000+0000",
            UTCDateTime::box("2001-09-09T01:46:40")->toIso8601WithMicroseconds()
        );
        $this->assertEquals(
            "2001-09-09T01:46:40.123456+0000",
            UTCDateTime::fromMicrotime("0.123456 1000000000")->toIso8601WithMicroseconds()
        );
    }

    /**
     * @requires extension mongo
     */
    public function testPrecisionIsKeptEvenDuringSubtractionOfSecondsOperation()
    {
        $this->assertEquals(
            UTCDateTime::box(new MongoDate(1000, 123000))->subtractSeconds(15),
            UTCDateTime::box(new MongoDate(985, 123000))
        );
    }

    /**
     * @requires extension mongo
     */
    public function testPrecisionIsKeptEvenDuringDifferenceOfTimesOperation()
    {
        $this->assertEquals(
            14.6,
            UTCDateTime::box(new MongoDate(1000, 123000))
                ->differenceInSeconds(
                    UTCDateTime::box(new MongoDate(985, 523000))
                )
        );
    }

    /**
     * @requires extension mongo
     */
    public function testCanAddSeconds()
    {
        $this->assertEquals(
            UTCDateTime::box(new MongoDate(1000, 123000)),
            UTCDateTime::box(new MongoDate(985, 123000))->addSeconds(15)
        );
    }

    public function testCanAddHours()
    {
        $this->assertEquals(
            UTCDateTime::box(new DateTime('2014-01-01 02:45:00')),
            UTCDateTime::box(new DateTime('2014-01-01 01:45:00'))->addHours(1)
        );
    }

    public function testCondensedIso8601Precision()
    {
        $this->assertEquals(
            "20010909014640",
            UTCDateTime::fromMicrotime("0.4 1000000000")->toCondensedIso8601()
        );
        $this->assertEquals(
            "20010909014640",
            UTCDateTime::fromMicrotime("0.49999 1000000000")->toCondensedIso8601()
        );
        $this->assertEquals(
            "20010909014641",
            UTCDateTime::fromMicrotime("0.499999 1000000000")->toCondensedIso8601()
        );
        $this->assertEquals(
            "20010909014641",
            UTCDateTime::fromMicrotime("0.5 1000000000")->toCondensedIso8601()
        );
        $this->assertEquals(
            "20010909014641",
            UTCDateTime::fromMicrotime("0.9 1000000000")->toCondensedIso8601()
        );
    }

    public function testADateIntervalCanBeAdded()
    {
        $this->assertEquals(
            UTCDateTime::fromString('2014-09-01T13:00:00Z'),
            UTCDateTime::fromString('2014-09-01T12:00:00Z')->add(new DateInterval('PT1H'))
        );
    }

    public function testCanBeComparedWithOtherObjects()
    {
        $this->assertTrue(
            UTCDateTime::fromString('2014-09-01T12:00:01Z')->greaterThan(
                UTCDateTime::fromString('2014-09-01T12:00:00Z')
            )
        );

        $this->assertFalse(
            UTCDateTime::fromString('2014-09-01T12:00:00Z')->greaterThan(
                UTCDateTime::fromString('2014-09-01T12:00:00Z')
            )
        );

        $this->assertFalse(
            UTCDateTime::fromString('2014-09-01T12:00:00Z')->greaterThan(
                UTCDateTime::fromString('2014-09-01T12:00:01Z')
            )
        );

        $this->assertTrue(
            UTCDateTime::fromString('2014-09-01T12:00:00Z')->greaterThanOrEqual(
                UTCDateTime::fromString('2014-09-01T12:00:00Z')
            )
        );

        $this->assertTrue(
            UTCDateTime::fromString('2014-09-01T12:00:00.000001Z')->greaterThanOrEqual(
                UTCDateTime::fromString('2014-09-01T12:00:00Z')
            )
        );
    }

    public function testSort() {
        $this->assertEquals(
            0,
            UTCDateTime::sort(
                UTCDateTime::fromMicrotime("0.2 1000000000"),
                UTCDateTime::fromMicrotime("0.2 1000000000")
            )
        );
        $this->assertEquals(
            -1,
            UTCDateTime::sort(
                UTCDateTime::fromMicrotime("0.1 1000000000"),
                UTCDateTime::fromMicrotime("0.2 1000000000")
            )
        );
        $this->assertEquals(
            1,
            UTCDateTime::sort(
                UTCDateTime::fromMicrotime("0.2 1000000000"),
                UTCDateTime::fromMicrotime("0.1 1000000000")
            )
        );
        $this->assertEquals(
            -1,
            UTCDateTime::sort(
                UTCDateTime::fromMicrotime("0 1000000000"),
                UTCDateTime::fromMicrotime("0 1000000001")
            )
        );
        $this->assertEquals(
            1,
            UTCDateTime::sort(
                UTCDateTime::fromMicrotime("0 1000000001"),
                UTCDateTime::fromMicrotime("0 1000000000")
            )
        );

    }

    public function testSorting()
    {
        $actual = [
            UTCDateTime::fromString ('2000-01-01'),
            UTCDateTime::fromString ('2003-01-01'),
            UTCDateTime::fromString ('2001-01-01')
        ];
        $expected = [
            UTCDateTime::fromString ('2000-01-01'),
            UTCDateTime::fromString ('2001-01-01'),
            UTCDateTime::fromString ('2003-01-01')
        ];
        usort($actual, '\Onebip\DateTime\UTCDateTime::sort');
        $this->assertEquals($expected, $actual);
    }

    public function testStartOfHour()
    {
        $date = UTCDateTime::fromString('2000-01-01 01:02:03');
        $roundedDate = $date->startOfHour();
        $this->assertEquals(
            UTCDateTime::fromString('2000-01-01 01:00:00'),
            $roundedDate
        );
    }

    public function testStartOfNextHour()
    {
        $date = UTCDateTime::fromString('2000-01-01 01:02:03');
        $roundedDate = $date->startOfNextHour();
        $this->assertEquals(
            UTCDateTime::fromString('2000-01-01 02:00:00'),
            $roundedDate
        );
    }

    public function testStartOfDay()
    {
        $date = UTCDateTime::fromString('2000-01-01 01:02:03');
        $roundedDate = $date->startOfDay();
        $this->assertEquals(
            UTCDateTime::fromString('2000-01-01 00:00:00'),
            $roundedDate
        );
    }

    public function testEndOfDay()
    {
        $date = UTCDateTime::fromString('2000-01-01 01:02:03');
        $roundedDate = $date->endOfDay();
        $this->assertEquals(
            UTCDateTime::fromString('2000-01-01 23:59:59'),
            $roundedDate
        );
    }

    public function testAddAndSubtractMonthsProperty()
    {
        $this
            ->forAll(Generator\nat())
            ->then(function ($months) {
                $date = UTCDateTime::fromString('2000-01-03 00:00:00');

                $addSub = $date->addMonths($months)->subtractMonths($months);
                $subAdd = $date->subtractMonths($months)->addMonths($months);

                $this->assertEquals(
                    $date,
                    $addSub,
                    "adding and subtracting {$months} month(s) from {$date} returned {$addSub}"
                );

                $this->assertEquals(
                    $date,
                    $subAdd,
                    "subtracting and adding {$months} month(s) from {$date} returned {$subAdd}"
                );
            });
    }

    public function testAddDays()
    {
        $expected = UTCDateTime::fromString('2000-01-03 00:00:00');
        $date = UTCDateTime::fromString('2000-01-01 00:00:00');

        $added = $date->addDays(2);

        $this->assertEquals($expected, $added);
    }

    public function testSubtractDays()
    {
        $date = UTCDateTime::fromString('2000-01-03 00:00:00');
        $expected = UTCDateTime::fromString('2000-01-01 00:00:00');

        $added = $date->subtractDays(2);

        $this->assertEquals($expected, $added);
    }

    public function testSubtractHours()
    {
        $date = UTCDateTime::fromString('2000-01-03 10:00:00');
        $expected = UTCDateTime::fromString('2000-01-03 08:00:00');

        $added = $date->subtractHours(2);

        $this->assertEquals($expected, $added);
    }

    public function testToIso8601Day()
    {
        $date = UTCDateTime::fromString('2000-01-03 00:00:00');
        $expected = '2000-01-03';

        $this->assertEquals($expected, $date->toIso8601Day());
    }

    public function testCanBeFormattedToHourlyPrecision()
    {
        $date = UTCDateTime::fromString('2000-01-03 10:00:00');
        $expected = '2000-01-03 10';

        $this->assertEquals($expected, $date->toHourlyPrecision());
    }

    public function testCanBeFormattedToHour()
    {
        $date = UTCDateTime::fromString('2000-01-03 10:00:00');
        $expected = '10';

        $this->assertEquals($expected, $date->toHour());
    }

    public function testCanBeBoxedFromHourlyPrecision()
    {
        $expected = UTCDateTime::fromString('2000-01-03 10:00:00');
        $date = '2000-01-03 10';

        $this->assertEquals($expected, UTCDateTime::fromHourlyPrecision($date));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage '2015-02-02 03:10' is not a valid hourly precision string
     */
    public function testWrongHourlyPrecisionFormatThrowsException()
    {
        UTCDateTime::fromHourlyPrecision('2015-02-02 03:10');
    }

    public function testLessThanIsFalseOnEqualDates()
    {
        $date = UTCDateTime::box('2015-01-01');

        $this->assertFalse(
            $date->lessThan($date)
        );
    }

    public function testLessThanInPositiveCase()
    {
        $date = UTCDateTime::box('2015-01-01');

        $this->assertTrue(
            $date->subtractSeconds(1)->lessThan($date)
        );
    }

    public function testItCanSetUsec()
    {
        $date = UTCDateTime::box('2015-01-01');

        $this->assertEquals(
            UTCDateTime::fromMicrotime(
                '0.123456 1420070400'
            ),
            $date->withUsec(123456)
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage usecs must be within 0 and 999999, got 1000000
     */
    public function testUsecGreaterThanRange()
    {
        UTCDateTime::box('2015-01-01')
            ->withUsec(1000000);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage usecs must be within 0 and 999999, got -1
     */
    public function testUsecLessThenRange()
    {
        UTCDateTime::box('2015-01-01')
            ->withUsec(-1);
    }

    public function testItCanBeBoxedWithCustomTimeZone()
    {
        $boxed = UTCDateTime::fromStringAndTimezone(
            '2015-06-21T16:38:00',
            new DateTimeZone('Europe/Rome')
        );

        $this->assertEquals(
            UTCDateTime::box('2015-06-21T14:38:00'),
            $boxed
        );
    }

    public function testDiff()
    {
        $this->iterations = 1000;

        $this
            ->forAll(
                Generator\nat(),
                Generator\date(
                    new DateTime('1980-01-01'),
                    new DateTime('2020-12-31')
                )
            )
            ->then(function ($days, $datetime) {
                $date = UTCDateTime::box($datetime);

                $addDiff = $date->addDays($days)->diff($date)->days;
                $subDiff = $date->subtractDays($days)->diff($date)->days;

                $this->assertSame(
                    $days,
                    $addDiff,
                    "adding and diffing {$days} days(s) from {$date->toIso8601()} returned {$addDiff}"
                );

                $this->assertEquals(
                    $days,
                    $subDiff,
                    "subtracting and diffing {$days} month(s) from {$date->toIso8601()} returned {$subDiff}"
                );
            });
    }

    public function testStartOfMonthWillGiveTheFirstDay()
    {
        $this
            ->forAll(
                Generator\date(
                    new DateTime('1980-01-01'),
                    new DateTime('2020-12-31')
                )
            )
            ->then(function (DateTime $date) {
                $date->setTimeZone(new DateTimeZone('UTC'));
                $prefix = $date->format('Y-m');

                $this->assertEquals(
                    $prefix . '-01T00:00:00.000+0000',
                    UTCDateTime::box($date)->startOfMonth()->toIso8601WithMilliseconds()
                );
            })
        ;
    }

    public function testBoxingWithFractionalSeconds()
    {
        $this->assertEquals(
            UTCDateTime::box('2016-01-26 09:34:02')->withUsec(213060),
            UTCDateTime::box('2016-01-26 09:34:02.21306')
        );

        $this->assertEquals(
            UTCDateTime::box('2016-01-26 09:34:02'),
            UTCDateTime::box('2016-01-26 09:34:02.')
        );

        $this->assertEquals(
            UTCDateTime::box('2016-01-26 09:34:02'),
            UTCDateTime::box('2016-01-26 09:34:02.0')
        );

        $this->assertEquals(
            UTCDateTime::box('2016-01-26 09:34:02')->withUsec(100000),
            UTCDateTime::box('2016-01-26 09:34:02.1')
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage expected ISO8601 with/without one fractional part separated by dot, got '2016-01-26 09:34:02.123.143'
     */
    public function testBoxingFractionalSecondsFormatErrors()
    {
        UTCDateTime::box('2016-01-26 09:34:02.123.143');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testBoxingFractionalSecondsGreaterThanRange()
    {
        UTCDateTime::box('2016-01-26 09:34:02.1234567');
    }

    public function testDebugInfo()
    {
        $iso = '2016-01-01T10:00:42.123456+0000';

        $this->assertEquals(['ISO' => $iso], UTCDateTime::box($iso)->__debugInfo());
    }

    public function testItCanBeJsonEncoded()
    {
        $iso = '2016-01-01T10:00:42.123456+0000';

        $this->assertEquals("\"$iso\"", json_encode(UTCDateTime::box($iso)));
    }
}
