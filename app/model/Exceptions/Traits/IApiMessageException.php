<?php

namespace App\Model\Exceptions\Traits;

interface IApiMessageException
{
    /**
     * @return bool
     */
    public function hasApiMessage();


    /**
     * @return string|NULL
     */
    public function getApiMessageKey();


    /**
     * @return mixed[]|NULL
     */
    public function getApiMessageParams();

}
