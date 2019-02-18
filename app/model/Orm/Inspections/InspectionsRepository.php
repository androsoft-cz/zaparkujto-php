<?php

namespace App\Model\Orm\Inspections;

use App\Model\Orm\AbstractRepository;

class InspectionsRepository extends AbstractRepository
{

    /**
     * @return array
     */
    public static function getEntityClassNames()
    {
        return [
            Inspection::class,
        ];
    }
}
