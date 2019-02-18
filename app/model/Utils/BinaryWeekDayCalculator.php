<?php

namespace App\Model\Utils;

use Exception;

class BinaryWeekDayCalculator
{
    const BINARY_MONDAY = 1;
    const BINARY_TUESDAY = 2;
    const BINARY_WEDNESDAY = 4;
    const BINARY_THURSDAY = 8;
    const BINARY_FRIDAY = 16;
    const BINARY_SATURDAY = 32;
    const BINARY_SUNDAY = 64;


    /**
     * @param int $currentBinaryDays  0-127
     * @param int $day  1-7, 1 = monday
     * @param bool $value
     * @return int
     * @throws Exception
     */
    public static function calculateBinary($currentBinaryDays, $day, $value)
    {
        $binaryDay = self::dayToBinary($day);
        $invertedBinaryDay = 127 - $binaryDay;

        return $value ? $currentBinaryDays | $binaryDay : $currentBinaryDays & $invertedBinaryDay;
    }


    /**
     * @param int $binaryDays  0-127
     * @param int $day  1-7, 1 = monday
     * @return bool
     * @throws Exception
     */
    public static function getDayValue($binaryDays, $day)
    {
        return (bool) ($binaryDays & self::dayToBinary($day));
    }


    /**
     * @param int $day  1-7, 1 = monday
     * @return int
     * @throws Exception
     */
    private static function dayToBinary($day)
    {
        switch ($day) {
            case 1:
                return self::BINARY_MONDAY;
            case 2:
                return self::BINARY_TUESDAY;
            case 3:
                return self::BINARY_WEDNESDAY;
            case 4:
                return self::BINARY_THURSDAY;
            case 5:
                return self::BINARY_FRIDAY;
            case 6:
                return self::BINARY_SATURDAY;
            case 7:
                return self::BINARY_SUNDAY;
        }

        throw new Exception("Day '$day' is out of range [1-7]");
    }

}
