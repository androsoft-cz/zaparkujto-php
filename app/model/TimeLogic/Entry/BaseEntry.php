<?php

namespace App\Model\TimeLogic;

use Nette\Object;
use Nette\Utils\DateTime;

/**
 * @property-read int $type
 * @property-read DateTime $date
 */
abstract class BaseEntry extends Object
{

    /** @var int */
    protected $type;

    /** @var DateTime */
    protected $date;

    /**
     * @param int $type
     * @param DateTime $date
     */
    public function __construct($type, $date)
    {
        $this->type = $type;
        $this->date = DateTime::from($date);
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

}
