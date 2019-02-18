<?php

namespace App\Model\WebServices\Google\Place;

use App\Model\WebServices\Google\Geocoding\BaseApiClient;

final class AutocompleteClient extends BaseApiClient
{

    // Google MAPS geocode api URL
    const GEOCODE_URL = 'https://maps.googleapis.com/maps/api/place/autocomplete/json';


    /**
     * @param array $params
     * @return mixed
     */
    public function autocomplete($params = [])
    {
        return $this->call($this->createUrl(self::GEOCODE_URL, $params));
    }

}
