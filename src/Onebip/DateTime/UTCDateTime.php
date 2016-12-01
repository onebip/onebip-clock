<?php
namespace Onebip\DateTime;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use JsonSerializable;
use MongoDB\BSON\UTCDateTime as MongoUTCDateTime;
use MongoDate;

final class UTCDateTime implements JsonSerializable
{
    private $sec;
    private $usec;

    private function __construct($sec, $usec = 0)
    {
        $this->sec = (int) $sec;
        $this->usec = (int) $usec;
    }

    public function __toString()
    {
        return $this->sec . ' ' . $this->usec;
    }

    public function toMongoDate()
    {
        return new MongoDate($this->sec, $this->usec);
    }

    public function toMongoUTCDateTime()
    {
        return new MongoUTCDateTime(
            $this->sec * 1000 + (int) round($this->usec / 1000)
        );
    }

    public function toDateTime(DateTimeZone $timeZone = null)
    {
        if(is_null($timeZone)) {
            $timeZone = new DateTimeZone("UTC");
        }
        $date = DateTime::createFromFormat(
            "U",
            $this->sec
        );
        $date->setTimeZone($timeZone);

        return $date;
    }

    public function toDateTimeImmutable(DateTimeZone $timeZone = null)
    {
        return DateTimeImmutable::createFromMutable($this->toDateTime($timeZone));
    }

    public function toIso8601WithMilliseconds()
    {
        $isoRepresentation = $this->toDateTime()
            ->format(DateTime::ISO8601) ;
        return $this->insertSubseconds($isoRepresentation, $this->usec / 1000, 3);
    }

    public function toIso8601WithMicroseconds()
    {
        $isoRepresentation = $this->toDateTime()
            ->format(DateTime::ISO8601) ;
        return $this->insertSubseconds($isoRepresentation, $this->usec, 6);
    }

    private function insertSubseconds($isoRepresentation, $subseconds, $padding)
    {
        return str_replace(
            '+',
            '.' . sprintf("%0{$padding}d", $subseconds) . '+',
            $isoRepresentation
        );
    }

    public function toIso8601()
    {
        return $this->toDateTime()->format(DateTime::ISO8601);
    }

    public function toIso8601Day()
    {
        return $this->toDateTime()->format('Y-m-d');
    }

    public function toCondensedIso8601()
    {
        $roundedValue = round($this-> sec + ($this->usec / 1000 / 1000));
        return (new DateTime("@{$roundedValue}"))->format('YmdHis');
    }

    public function toApiFormat()
    {
        return $this->toCondensedIso8601();
    }

    public function sec()
    {
        return $this->sec;
    }

    public function usec()
    {
        return $this->usec;
    }

    public static function box($dateToBox)
    {
        if (is_null($dateToBox) || $dateToBox instanceof static) {
            return $dateToBox;
        }

        if (is_string($dateToBox)) {
            return self::fromString($dateToBox);
        }

        if (!is_object($dateToBox)) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s is not a valid value to box',
                    var_export($dateToBox, true)
                )
            );
        }

        if ($dateToBox instanceof MongoUTCDateTime) {
            $msec = (string)$dateToBox + 0;

            return new self(
                (int) ($msec / 1000),
                1000 * ($msec % 1000)
            );
        }

        $clonedDateToBox = clone $dateToBox;

        if ($clonedDateToBox instanceof MongoDate) {
            return new self($clonedDateToBox->sec, $clonedDateToBox->usec);
        }
        if ($clonedDateToBox instanceof DateTimeInterface) {
            return new self($clonedDateToBox->getTimestamp(), 0);
        }
    }

    public static function fromStringAndtimezone($string, DateTimeZone $timeZone)
    {
        $pieces = explode('.', $string);

        switch (count($pieces)) {
        case 1:
            return self::box(new DateTime($string, $timeZone));
        case 2:
            list($dateTime, $fractional) = $pieces;
            $padded = str_pad($fractional, 6, '0', STR_PAD_RIGHT);

            return self::box(new DateTime($dateTime, $timeZone))
                        ->withUsec((int)$padded);
        default:
            throw new InvalidArgumentException(
                "expected ISO8601 with/without one fractional part separated by dot, got " . var_export($string, true)
            );
        }
    }

    public static function fromString($string)
    {
        return self::fromStringAndtimezone($string, new DateTimeZone('UTC'));
    }

    public static function fromHourlyPrecision($string)
    {
        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}$/', $string)) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s is not a valid hourly precision string',
                    var_export($string, true)
                )
            );
        }

        return self::fromString($string . ':00');
    }

    public static function fromTimestamp($timestamp)
    {
        return new self($timestamp);
    }

    public static function now()
    {
        return self::fromMicrotime(microtime());
    }

    public static function fromMicrotime($microtimeString)
    {
        list($usec, $sec) = explode(" ", $microtimeString);
        if($usec >= 1) {
            throw new \Exception("usec parameter can’t be more than 1 second: {$usec}");
        }
        return new self($sec, $usec * 1000 * 1000);
    }

    public static function fromFloat($timeInSeconds)
    {
        $sec = floor($timeInSeconds);
        $usec = $timeInSeconds - $sec;
        return new self($sec, $usec * 1000 * 1000);
    }

    public static function fromZeroBasedDayOfYear($year, $days)
    {
        $d = DateTime::createFromFormat('Y-m-d H:i:s', "$year-01-01 00:00:00", new DateTimeZone("UTC"));

        return self::box($d)->addDays($days)->startOfDay();
    }

    public static function fromOneBasedDayOfYear($year, $days)
    {
        return self::fromZeroBasedDayOfYear($year, $days - 1);
    }

    public static function fromIso8601($formattedString)
    {
        return self::fromString($formattedString);
    }

    public static function fromApiFormat($formattedString)
    {
        return self::fromString($formattedString);
    }

    public static function minimum()
    {
        return new self(0);
    }

    public static function maximum()
    {
        return new self(pow(2, 32));
    }

    public function subtractSeconds($seconds)
    {
        return $this->addSeconds(-$seconds);
    }

    public function addSeconds($seconds)
    {
        return new self($this->sec + $seconds, $this->usec);
    }

    public function add(DateInterval $interval)
    {
        $newDateTime = $this->toDateTime();
        $newDateTime->add($interval);
        return self::box($newDateTime);
    }

    public function addMonths($months)
    {
        return $this->add(new DateInterval(sprintf('P%dM', $months)));
    }

    public function subtractMonths($months)
    {
        return $this->sub(new DateInterval(sprintf('P%dM', $months)));
    }

    public function addDays($days)
    {
        return $this->add(new DateInterval(sprintf('P%dD', $days)));
    }

    public function subtractDays($days)
    {
        return $this->sub(new DateInterval(sprintf('P%dD', $days)));
    }

    public function addHours($hours)
    {
        return $this->add(new DateInterval(sprintf('PT%dH', $hours)));
    }

    public function subtractHours($hours)
    {
        return $this->sub(new DateInterval(sprintf('PT%dH', $hours)));
    }

    public function sub(DateInterval $interval)
    {
        $newDateTime = $this->toDateTime();
        $newDateTime->sub($interval);
        return self::box($newDateTime);
    }

    public function startOfDay()
    {
        $newDateTime = $this->toDateTime();
        $newDateTime->setTime(0, 0, 0);
        return self::box($newDateTime);
    }

    public function endOfDay()
    {
        $newDateTime = $this->toDateTime();
        $newDateTime->setTime(23, 59, 59);
        return self::box($newDateTime);
    }

    public function startOfHour()
    {
        $newDateTime = $this->toDateTime();
        $newDateTime->setTime($newDateTime->format('H'), 0, 0);
        return self::box($newDateTime);
    }

    public function startOfNextHour()
    {
        return $this
            ->add(new DateInterval('PT1H'))
            ->startOfHour();
    }

    public function differenceInSeconds(UTCDateTime $another)
    {
        return $this->sec + $this->usec / 1000000
            - $another->sec - $another->usec / 1000000;
    }

    public function greaterThan(UTCDateTime $another)
    {
        return $this->toDateTime() > $another->toDateTime();
    }

    public function greaterThanOrEqual(UTCDateTime $another)
    {
        return self::sort($this, $another) >= 0;
    }

    public function lessThanOrEqual(UTCDateTime $another)
    {
        return self::sort($this, $another) <= 0;
    }

    public function lessThan(UTCDateTime $another)
    {
        return self::sort($this, $another) < 0;
    }

    public static function sort($a, $b)
    {
        if($a->sec() == $b->sec() && $a->usec() == $b->usec()) {
            return 0;
        }
        if($a->sec() == $b->sec()) {
            return $a->usec() < $b->usec() ? -1 : 1;
        } else {
            return $a->sec() < $b->sec() ? -1 : 1;
        }
    }

    public function toHourlyPrecision()
    {
        return $this->toDateTime()->format('Y-m-d H');
    }

    public function toHour()
    {
        return $this->toDateTime()->format('H');
    }

    public function toYearMonth()
    {
        return $this->toDateTime()->format('Y-m');
    }

    public function toSecondPrecision()
    {
        return $this->toDateTime()->format('Y-m-d H:i:s');
    }

    public function withUsec($usec)
    {
        if ($usec < 0 || $usec > 999999) {
            throw new \InvalidArgumentException(
                "usecs must be within 0 and 999999, got " . var_export($usec, true)
            );
        }

        return new self(
            $this->sec(),
            $usec
        );
    }

    public function startOfMonth()
    {
        return self::box(
            $this->toYearMonth() . '-01'
        );
    }

    public function diff(UTCDateTime $another)
    {
        return $this->toDateTime()->diff($another->toDateTime());
    }

    public function jsonSerialize()
    {
        return $this->toIso8601WithMicroseconds();
    }

    public function __debugInfo()
    {
        return ['ISO' => $this->toIso8601WithMicroseconds()];
    }
}
