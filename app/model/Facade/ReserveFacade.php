<?php

namespace App\Model\Facade;

use App\Core\Config\Config;
use App\Model\Exceptions\Runtime\ReserveException;
use App\Model\Exceptions\Runtime\ReserveValidationException;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\Reservations\ReservationsRepository;
use App\Model\Orm\ReservationTokens\ReservationToken;
use App\Model\Orm\ReservationTokens\ReservationTokensRepository;
use App\Model\Orm\UseofPlaces\UseofPlaces;
use App\Model\Search\SearchArgs;
use App\Model\TimeLogic\FreeEntry;
use App\Model\TimeLogic\ITimeAnalyzatorFactory;
use Nette\Utils\DateTime;

final class ReserveFacade
{

    /** @var Config */
    private $config;

    /** @var \Kdyby\Translation\Translator */
    private $translator;

    /** @var PlacesRepository */
    private $placesRepository;

    /** @var ReservationsRepository */
    private $reservationsRepository;

    /** @var ReservationTokensRepository */
    private $reservationTokensRepository;

    /** @var ITimeAnalyzatorFactory */
    private $timeAnalyzatorFactory;


    /**
     * @param Config $config
     * @param \Kdyby\Translation\Translator
     * @param PlacesRepository $placesRepository
     * @param ReservationsRepository $reservationsRepository
     * @param ReservationTokensRepository $reservationTokensRepository
     * @param ITimeAnalyzatorFactory $timeAnalyzatorFactory
     */
    public function __construct(
        Config $config,
        \Kdyby\Translation\Translator $translator,
        PlacesRepository $placesRepository,
        ReservationsRepository $reservationsRepository,
        ReservationTokensRepository $reservationTokensRepository,
        ITimeAnalyzatorFactory $timeAnalyzatorFactory
    )
    {
        $this->config = $config;
        $this->translator = $translator;
        $this->placesRepository = $placesRepository;
        $this->reservationsRepository = $reservationsRepository;
        $this->reservationTokensRepository = $reservationTokensRepository;
        $this->timeAnalyzatorFactory = $timeAnalyzatorFactory;
    }

    /**
     * @param Place $place
     * @param UseofPlaces $useofPlace
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     * @param $rz
     * @return Reservation
     */
    public function createReservation(Place $place, UseofPlaces $useofPlace, DateTime $dateFrom, DateTime $dateTo, $rz, $price = NULL)
    {
        // Create reservation ====================

        $reservation = new Reservation();
        $reservation->place = $place;
        $reservation->useofPlaces = $useofPlace;
        $reservation->rz = $rz;

        // Parse dates
        $reservation->from = $dateFrom;
        $reservation->to = $dateTo;

        // Validate dates
        if ($reservation->from > $reservation->to) {
            throw new ReserveException($this->translator->translate('misc.reserve.badDate'));
        }

        // Calculate price
        if ($price == NULL) {
            $reservation->price = $this->calculateReservationPrice($reservation->from, $reservation->to, $useofPlace);
        } else {
            $reservation->price = $price;
        }

        // Create tokens
        $minutes = $this->config->expand('reservation.extend.extend_min_before');
        if ($reservation->from > DateTime::from("+ $minutes minutes")) {
            // Extends reservation is available only for long term reservations
            // 24 hours ahead
            $this->createExtendToken($reservation);
        }

        $this->createReleaseToken($reservation);

        return $reservation;
    }

    /**
     * @param int $id
     * @return Place
     */
    public function getPlace($id)
    {
        return $this->placesRepository->getById($id);
    }

    /**
     * @param Reservation $reservation
     * @return ReservationToken
     */
    public function createReleaseToken(Reservation $reservation)
    {
        $token = $this->reservationTokensRepository->createUniqueToken();
        $token->type = $token::TYPE_RELEASE;
        $reservation->tokens->add($token);

        return $token;
    }

    /**
     * @param Reservation $reservation
     * @return ReservationToken
     */
    public function createExtendToken(Reservation $reservation)
    {
        $token = $this->reservationTokensRepository->createUniqueToken();
        $token->type = $token::TYPE_EXTEND;
        $reservation->tokens->add($token);

        return $token;
    }

    /**
     * Returns given time $fromFrom or time when current reservation ends
     *
     * @param Place $place
     * @param DateTime $timeFrom
     * @return DateTime
     */
    public function getFormTimeFrom(Place $place, DateTime $timeFrom)
    {
        $search = new SearchArgs();
        $search->reserveFrom = $timeFrom->format('U');
        $analyzator = $this->timeAnalyzatorFactory->create($search);

        $free = $analyzator->getFreeEntry($place);

        return $free->date;
    }

    /**
     * Returns given time $timeTo or time when current reservation ends
     *
     * @param Place $place
     * @param DateTime $timeTo
     * @return DateTime
     */
    public function getFormTimeTo(Place $place, DateTime $timeFrom, DateTime $timeTo)
    {
        $search = new SearchArgs();
        $search->reserveFrom = $timeFrom->format('U');
        $analyzator = $this->timeAnalyzatorFactory->create($search);

        $park = $analyzator->getParkEntry($place);
        if ($park->date < $timeTo) {
            return $park->date;
        } else {
            return $timeTo;
        }
    }

    /**
     * @param Place $place
     * @param DateTime $timeFrom
     * @return bool
     */
    public function isPlaceShowable(Place $place, DateTime $timeFrom)
    {
        // Create analyzator
        $search = new SearchArgs();
        $search->reserveFrom = $timeFrom->format('U');
        $analyzator = $this->timeAnalyzatorFactory->create($search);

        return $analyzator->isShowable($place);
    }

    /**
     * @param Place $place
     * @param DateTime $timeFrom
     * @param DateTime $timeTo
     * @throws ReserveValidationException
     */
    public function validateReservation(Place $place, DateTime $timeFrom, DateTime $timeTo)
    {
        // Create analyzator
        $search = new SearchArgs();
        $search->reserveFrom = $timeFrom->format('U');
        $analyzator = $this->timeAnalyzatorFactory->create($search);

        // Is minimal range???
        $minimalDiff = $this->config->expand('diff.minimal');
        if ($timeFrom->modifyClone("+ $minimalDiff minutes") > $timeTo) {
            throw new ReserveValidationException($this->translator->translate('misc.reserve.range', ['diff' => $minimalDiff]));
        }

        // Showable???
        if (!$analyzator->isShowable($place)) {
            throw new ReserveValidationException($this->translator->translate('misc.reserve.full'));
        }

        // Free now???
        $free = $analyzator->getFreeEntry($place);
        if ($free->type != FreeEntry::FREE_NOW) {
            throw new ReserveValidationException($this->translator->translate('misc.reserve.freeFrom', ['from' => $free->date->format('H:i')]));
        }

        // Park at least until timeTo???
        $park = $analyzator->getParkEntry($place);
        if ($park->date < $timeTo) {
            throw new ReserveValidationException($this->translator->translate('misc.reserve.freeTo', ['to' => $park->date->format('H:i')]));
        }
    }


    public function calculateReservationPrice(DateTime $from, DateTime $to, UseofPlaces $placeUsages): float
    {
        if ($placeUsages->isForFree()) {
            return 0;
        }

        $diff = $from->diff($to);
        $ratio = $diff->h + ($diff->i / 60);
        return ceil($placeUsages->pricePerUnit * $ratio);
    }
}
