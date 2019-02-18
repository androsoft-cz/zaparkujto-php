<?php

namespace App\Model\Search;

use App\Model\Orm\Places\PlacesRepository;
use App\Model\Orm\Places\SearchPlace;
use Nextras\Orm\Collection\ArrayCollection;
use Nextras\Orm\Collection\ICollection;

class PlaceFinder
{
    /** @var PlacesRepository */
    private $placesRepository;

    /** @var FreeCapacityCounter */
    private $freeCapacityCounter;

    /** @var UseofPlaceSelector */
    private $useofPlaceSelector;


    /**
     * @param PlacesRepository $placesRepository
     * @param FreeCapacityCounter $freeCapacityCounter
     * @param UseofPlaceSelector $useofPlaceSelector
     */
    public function __construct(
        PlacesRepository $placesRepository,
        FreeCapacityCounter $freeCapacityCounter,
        UseofPlaceSelector $useofPlaceSelector
    )
    {
        $this->placesRepository = $placesRepository;
        $this->freeCapacityCounter = $freeCapacityCounter;
        $this->useofPlaceSelector = $useofPlaceSelector;
    }


    /**
     * @param SearchArgs $searchArgs
     * @param int|NULL $limit
     * @return SearchPlace[]|ArrayCollection
     */
    public function find(SearchArgs $searchArgs, $limit = NULL)
    {
        /** @var SearchPlace[]|ICollection $places */
        $places = $this->placesRepository->search($searchArgs)->orderBy('distance');

        $_places = [];

        foreach ($places as $place) {
            $useofPlace = $this->useofPlaceSelector->getUseofPlace($place, $searchArgs);

            if (!$useofPlace) {
                continue;
            }

            $freeCapacity = $this->freeCapacityCounter->count($place, $useofPlace, $searchArgs);

            if ($freeCapacity) {
                $_places[] = $place;
                $place->freeCapacity = $freeCapacity;
                $place->matchingUseofPlace = $useofPlace;
            }

            if ($limit !== NULL && count($_places) >= $limit) {
                break;
            }
        }

        return new ArrayCollection($_places, $this->placesRepository);
    }

}
