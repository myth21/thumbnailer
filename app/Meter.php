<?php

namespace App;

/**
 * Не удержался чтобы накидать свой велосипед
 */
class Meter
{
    /**
     * @var int
     */
    protected static $startTime;
    protected static $startMemoryUsage;

    private function __construct() {}

    public static function startTime()
    {
        self::$startTime = microtime(true);
    }

    public static function getEndTime(): float
    {
        return microtime(true) - self::$startTime;
    }

    public static function getRoundEndTime(int $precision = 2): float
    {
        return round(microtime(true) - self::$startTime, $precision);
    }

    public static function printRoundEndTime(int $precision = 0)
    {
        print_r(PHP_EOL . self::getRoundEndTime($precision) . ' seconds' . PHP_EOL);
    }

    public static function startMemory()
    {
        self::$startMemoryUsage = memory_get_usage();
    }

    public static function getMemoryUsage(): int
    {
        return round(memory_get_usage() - self::$startMemoryUsage);
    }

    public static function printMemoryUsage(int $precision = 0)
    {
        $memory = self::getMemoryUsage();
        $i = 0;
        while (floor($memory / 1024) > 0) {
            $i++;
            $memory /= 1024;
        }
        $memory = round($memory, $precision);
        $name = ['bytes', 'kb', 'mb', 'gb'];

        print_r(PHP_EOL . $memory . ' ' . ($name[$i] ?? '') . PHP_EOL);
    }

}
