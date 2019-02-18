<?php

namespace App\Model\Exceptions\Runtime\Reservation;

use App\Model\Exceptions\RuntimeException;

class TokenException extends RuntimeException
{

    // Token codes
    const NOT_FOUND = 1;
    const EXPIRED = 2;
    const APPLIED = 3;

}
