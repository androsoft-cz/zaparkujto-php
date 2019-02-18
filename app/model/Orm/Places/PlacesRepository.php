<?php

namespace App\Model\Orm\Places;

use App\Model\Orm\AbstractApplicationRepository;
use App\Model\Search\SearchArgs;
use Nextras\Orm\Collection\ICollection;

/**
 * @property-read PlacesMapper $mapper
 */
class PlacesRepository extends AbstractApplicationRepository
{

    /**
     * @return array
     */
    public static function getEntityClassNames()
    {
        return [SearchPlace::class, Place::class];
    }

    /**
     * @param array $conditions
     * @return ICollection
     */
    public function findUniqueByStreet(array $conditions = [])
    {
        return $this->mapper->findUniqueByStreet($conditions);
    }

    /**
     * @param float $lat
     * @param float $lng
     * @return Place[]|ICollection
     */
    public function findByGps($lat, $lng)
    {
        $result = $this->mapper->findByGps($lat, $lng);

        return $this->mapper->toCollection($result);
    }

    public function findPlacesToCheck(array $conditions)
    {
        return $this->mapper->findPlacesToCheck($conditions);

    }

    /**
     * @param int $organization
     * @return Place[]|ICollection
     */
    public function findSparePlaces($organization)
    {
        return $this->findBy([
            'spareSignature' => 1,
            'organization' => $organization,
        ])->fetchPairs('id', 'identifier');
    }

    public function isPlaceMember($id, $username): int
    {
        return $this->mapper->isPlaceMember($id, $username)->fetch()->is_member;
    }

    /**
     * @param SearchArgs $args
     * @return SearchPlace[]|ICollection
     */
    public function search(SearchArgs $args)
    {
        $search = [
            'lat' => $args->lat,
            'lng' => $args->lng,
        ];

        $result = $this->mapper->search($search);

        return $this->mapper->toCollection($result);
    }

    /**
     * @param array $data
     * @return string
     */
    public function getEntityClassName(array $data)
    {
        if (isset($data['distance'])) {
            return SearchPlace::class;
        } else {
            return Place::class;
        }
    }
}
