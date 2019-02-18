<?php

namespace App\Model\Exceptions;

use App\Model\Exceptions\Traits\ApiMessage;
use App\Model\Exceptions\Traits\IApiMessageException;

class LogicException extends \LogicException implements IApiMessageException
{

    use ApiMessage;

}
