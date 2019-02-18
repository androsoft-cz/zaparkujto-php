<?php

namespace App\Model\WebServices\Google\Geocoding;

use App\Model\WebServices\Google\HttpClient;
use Nette\Http\Url;

abstract class BaseApiClient
{

    /** @var string */
    private $key;

    /** @var HttpClient */
    private $http;

    /**
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key = $key;
        $this->http = new HttpClient();
    }

    /**
     * HELPERS *****************************************************************
     * *************************************************************************
     */

    /**
     * Process request
     *
     * @param Url $url
     * @return array
     */
    protected function call(Url $url)
    {
        // Append key
        $url->appendQuery(['key' => $this->key]);

        // Make request
        return $this->http->makeRequest($url);
    }

    /**
     * @param string $url
     * @param array $params
     * @return Url
     */
    protected function createUrl($url, array $params)
    {
        // Create base url
        $url = new Url($url);

        // Append params
        $url->appendQuery($params);

        return $url;
    }

}
