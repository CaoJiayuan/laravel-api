<?php

namespace CaoJiayuan\LaravelApi\Utils;


use Carbon\Carbon;
use Illuminate\Support\Str;

class CarbonHelper
{
    const RANGE_MINUTE = 'minute';

    protected static $carbonNow = null;

    public static function getTimeRangeMonth($timestamp = false)
    {
        return static::getTimeRange('month', $timestamp);
    }

    /**
     * @param $range
     * year
     * quarter
     * month
     * week
     * day
     * @param bool $timestamp
     * @return array
     */
    public static function getTimeRange($range, $timestamp = false)
    {
        $now = static::getCarbonNow();

        if (!static::isValidRange($range)) {
            list($start, $end) = static::{"getTimeRangeCustom{$range}"}($timestamp);
        } else {
            $range = ucfirst(Str::camel($range));
            $startMethod = "startOf{$range}";
            $start = (clone $now)->{$startMethod}();
            $end = $now->{"endOf{$range}"}();

            if ($timestamp) {
                $start = $start->timestamp;
                $end = $end->timestamp;
            }
        }


        return [$start, $end];
    }

    /**
     * @return Carbon
     */
    public static function getCarbonNow()
    {
        if (!static::$carbonNow) {
            static::$carbonNow = Carbon::now();
        }
        return static::$carbonNow;
    }

    public static function setCarbonNow(Carbon $carbon)
    {
        static::$carbonNow = $carbon;

        return new static();
    }

    public static function getTimeRangeWeek($timestamp = false)
    {
        return static::getTimeRange('week', $timestamp);
    }

    public static function getTimeRangeYear($timestamp = false)
    {
        return static::getTimeRange('year', $timestamp);
    }

    public static function getRangeTimes($range = 'year', $step = 'month', $untilNow = false, $collect = false)
    {
        if (is_numeric($range)) {
            $now = static::getCarbonNow();
            $st = ucfirst($step);
            list($start, $end) = [
                $untilNow ? (clone $now)->{"add{$step}"}(-$range) : (clone $now)->{"startOf{$st}"}()->{"add{$step}"}(-$range),
                $untilNow ? (clone $now) : (clone $now)->{"endOf{$st}"}(),
            ];
        } elseif (is_array($range) && count($range) == 2) {
            list($start, $end) = array_map(function ($item) {
                return new Carbon($item);
            }, $range);
        } else {
            list($start, $end) = $untilNow ? static::getTimeRangeNow($range, false) : static::getTimeRange($range, false);
        }

        $method = 'add' . ucfirst($step);
        $times = [];
        for (; $start < $end; $start->$method()) {
            $times[] = [
                (clone $start),
                (clone $start)->{"add{$step}"}()
            ];
        }

        return $collect ? collect($times) : $times;
    }

    public static function getTimeRangeNow($range, $timestamp = false)
    {
        $now = static::getCarbonNow();

        if (!static::isValidRange($range)) {
            list($start, $end) = static::{"getTimeRangeNowCustom{$range}"}($timestamp);
        } else {
            $range = ucfirst(strtolower($range));
            $method = "add{$range}";
            $start = $now;
            $end = (clone $now)->{$method}(-1);

            if ($timestamp) {
                $start = $start->timestamp;
                $end = $end->timestamp;
            }
        }


        return [$end, $start];
    }

    protected static function isValidRange($range)
    {
        return in_array($range, [
            'day',
            'month',
            'year',
            'quarter',
            'week',
            'minute',
            'hour',
            'decade'
        ]);
    }
}
