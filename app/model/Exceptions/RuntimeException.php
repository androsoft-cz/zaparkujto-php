<?php

namespace App\Model\Exceptions;

use App\Model\Exceptions\Traits\ApiMessage;
use App\Model\Exceptions\Traits\IApiMessageException;

class RuntimeException extends \RuntimeException implements IApiMessageException
{

    use ApiMessage;

}
