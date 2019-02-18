<?php

namespace App\Model\TimeLogic\Rules;

use App\Core\Config\Config;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Search\SearchArgs;
use Nette\Utils\DateTime;

abstract class BaseRules
{

    /** @var Config */
    protected $config;

    /** @var SearchArgs */
    protected $search;

    /** @var DateTime */
    protected $date;

    /**
     * @param Config $config
     * @param SearchArgs $search
     */
    public function __construct(Config $config, SearchArgs $search)
    {
        $this->config = $config;
        $this->search = $search;
    }

    /**
     * @return DateTime
     */
    protected function getSearchDate()
    {
        if (!$this->date) {
            $this->date = DateTime::from($this->search->reserveFrom);
        }

        return $this->date;
    }

    /**
     * @param Place $place
     * @return Reservation|NULL
     */
    protected function getPreviousReservation(Place $place)
    {
        return $place->getPreviousReservation($this->getSearchDate());
    }

    /**
     * @param Place $place
     * @return Reservation|NULL
     */
    protected function getCurrentReservation(Place $place)
    {
        return $place->getCurrentReservation($this->getSearchDate());
    }

    /**
     * @param Place $place
     * @return Reservation|NULL
     */
    protected function getNextReservation(Place $place)
    {
        return $place->getNextReservation($this->getSearchDate());
    }

}
