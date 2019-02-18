<?php

namespace App\Modules\Driver\Components\Geocode;

use App\Core\UI\BaseControl;
use App\Model\Search\SearchService;

final class AutoGeocodeControl extends BaseControl
{

    /** @var array */
    public $onError = [];

    /** @var array */
    public $onGeocode = [];

    /** @var SearchService */
    private $searchService;

    /**
     * @param SearchService $searchService
     */
    public function __construct(SearchService $searchService)
    {
        parent::__construct();
        $this->searchService = $searchService;
    }

    /**
     * Fill coords by user location
     *
     * @param float $lat
     * @param float $lng
     */
    public function handleAuto($lat, $lng)
    {
        $result = $this->searchService->geocodeByCoords($lat, $lng);

        if (!$result) {
            $this->onError();
        }

        $addresses = explode(',', $result->formatted_address);

        $this->onGeocode(
            $addresses[0],
            $result->geometry->location->lng,
            $result->geometry->location->lat
        );
    }

}
