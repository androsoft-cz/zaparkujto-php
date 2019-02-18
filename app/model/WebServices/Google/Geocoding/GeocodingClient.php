<?php

namespace App\Model\WebServices\Google\Geocoding;

final class GeocodingClient extends BaseApiClient
{

    // Google MAPS geocode api URL
    const GEOCODE_URL = 'https://maps.googleapis.com/maps/api/geocode/json';


    /**
     * @param array $params
     * @return mixed
     */
    public function geocode($params = [])
    {
        return $this->call($this->createUrl(self::GEOCODE_URL, $params));
    }

}
