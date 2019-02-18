<?php

namespace App\Model\Orm\UseofPlaces;

use App\Model\Orm\AbstractRepository;

/**
 * @property-read UseofPlacesMapper $mapper
 */
class UseofPlacesRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public static function getEntityClassNames()
    {
        return [
            UseofPlaces::class,
        ];
    }

}
