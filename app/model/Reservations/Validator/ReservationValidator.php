<?php

namespace App\Model\Reservations;

use App\Core\Config\Config;
use App\Model\Exceptions\Runtime\ReserveValidationException;
use App\Model\Orm\Places\Place;
use App\Model\Orm\UseofPlaces\UseofPlaces;
use App\Model\Orm\Users\User;
use App\Model\Search\FreeCapacityCounter;
use App\Model\Search\SearchArgs;
use App\Model\Search\UseofPlaceSelector;
use App\Model\TimeLogic\ITimeAnalyzatorFactory;
use Kdyby\Translation\Translator;

class ReservationValidator
{
    /** @var Translator */
    private $translator;

    /** @var Config */
    private $config;

    /** @var FreeCapacityCounter */
    private $freeCapacityCounter;

    /** @var UseofPlaceSelector */
    private $useofPlaceSelector;


    /**
     * @param Translator $translator
     * @param Config $config
     * @param ITimeAnalyzatorFactory $timeAnalyzatorFactory
     * @param FreeCapacityCounter $freeCapacityCounter
     * @param UseofPlaceSelector $useofPlaceSelector
     */
    public function __construct(
        Translator $translator,
        Config $config,
        ITimeAnalyzatorFactory $timeAnalyzatorFactory,
        FreeCapacityCounter $freeCapacityCounter,
        UseofPlaceSelector $useofPlaceSelector
    ) {
        $this->translator = $translator;
        $this->config = $config;
        $this->freeCapacityCounter = $freeCapacityCounter;
        $this->useofPlaceSelector = $useofPlaceSelector;
    }


    /**
     * @param Place     $place
     * @param int       $reserveFrom
     * @param int       $parkTime minutes
     * @param User|NULL $user
     * @return UseofPlaces|NULL
     * @throws ReserveValidationException
     */
    public function validateReservation(Place $place, int $reserveFrom, int $parkTime, User $user = NULL): ?UseofPlaces
    {
        $search = new SearchArgs();
        $search->reserveFrom = $reserveFrom;
        $search->parkTime = $parkTime;

        // Is minimal range???
        $minimalDiff = $this->config->expand('diff.minimal');

        if ($minimalDiff > $parkTime) {
            $params = ['minutes' => $minimalDiff];
            throw (new ReserveValidationException($this->translator->translate('rpmApi.minReservationTime', $params)))
                ->setApiMessage('minReservationTime', $params);
        }

        $placeUsage = $this->useofPlaceSelector->getUseofPlace($place, $search);

        if (!$placeUsage) {
            throw (new ReserveValidationException($this->translator->translate('rpmApi.placeClosed')))
                ->setApiMessage('placeClosed');
        }

        if ($placeUsage->type === UseofPlaces::TYPE_HANDICAPPED && $user !== NULL && !$user->handicapped) {
            throw (new ReserveValidationException($this->translator->translate('rpmApi.placeOnlyForHandicapped')))
                ->setApiMessage('placeOnlyForHandicapped');
        }

        if ($placeUsage->type === UseofPlaces::TYPE_SHAREABLE && !$this->freeCapacityCounter->count($place, $placeUsage, $search)) {
            throw (new ReserveValidationException($this->translator->translate('rpmApi.placeFull')))
                ->setApiMessage('placeFull');
        }

        return $placeUsage;
    }
}
