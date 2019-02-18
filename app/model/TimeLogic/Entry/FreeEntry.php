<?php

namespace App\Model\TimeLogic;

final class FreeEntry extends BaseEntry
{

    // Free types
    const FREE_NOW = 1;
    const FREE_IN = 2;

    /**
     * @return bool
     */
    public function isFreeNow()
    {
        return $this->type == self::FREE_NOW;
    }

    /**
     * @return bool
     */
    public function isFreeIn()
    {
        return $this->type == self::FREE_IN;
    }

}
