<?php

namespace App\Model\Search;

use App\Model\OrganizationRequest;
use App\Model\WebServices\Google\Geocoding\GeocodingClient;
use App\Model\WebServices\Google\Place\AutocompleteClient;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Utils\Strings;

final class SearchService
{

    /** @var AutocompleteClient */
    private $autocomplete;

    /** @var GeocodingClient */
    private $geocoding;

    /** @var OrganizationRequest */
    private $organizationRequest;

    /** @var Cache */
    private $geocache;

    /**
     * @param OrganizationRequest $organizationRequest
     * @param AutocompleteClient $autocomplete
     * @param GeocodingClient $geocoding
     * @param IStorage $storage
     */
    public function __construct(
        OrganizationRequest $organizationRequest,
        AutocompleteClient $autocomplete,
        GeocodingClient $geocoding,
        IStorage $storage
    )
    {
        $this->organizationRequest = $organizationRequest;
        $this->autocomplete = $autocomplete;
        $this->geocoding = $geocoding;
        $this->geocache = new Cache($storage, 'GeoCache');
    }

    /**
     * @param string $q
     * @return array
     */
    public function suggest($q)
    {
        // Get organization city
        $organization = $this->organizationRequest->getOrganization();
        if ($organization) {
            $city = $organization->name;

            // Prepend city
            if (!Strings::contains($q, $city)) {
                $q = "$city, $q";
            }
        }

        // Ask google
        $result = $this->autocomplete->autocomplete([
            'input' => $q,
            'components' => 'country:CZ',
            'language' => 'cs',
            //'types' => 'address',
        ]);

        if ($result->status !== 'OK') {
            return [];
        } else {
            $output = [];

            foreach ($result->predictions as $p) {
                // Take first term
                $street = array_shift($p->terms);

                // Load play coords from cached geocoding API
                $result2 = $this->geocache->load($p->place_id, function () use ($p) {
                    // Geocode place lat & lng
                    $result2 = $this->geocoding->geocode(['place_id' => $p->place_id]);
                    if ($result2->status !== 'OK') return NULL;

                    return $result2;
                });

                if (!$result2) continue;

                // Append to output
                $output[] = [
                    'name' => $street->value,
                    'place_id' => $p->place_id,
                    'lat' => $result2->results[0]->geometry->location->lat,
                    'lng' => $result2->results[0]->geometry->location->lng,
                ];
            }

            return $output;
        }

    }

    /**
     * @param string $q
     * @return mixed
     */
    public function geocode($q)
    {
        // Get organization city
        $organization = $this->organizationRequest->getOrganization();

        if ($organization) {
            $city = $organization->name;

            // Prepend city
            if (!Strings::contains($q, $city)) {
                $q = "$city, $q";
            }
        }

        $result = $this->geocoding->geocode(['address' => $q]);
        if ($result->status !== 'OK') return NULL;

        return $result->results[0];
    }

    /**
     * @param float $lat
     * @param float $lng
     * @return mixed
     */
    public function geocodeByCoords($lat, $lng)
    {
        $result = $this->geocoding->geocode(['latlng' => "$lat, $lng", 'result_type' => 'street_address']);
        if ($result->status !== 'OK') return NULL;

        return $result->results[0];
    }

}
