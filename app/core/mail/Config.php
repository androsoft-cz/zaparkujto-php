<?php

namespace App\Core\Mail;

use Nette\Object;

/**
 * Mail Config
 */
class Config extends Object
{

    /** @var string */
    private $dir;

    /** @var string */
    private $from;

    /** @var string */
    private $fromName;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

}
