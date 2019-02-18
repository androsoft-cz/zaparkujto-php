<?php

namespace App\Model\TimeLogic;

final class ParkEntry extends BaseEntry
{

    // Park types
    const PARK_UNTIL = 1;
    const PARK_LONG = 2;

    /**
     * @return bool
     */
    public function isParkUntil()
    {
        return $this->type == self::PARK_UNTIL;
    }

    /**
     * @return bool
     */
    public function isParkLong()
    {
        return $this->type == self::PARK_LONG;
    }

}
