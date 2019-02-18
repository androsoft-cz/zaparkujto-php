<?php

namespace App\Model\Search;

use Nette\Object;
use Nette\Utils\DateTime;

/**
 * @property float $lat
 * @property float $lng
 * @property int $reserveFrom
 * @property int $parkTime
 * @property DateTime $datetime
 * @property string $q
 * @property int|int[]|NULL $type  App\Model\Orm\UseofPlaces\UseofPlaces::TYPE_*|UseofPlaces::TYPE_*[]
 */
final class SearchArgs extends Object
{
    /** @var float */
    private $lat;

    /** @var float */
    private $lng;

    /** @var int */
    private $reserveFrom;

    /** @var int|NULL  minutes */
    private $parkTime;

    /** @var DateTime */
    private $datetime;

    /** @var string */
    private $q;

    /** @var int|int[]|NULL */
    private $type;


    /**
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param float $lat
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    }

    /**
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @param float $lng
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    /**
     * @return int
     */
    public function getReserveFrom()
    {
        return $this->reserveFrom;
    }

    /**
     * @return DateTime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @param int $reserveFrom
     */
    public function setReserveFrom($reserveFrom)
    {
        $this->reserveFrom = $reserveFrom;
        $this->datetime = DateTime::from($reserveFrom);
    }

    /**
     * @return int|NULL
     */
    public function getParkTime()
    {
        return $this->parkTime;
    }

    /**
     * @param int|NULL $parkTime
     */
    public function setParkTime($parkTime)
    {
        $this->parkTime = $parkTime;
    }

    /**
     * @return string
     */
    public function getQ()
    {
        return $this->q;
    }

    /**
     * @param string $q
     */
    public function setQ($q)
    {
        $this->q = $q;
    }

    /**
     * @return int|int[]|NULL
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int|int[]|NULL $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

}
