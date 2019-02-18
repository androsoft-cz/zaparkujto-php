<?php

namespace App\Model\Facade;

use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\Orm\Reservations\ReservationsRepository;
use App\Model\Orm\Users\UsersRepository;
use Exception;

final class PlaceFacade
{
    /** @var PlacesRepository */
    private $placeRepository;

    /** @var UsersRepository */
    private $userRepository;

    /** @var ReservationsRepository */
    private $reservationsRepository;

    /**
     * @param PlacesRepository $placeRepository
     * @param UsersRepository $userRepository
     * @param ReservationsRepository $reservationsRepository
     */
    public function __construct(PlacesRepository $placeRepository, UsersRepository $userRepository, ReservationsRepository $reservationsRepository)
    {
        $this->placeRepository = $placeRepository;
        $this->userRepository = $userRepository;
        $this->reservationsRepository = $reservationsRepository;
    }

    /**
     * @param int $placeId
     * @return Place
     */
    public function getPlace($placeId)
    {
        return $this->placeRepository->getById($placeId);
    }

    /**
     * @param Place $place
     * @return string
     * @throws Exception
     */
    public function getStringCurrency(Place $place)
    {
        switch ($place->currency) {
            case Place::CURRENCY_CZK:
                return 'CZK';
            case Place::CURRENCY_EUR:
                return 'EUR';
            case Place::CURRENCY_PLN:
                return 'PLN';
        }

        throw new Exception('Unknown currency');
    }

}
