<?php

namespace App\Model\Exceptions\Runtime;

use App\Model\Exceptions\RuntimeException;
use Exception;

class InvalidReservationStateException extends RuntimeException
{

    /** @var int */
    public $state;

    /**
     * @param int $state
     * @param int $code
     * @param Exception|NULL $previous
     */
    public function __construct($state, $code = 0, Exception $previous = NULL)
    {
        parent::__construct(NULL, $code, $previous);
        $this->state = $state;
    }

}
