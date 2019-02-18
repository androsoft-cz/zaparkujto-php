<?php

namespace App\Core\Utils;

use Nette\Utils\DateTime;

final class Dates
{

    /**
     * @param mixed $d1
     * @param mixed $d2
     * @return bool
     */
    public static function equal($d1, $d2)
    {
        $datetime1 = DateTime::from($d1);
        $datetime2 = DateTime::from($d2);

        return $datetime1 == $datetime2;
    }

}
