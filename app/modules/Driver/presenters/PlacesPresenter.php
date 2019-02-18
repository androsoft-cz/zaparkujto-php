<?php

namespace App\Modules\Driver;

use App\Core\Latte\Filters;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\SearchPlace;
use App\Model\Orm\UseofPlaces\UseofPlaces;
use App\Model\Search\PlaceFinder;
use App\Model\Search\SearchArgs;
use App\Model\TimeLogic\ITimeAnalyzatorFactory;
use App\Model\WebServices\Locator\PlaceTypeIconLocator;
use App\Modules\Driver\Components\Search\ISearchControlFactory;
use App\Modules\Driver\Components\Search\SearchControl;

class PlacesPresenter extends SecurePresenter
{
    /** @var SearchArgs @inject */
    public $search;

    /** @var ISearchControlFactory @inject */
    public $searchControlFactory;

    /** @var PlaceFinder @inject */
    public $placeFinder;

    /** @var PlaceTypeIconLocator @inject */
    public $placeTypeIconLocator;

    /** @var ITimeAnalyzatorFactory @inject */
    public $timeAnalyzatorFactory;

    // PERSISTENT ARGUMENTS

    /** @var float @persistent */
    public $lat;

    /** @var float @persistent */
    public $lng;

    /** @var string @persistent */
    public $q;

    /** @var int[] @persistent */
    public $types = [];


    /**
     * List of places
     */
    public function renderDefault()
    {
        // Pass search arguments
        $this->search->reserveFrom = time();
        $this->search->q = $this->q;
        $this->search->lat = $this->lat;
        $this->search->lng = $this->lng;
        $this->search->type = !empty($this->types) ? $this->types : UseofPlaces::TYPE_NOT_PROSPECT;

        // Search places
        $places = $this->placeFinder->find($this->search, 20);

        // Create time analyzator (by search arguments)
        $analyzator = $this->timeAnalyzatorFactory->create($this->search);

        $placeItems = [];

        /** @var SearchPlace $place */
        foreach ($places as $place) {
            $free = $analyzator->getFreeEntry($place);
            $park = $analyzator->getParkEntry($place);
            $is_free = ($free->isFreeIn()) ? 'in' : 'now';
            $is_park = ($park->isParkLong()) ? 'long' : 'until';

            $placeItems[] = (object) [
                'id' => $place->id,
                'organization_id' => $place->organization->id,
                'streetName' => $place->streetName,
                'identifier' => $place->identifier,
                'description' => $place->description,
                'lng' => $place->lng,
                'lat' => $place->lat,
                'distance' => isset($place->distance) ? round($place->distance, 1) : NULL,
                'price_per_unit' => $place->matchingUseofPlace->pricePerUnit,
                'price_per_extend' => $place->matchingUseofPlace->pricePerExtend,
                'free_text' => $free->isFreeNow() ? $this->translator->translate('presenters.places.freeNow') : $this->translator->translate('presenters.places.freeNow', ['minutes' => $free->date->format('i')]),
                'free_now' => $free->isFreeNow(),
                'is_free' => $is_free,
                'park_text' => $park->isParkLong() ? '> ' . Filters::hours($park->date, $this->search->datetime) : $this->translator->translate('presenters.places.toTime', ['time' => $park->date->format('G:i')]),
                'is_park' => $is_park,
                'free_from_date' => $free->date->format('c'),
                'free_to_date' => $park->date->format('c'),
                'max_reservation_time' => $place->matchingUseofPlace->maxReservationTime,
                'capacity' => $place->capacity,
                'free_capacity' => $place->freeCapacity,
                'type' => $place->matchingUseofPlace->type,
                'typeIconUrl' => $this->placeTypeIconLocator->getUrl($place->matchingUseofPlace->type),
                'thumbnail' => NULL,
            ];
        }

        $this->template->placeItems = $placeItems;
    }


    /**
     * @return SearchControl
     */
    protected function createComponentSearch()
    {
        $search = $this->searchControlFactory->create();

        $search->onNotFound[] = function () {
            $this->flashMessage('flashmessages.addressNotFound');
            $this->redirect('this');
        };

        $search->onSearch[] = function (array $args) {
            $this->redirect('this', $args);
        };

        return $search;
    }
}
