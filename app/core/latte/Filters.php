<?php

namespace App\Core\Latte;

use Nette\Utils\DateTime;
use Nette\Utils\Json;

final class Filters
{

    /**
     * @param string $presenter
     * @return string
     */
    public static function wrapper($presenter)
    {
        return str_replace(':', '-', strtolower($presenter));
    }

    /**
     * @param mixed $km
     * @return string
     */
    public static function distance($km)
    {
        if ($km < 1) {
            return number_format($km * 1000, 0, '.', ' ') . 'm';
        } else {
            return number_format($km, 2, '.', ' ') . 'km';
        }
    }

    /**
     * @param mixed $time
     * @return string
     */
    public static function time($time)
    {
        if (!$time) return '-';
        $dt = DateTime::from($time);

        return $dt->format('H:i');
    }

    /**
     * @param mixed $time
     * @param DateTime $diff
     * @return float
     */
    public static function seconds($time, $diff = NULL)
    {
        if (!$time instanceof DateTime) {
            $time = DateTime::from($time);
        }

        if (!$diff || !$diff instanceof DateTime) {
            $diff = DateTime::from($diff ?: time());
        }

        $interval = $time->diff($diff);

        return $interval->format('%s') . 's';
    }

    /**
     * @param mixed $time
     * @param DateTime $diff
     * @return float
     */
    public static function minutes($time, $diff = NULL)
    {
        if (!$time instanceof DateTime) {
            $time = DateTime::from($time);
        }

        if (!$diff || !$diff instanceof DateTime) {
            $diff = DateTime::from($diff ?: time());
        }

        $interval = $time->diff($diff);
        $minutes = $interval->format('%i');

        if ($minutes <= 0) {
            return self::seconds($time, $diff);
        } else {
            return $minutes . 'min';
        }
    }

    /**
     * @param mixed $time
     * @param DateTime $diff
     * @return float
     */
    public static function hours($time, $diff = NULL)
    {
        if (!$time instanceof DateTime) {
            $time = DateTime::from($time);
        }

        if (!$diff || !$diff instanceof DateTime) {
            $diff = DateTime::from($diff ?: time());
        }

        $interval = $time->diff($diff);
        $base = $interval->days * 24;
        $hours = $base + $interval->format('%h');

        if ($hours <= 0) {
            return self::minutes($time, $diff);
        } else {
            return $hours . 'h';
        }
    }

    /**
     * @param mixed $from
     * @param mixed $to
     * @return mixed
     */
    public static function duration($from, $to)
    {
        $from = DateTime::from($from);
        $to = DateTime::from($to);
        $udiff = $to->format('U') - $from->format('U');

        $hours = floor($udiff / (60 * 60));
        $udiff -= $hours * 60 * 60;
        $minutes = floor($udiff > 0 ? $udiff / 60 : 0);

        if ($minutes > 0) {
            return sprintf('%sh %smin', $hours, $minutes);
        } else {
            return sprintf('%sh', $hours);
        }

    }

    /**
     * @param mixed $data
     * @return string
     */
    public static function json($data)
    {
        return Json::encode($data);
    }
}
