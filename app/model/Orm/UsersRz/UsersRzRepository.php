<?php

namespace App\Model\Orm\UsersRz;

use App\Model\Orm\AbstractRepository;

/**
 * @property-read UsersRzMapper $mapper
 */
class UsersRzRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public static function getEntityClassNames()
    {
        return [
            UserRz::class,
        ];
    }

}
