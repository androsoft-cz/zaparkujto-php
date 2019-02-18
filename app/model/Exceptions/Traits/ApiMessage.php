<?php

namespace App\Model\Exceptions\Traits;

trait ApiMessage
{

    /** @var string|NULL */
    public $apiMessageKey;

    /** @var mixed[]|NULL */
    public $apiMessageParams;

    /**
     * @param $apiMessageKey
     * @param $apiMessageParams
     * @return $this
     */
    public function setApiMessage($apiMessageKey, array $apiMessageParams = NULL)
    {
        $this->apiMessageKey = $apiMessageKey;
        $this->apiMessageParams = $apiMessageParams;

        return $this;
    }


    /**
     * @return bool
     */
    public function hasApiMessage()
    {
        return $this->apiMessageKey !== NULL;
    }


    /**
     * @return string|NULL
     */
    public function getApiMessageKey()
    {
        return $this->apiMessageKey;
    }


    /**
     * @return mixed[]|NULL
     */
    public function getApiMessageParams()
    {
        return $this->apiMessageParams;
    }

}
