<?php

namespace App\Model\Search;

use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\Orm\Positions\PositionsRepository;
use App\Model\Orm\Reservations\ReservationsRepository;
use App\Model\Orm\UseofPlaces\UseofPlaces;
use App\Model\Orm\Users\UsersRepository;

class FreeCapacityCounter
{
    /** @var ReservationsRepository */
    private $reservationsRepository;

    /** @var UsersRepository */
    private $usersRepository;

    /** @var  PositionsRepository */
    private $positionsRepository;


    public function __construct(ReservationsRepository $reservationsRepository, UsersRepository $usersRepository, PositionsRepository $positionsRepository)
    {
        $this->reservationsRepository = $reservationsRepository;
        $this->usersRepository = $usersRepository;
        $this->positionsRepository = $positionsRepository;
    }


    /**
     * @param Place $place
     * @param UseofPlaces $useofPlace
     * @param SearchArgs $searchArgs
     * @return int
     */
    public function count(Place $place, UseofPlaces $useofPlace, SearchArgs $searchArgs)
    {
        if ($place->countFreePlaces) {
            $freeCapacity = $this->positionsRepository->countFreeSenzors($place->id);
        } else {
            $freeCapacity = $place->capacity;

            $reservationCount = $this->reservationsRepository->findByTime($place, $searchArgs->datetime)->count();
            $freeCapacity -= $reservationCount;

            if ($freeCapacity <= 0) {
                return 0;
            }

            if ($useofPlace->type === UseofPlaces::TYPE_SHAREABLE) {
                $shareableReservationCount = $this->usersRepository->findReservedShareable($place->id, $searchArgs->reserveFrom)->count();
                $freeCapacity -= $shareableReservationCount;

                if ($freeCapacity <= 0) {
                    return 0;
                }
            }
        }

        return $freeCapacity;
    }

}
