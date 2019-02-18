<?php

namespace App\Model\WebServices\Google;

use Nette\Utils\Json;

final class HttpClient
{

    /**
     * Make request via cURL
     *
     * @param string $url
     * @return array
     */
    public function makeRequest($url)
    {
        // Get cURL resource
        $curl = curl_init();

        // Set some options
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYPEER => FALSE,
        ]);

        // Send the request & save response to $resp
        $resp = curl_exec($curl);

        // Close request to clear up some resources
        curl_close($curl);

        if (!$resp) {
            return (object) ['status' => 'ERROR'];
        } else {
            return Json::decode($resp);
        }
    }

}
