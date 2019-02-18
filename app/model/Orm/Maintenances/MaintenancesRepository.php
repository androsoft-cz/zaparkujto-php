<?php

namespace App\Model\Orm\Maintenances;

use App\Model\Orm\AbstractRepository;

class MaintenancesRepository extends AbstractRepository
{

    /**
     * @return array
     */
    public static function getEntityClassNames()
    {
        return [
            Maintenance::class,
        ];
    }
}
