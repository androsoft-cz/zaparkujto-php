<?php

namespace App\Model\Orm\Credits;

use App\Model\Orm\AbstractRepository;

class CreditsRepository extends AbstractRepository
{

    /**
     * @return array
     */
    public static function getEntityClassNames()
    {
        return [
            Credit::class,
        ];
    }
}
