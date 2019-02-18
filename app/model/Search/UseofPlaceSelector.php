<?php

namespace App\Model\Search;

use App\Model\Orm\Places\Place;
use App\Model\Orm\UseofPlaces\UseofPlaces;
use App\Model\Orm\UseofPlaces\UseofPlacesRepository;
use DateInterval;
use DateTimeImmutable;

class UseofPlaceSelector
{
    /** @var UseofPlacesRepository */
    private $useofPlaceRepository;


    public function __construct(UseofPlacesRepository $useofPlaceRepository)
    {
        $this->useofPlaceRepository = $useofPlaceRepository;
    }


    /**
     * Returns first UseofPlace record of the place with matching "valid time" attributes.
     *
     * @param Place|int $place
     * @param SearchArgs $searchArgs
     * @return UseofPlaces|NULL
     */
    public function getUseofPlace($place, SearchArgs $searchArgs)
    {
        if (is_numeric($place)) {
            $useofPlaces = $this->useofPlaceRepository->findBy(['place' => $place]);
        } else {
            $useofPlaces = $place->useofPlaces;
        }

        if (!$useofPlaces->count()) {
            return NULL;
        }

        $weekDay = (int) $searchArgs->datetime->format('N');
        $hours = (int) $searchArgs->datetime->format('H');
        $minutes = (int) $searchArgs->datetime->format('i');
        $dateInterval = new DateInterval("PT{$hours}H{$minutes}M");

        foreach ($useofPlaces as $key => $useofPlace) {
            if ($searchArgs->type !== NULL) {
                if (is_array($searchArgs->type)) {
                    if (!in_array($useofPlace->type, $searchArgs->type)) {
                        continue;
                    }
                } else if ($searchArgs->type == UseofPlaces::TYPE_NOT_PROSPECT) {
                    if ($useofPlace->type == UseofPlaces::TYPE_PROSPECT) {
                        continue;
                    }
                } elseif ($useofPlace->type != $searchArgs->type) {
                    continue;
                }
            }

            if (!$useofPlace->isValidDay($weekDay)) {
                continue;
            }

            $isInInterval = $this->isInInterval($dateInterval, $useofPlace->validFrom, $useofPlace->validTo);

            if ($useofPlace->validHoursInverted) {
                if ($isInInterval) {
                    continue;
                }
            } elseif (!$isInInterval) {
                continue;
            }

            return $useofPlace;
        }

        return NULL;
    }


    /**
     * @param DateInterval $dateInterval
     * @param DateInterval $fromDateInterval
     * @param DateInterval $toDateInterval
     * @return bool
     */
    private function isInInterval(DateInterval $dateInterval, DateInterval $fromDateInterval, DateInterval $toDateInterval)
    {
        $immutableDateTime = new DateTimeImmutable();
        $timestamp = $immutableDateTime->add($dateInterval)->getTimestamp();

        $immutableDateTime = new DateTimeImmutable();
        $fromTimestamp = $immutableDateTime->add($fromDateInterval)->getTimestamp();

        $immutableDateTime = new DateTimeImmutable();
        $toTimestamp = $immutableDateTime->add($toDateInterval)->getTimestamp();

        return $fromTimestamp <= $timestamp && $timestamp < $toTimestamp;
    }

}
